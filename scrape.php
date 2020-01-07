<?php
    include "enable_error_report.php";

    if (!isset($conn)) {
        include "db_connect.php";
    }

    //Replace ', " character with \', \"
    function getValue($item) {
        return str_replace("'", "\'", str_replace("\\", "\\\\", $item));
    }

    // Get study item value
    function getItem($studyItem) {
        $field = $studyItem->getName();
        $value = "";
        if (count($studyItem->children()) > 0) {                        // In the case of An item has multiple subitem
            foreach($studyItem->children() as $item) {
                if (strlen($value) > 0) {
                    $value .= "|";
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
            } else {
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

    $startTime = time();
    $down_chunk = 169;
    $down_count = 1000;
    mysqli_autocommit($conn,FALSE);
    while (true) {
        $chuckStart = time();
        // Scrape data from the link and save in data.xml file
        file_put_contents("data.xml", fopen("https://clinicaltrials.gov/ct2/results/download_fields?down_count=$down_count&down_flds=all&down_fmt=xml&down_chunk=$down_chunk", 'r'));
        $chuckEnd = time();
        echo "<br> Updated Rank " . ($down_chunk-1) * 1000 . " - " . $down_chunk * 1000 .  ",    Download Time: " . time_elapsed($chuckEnd - $chuckStart);
        $down_chunk++;

        // Load xml data and save into db
        $xml=simplexml_load_file("data.xml") or die("Error: Can't read data from file");
        
        //if there is no data, stop updating
        if (count($xml->children()) < $down_count) {
            break;
        } 

    
        // $totQuery = "";
        // $nCnt = 0;
        foreach($xml->children() as $study) {
            if ($study->getName() != "study") {
                continue;
            }
    
            $fields = "";
            $values = "";
            $updates = "";
            $status_open = 1;

            foreach($study->children() as $studyItem) {
                if (strlen($fields) > 0) {
                    $values .= ", ";
                    $fields .= ", ";
                    $updates .= ", ";
                }
                $field = $studyItem->getName();
                $fields .= "`" . $field . "`";
                $value = getItem($studyItem);
                $values .="'" . $value . "'";
                $updates .= "`" . $field . "`='" . $value . "'";
                if ($field == "status" && $studyItem["open"] == "N") {
                    $status_open = 0;
                }
            }
            $values .= ", '" . $study["rank"] . "'" . ", '" . $status_open . "'";
            $fields .= ", `rank`, `status_open`";
            $updates = "ON DUPLICATE KEY UPDATE " . $updates . ", `rank`='" . $study["rank"] . "', `status_open`='" . $status_open . "'";
            $query = " INSERT INTO studies ($fields) VALUES ($values) $updates; ";
            // $totQuery .= $query;
            // $nCnt++;
            if (!mysqli_query($conn, $query)) {
                echo "<br> Error in mysql query: " . mysqli_error($conn);
            }

            // if ($nCnt > 99) {
            //     // Insert or Update data into db
            //     if (!mysqli_multi_query($conn, $totQuery)) {
            //         echo "<br> Error in mysql query: " . mysqli_error($conn);
            //         echo "<br> query:" . $totQuery;
            //     }
            //     while(mysqli_more_results($conn))
            //     {
            //         mysqli_next_result($conn);
            //     }
            //     $nCnt = 0;
            //     $totQuery = "";
            // }
        }

        // if (strlen($totQuery) > 0) {
        //     // Insert or Update data into db
        //     if (!mysqli_multi_query($conn, $totQuery)) {
        //         echo "<br> Error in mysql query: " . mysqli_error($conn);
        //         echo "<br> query:" . $totQuery;
        //     }
        //     while(mysqli_more_results($conn))
        //     {
        //         mysqli_next_result($conn);
        //     }
        // }

        // Commit transaction
        if (!mysqli_commit($conn)) {
            echo "Commit transaction failed";
            //exit();
        }
        $chuckEnd = time();
        echo ",    Time Elapsed: " . time_elapsed($chuckEnd - $chuckStart);
        ob_flush();
        flush();
    }

    mysqli_close($conn);
    $endTime = time();
    echo "<br> Total Time Elapsed: " . time_elapsed($endTime - $startTime);