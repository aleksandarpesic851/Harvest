<?php
    require_once "../enable_error_report.php";
    require_once "../db_connect.php";

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
        echo mysqli_error($conn);
        exit;
    }

    $startTime = time();
    $down_chunk = 328;
    $down_count = 1000;
    $ignoreFields = ["documents", "study_documents", "url", "other_ids", "funded_bys", "acronym", "exp_acc_types"];
    mysqli_autocommit($conn,FALSE);
    while (true) {
        echo "<br> Working on " . ($down_chunk-1) * 1000 . " - " . $down_chunk * 1000 .  " data:";
        $chuckStart = time();
        // Scrape data from the link and save in data.xml file
        file_put_contents("data.xml", fopen("https://clinicaltrials.gov/ct2/results/download_fields?down_count=$down_count&down_flds=all&down_fmt=xml&down_chunk=$down_chunk", 'r'));
        $chuckEnd = time();
        echo " Download Time: " . time_elapsed($chuckEnd - $chuckStart);
        $down_chunk++;

        // Load xml data and save into db
        $xml=simplexml_load_file("data.xml") or die("Error: Can't read data from file");
        
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
                echo "<br> Error in mysql query: " . mysqli_error($conn);
            }

        }

        // Commit transaction
        if (!mysqli_commit($conn)) {
            echo "Commit transaction failed";
            //exit();
        }
        $chuckEnd = time();
        echo ",    Complete Time: " . time_elapsed($chuckEnd - $chuckStart);
        ob_flush();
        flush();

        //if there is no data, stop updating
        if (count($xml->children()) < $down_count) {
            break;
        }
    }

    $isScraping = true;
    //Extract Data
    require_once "terms.php";
    //Extract study ids related with condition hierarchy
    require_once "condition/calculate_study_condition.php";
    //Extract study ids related with condition hierarchy
    require_once "drug/calculate_study_drug.php";
    
    mysqli_close($conn);
    $endTime = time();
    echo "<br> Total Time Elapsed: " . time_elapsed($endTime - $startTime);