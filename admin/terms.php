<?php
// Manage Terms for 
require_once "../db_connect.php";
require_once "../enable_error_report.php";
    echo "<br>-----------------------Extracting Data from scraped Studies-----------";
    $conditions = array();

    $start = time();

    // Remove all data in study_id_condition table
    $query = "DELETE FROM `study_id_conditions`";
    if (!mysqli_query($conn, $query)) {
        echo "<br> Error in mysql query: " . mysqli_error($conn);
    }

    mysqli_autocommit($conn,FALSE);

    processData();
    saveData();

    if (!mysqli_commit($conn)) {
        echo "Commit transaction failed";
    }

    print_r("<br>Extracting was completed.<br>");
    print_r("<br>Conditions: " . count($conditions));

    $end = time();
    print_r("<br><br>Total Elapsed Time" . time_elapsed($end-$start));

    // mysqli_close($conn);

    function processData() {
        global $conn;
        global $conditions;

        $nCnt = 0;
        $nRows = 1000;
        $query = "SELECT `nct_id`, `conditions` FROM studies ORDER BY `nct_id` LIMIT ? OFFSET ?;";
        
        while(true) {
            $offset = $nCnt*$nRows;
            $stmt = $conn->prepare($query);

            if ($stmt === false) {
                echo "ENDS";
            break;
            }
            $stmt->bind_param("ii", $nRows, $offset);
            $stmt->execute();

            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    processConditions($row["conditions"], $row["nct_id"]);
                }
            } else {
                $stmt->close();
            break;
            }
            $nCnt++;
            $stmt->close();

            echo "<br> Now Extracted from " . $nCnt*$nRows . " data";
            echo "<br> The number of extracted diseases: " . count($conditions) . "</br>";
            ob_flush();
            flush();
        }
    }

    function processConditions($data, $id) {
        global $conditions;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrCondition = explode("|", $data);

        foreach($arrCondition as $condition) {
            $val = getValue($condition);
            if (isset($val)) {
                pushData($val);
                saveStudyCondition($val, $id);
            }
        }
    }

    //Replace ', " character with \', \"
    function getValue($val) {
        if ($val=='""' || strlen($val) < 1) {
            return;
        }
        $newData = trim(strtolower(str_replace("'", "\'", str_replace("\\", "\\\\", $val))));
        //remove ""
        $newData = str_replace('"', '', $newData);
        // remove first -
        if (substr($newData, 0, 1) == "-") {
            $newData = trim(substr($newData, 1));
        }
        //remove ''
        if (substr($newData, 0, 1) == "'" && substr($newData, -1) == "'") {
            $newData = trim(substr($newData, 1, -1));
        }
        //remove last (xxx)
        if (substr($newData, -1) == ")") {
            $newData = trim(substr($newData, 0, strpos($newData, "(")));
        }

        if (strlen($newData) < 1) {
            return;
        }

        return $newData;
    }
//////////////////////////////////////Save on conditions table/////////////////////////////////////////////////
    function saveData() {
        global $conditions;
        global $conn;
        saveEachData($conditions, "conditions", "condition");
    }

    function saveEachData($data, $table, $columnName) {
        global $conn;
        
        $nCnt = 0;
        $nUnit = 1000;
        $subData = array_slice($data, 0, $nUnit);

        while(count($subData) > 0) {
            $values = "('" . implode("'), ('", $subData) . "')";
            $query = "INSERT INTO `$table` (`$columnName`) VALUES $values";
            $query .= " ON DUPLICATE KEY UPDATE `$columnName` = VALUES(`$columnName`)";
            //print_r($query);
            if (!mysqli_query($conn, $query)) {
                echo "<br> Error in mysql query: " . mysqli_error($conn);
            }
            $nCnt++;
            $subData = array_slice($data, $nUnit * $nCnt, $nUnit);
        }
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

    function pushData($newData) {
        global $conditions;

        if (in_array($newData, $conditions)) {
            return;
        }

        // remove xxxs
        if (substr($newData, -1) == "s") {
            $val = substr($newData, 0, -1);
            if (in_array($val, $conditions)) {
                return;
            }
        }
        
        $val = $newData . "s";
        if (in_array($val, $conditions)) {
            return;
        }

        array_push($conditions, $newData);

    }

//////////////////////////////////////Save on conditions table/////////////////////////////////////////////////

    function saveStudyCondition($condition, $id) {
        global $conn;
        $query = "INSERT INTO `study_id_conditions` (`nct_id`, `condition`) VALUES ('$id', '$condition')";
        if (!mysqli_query($conn, $query)) {
            echo "<br> Query: " . $query;
            echo "<br> Error in mysql query: " . mysqli_error($conn);
        }
    }