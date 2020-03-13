<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$runningCLI = false;
if (!isset($rootPath) || strlen($rootPath) < 1) {
    $rootPath = __DIR__ . "/../";
    $runningCLI = true;
}
$logMethodFile = true;
$logFile = fopen($rootPath . "/logs/scrape_log.txt", "w") or die("Unable to open file!");
fwrite($logFile, date("Y-m-d h:i:sa"));

    require_once $rootPath . "/db_connect.php";
	require_once $rootPath . "/enable_error_report.php";
    
    //Replace ', " character with \', \"
    function getValue($item) {
        return str_replace("'", "\'", str_replace("\\", "\\\\", $item));
    }

    // Get study item value
    function getItem($studyItem) {
        $field = $studyItem->getName();
        $value = "";
        $enumSetFields = ["phases", "age_groups"];
        if (count($studyItem->children()) > 0) {                        // In the case of An item has multiple subitem
            foreach($studyItem->children() as $item) {
                
                if (strlen($value) > 0) {
                    if (in_array($field, $enumSetFields)) {
                        $value .= ",";    
                    } else {
                        $value .= "|";
                    }
                }
                if ($field == "interventions") {                // type:content|type: content
                    $value .= $item["type"] .":" . $item;
                } else if ($field == "sponsors") {              // name:content|name:content
                    $value .= $item->getName() .":" . $item;
                } else {
                    $value .= $item;
                }
            }
        } else {
            $name = $studyItem->getName();
            // change date format
            if (strpos($name, "date") > 0 || strpos($name, "posted") > 0) {
                $time = strtotime($studyItem);
                $value = date('Y-m-d',$time);
            } else if ($name == "nct_id") {
                $value = substr($studyItem, 3);
            } else if ($name == "min_age" || $name == "max_age") {
                $value = intval($studyItem);
            }
            else {
                $value = $studyItem;
            }
        }
        $value = getValue($value);
        return $value;
    }

    // Calculate elapsed time
    function time_elapsed($secs){
        $bit = array(
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
            );
        $ret[] = "";
        foreach($bit as $k => $v)
            if($v > 0)
                $ret[] = $v . $k;
           
        return join(' ', $ret);
    }

    // Update history
    $now = date("Y-m-d H:i:s", time());
    $query = "INSERT INTO `update_history` (`updated_at`) VALUES ('$now')";
    
    if (!mysqli_query($conn, $query)) {
        if ($logMethodFile) {
            fwrite($logFile, mysqli_error($conn));
        } else {
            echo mysqli_error($conn);
        }
        exit;
    }

    $startTime = time();
    $down_chunk = 1;
    $down_count = 1000;
    $ignoreFields = ["documents", "study_documents", "url", "other_ids", "funded_bys", "acronym", "exp_acc_types"];
    mysqli_autocommit($conn,FALSE);
    while (true) {
        // The maximum updates number is less than 3000, so restricts as 3000 as maximum
        if ($down_chunk > 3) {
        break;
        }
        $log = "\r\n Working on " . ($down_chunk-1) * 1000 . " - " . $down_chunk * 1000 .  " data:";
        if ($logMethodFile) {
            fwrite($logFile, $log);
        } else {
            echo $log;
        }
        
        $chuckStart = time();
        // Scrape data from the link and save in data.xml file
        // file_put_contents("data.xml", fopen("https://clinicaltrials.gov/ct2/results/download_fields?down_count=$down_count&down_flds=all&down_fmt=xml&down_chunk=$down_chunk", 'r'));

        $url = "https://clinicaltrials.gov/ct2/results/download_fields?down_count=$down_count&down_flds=all&down_fmt=xml&down_chunk=$down_chunk";
        $file_name = $rootPath . "/logs/data.xml";
        $fp = fopen($file_name, "w");
        $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        // Load xml data and save into db
        $xml=simplexml_load_file($file_name) or die("Error: Can't read data from file");
   
        $chuckEnd = time();
        $log = " Download Time: " . time_elapsed($chuckEnd - $chuckStart);
        if ($logMethodFile) {
            fwrite($logFile, $log);
        } else {
            echo $log;
        }
        $down_chunk++;
        
        foreach($xml->children() as $study) {
            if ($study->getName() != "study") {
                continue;
            }
    
            $fields = "";
            $values = "";
            $updates = "";
            $status_open = 1;

            foreach($study->children() as $studyItem) {
                $field = $studyItem->getName();
                $value = getItem($studyItem);
                if (in_array($field, $ignoreFields)) {
                    continue;
                }

                if (strlen($fields) > 0) {
                    $values .= ", ";
                    $fields .= ", ";
                    $updates .= ", ";
                }
                $fields .= "`" . $field . "`";
                
                $values .="'" . $value . "'";
                $updates .= "`" . $field . "`='" . $value . "'";
                if ($field == "status" && $studyItem["open"] == "N") {
                    $status_open = 0;
                }
            }
            $values .= ", '" . $status_open . "'";
            $fields .= ",  `status_open`";
            $updates = "ON DUPLICATE KEY UPDATE " . $updates . ", `status_open`='" . $status_open . "'";
            $query = " INSERT INTO studies ($fields) VALUES ($values) $updates; ";
            if (!mysqli_query($conn, $query)) {
                $log = "\r\n Error in mysql query: " . mysqli_error($conn);
                if ($logMethodFile) {
                    fwrite($logFile, $log);
                } else {
                    echo $log;
                }
            }

        }

        // Commit transaction
        if (!mysqli_commit($conn)) {
            $log = "Commit transaction failed";
            if ($logMethodFile) {
                fwrite($logFile, $log);
            } else {
                echo $log;
            }
            //exit();
        }
        $chuckEnd = time();
        $log = ",    Complete Time: " . time_elapsed($chuckEnd - $chuckStart);
        if ($logMethodFile) {
            fwrite($logFile, $log);
        } else {
            echo $log;
            //ob_flush();
            flush();
        }

        //if there is no data, stop updating
        if (count($xml->children()) < $down_count) {
            break;
        }
    }
    
    $isScraping = true;
    mysqlReconnect();
    //Extract Data
    require_once $rootPath . "/admin/terms.php";
    mysqlReconnect();
    //Extract study ids related with condition hierarchy
    require_once $rootPath . "/admin/condition/calculate_study_condition.php";
    mysqlReconnect();
    //Extract study ids related with condition hierarchy
    require_once $rootPath . "/admin/drug/calculate_study_drug.php";
    
    mysqli_close($conn);
    $endTime = time();

    $log = "\r\n Total Time Elapsed: " . time_elapsed($endTime - $startTime);
    if ($logMethodFile) {
        fwrite($logFile, $log);
        fclose($logFile);
    } else {
        echo $log;
        //ob_flush();
        flush();
    }