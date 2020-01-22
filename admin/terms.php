<?php
// Manage Terms for 
require_once "../db_connect.php";
require_once "../enable_error_report.php";
    echo "<br>-----------------------Extracting Data from scraped Studies-----------";
    $conditions = array();
    $drugs = array();

    $start = time();
    
    mysqli_autocommit($conn,FALSE);

    // Remove all data in study_id_condition table
    $query = "TRUNCATE `study_id_conditions`";
    if (!mysqli_query($conn, $query)) {
        echo "<br> Error in mysql query: " . mysqli_error($conn);
    }
    // Remove all data in study_id_condition table
    $query = "TRUNCATE `study_id_drugs`";
    if (!mysqli_query($conn, $query)) {
        echo "<br> Error in mysql query: " . mysqli_error($conn);
    }

    if (!mysqli_commit($conn)) {
        echo "Commit transaction failed";
    }
    
    processData();
    saveData();

    print_r("<br>Extracting was completed.<br>");
    print_r("<br>Conditions: " . count($conditions));
    print_r("<br>Drugs: " . count($drugs));

    $end = time();
    print_r("<br><br>Total Elapsed Time in extracting terms" . time_diff_string($end-$start));

    // mysqli_close($conn);
    
    ///////////////////////////////////////////// Functions ///////////////////////////////////////////////////
    $tmpConditions = array();
    $tmpDrugs = array();

    function processData() {
        global $conn;
        global $conditions;
        global $drugs;
        global $tmpConditions, $tmpDrugs;
        $nCnt = 0;
        $nRows = 1000;
        $query = "SELECT `nct_id`, `conditions`, `interventions` FROM studies ORDER BY `nct_id` LIMIT ? OFFSET ?;";
        
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
            
            if (mysqli_num_rows($result) < 1) {
                $stmt->close();
                break;
            }
            $tmpConditions = array();
            $tmpDrugs = array();

            while($row = $result->fetch_assoc()) {
                processConditions($row["conditions"], $row["nct_id"]);
                processDrugs($row["interventions"], $row["nct_id"]);
            }
            saveStudyCondition();
            saveStudyDrug();
            
            $nCnt++;
            $stmt->close();
            
            echo "<br> Now Extracted from " . $nCnt*$nRows . " data";
            echo "<br> The number of extracted diseases: " . count($conditions);
            echo "<br> The number of extracted drugs: " . count($drugs) . "</br>";
            ob_flush();
            flush();
        }
    }

    function processConditions($data, $id) {
        global $conditions;
        global $tmpConditions;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrCondition = explode("|", $data);

        foreach($arrCondition as $condition) {
            $val =  getTermValue($condition);
            if (isset($val)) {
                pushData($val);
                array_push($tmpConditions, [ "val" => $val, "id" => $id ]);
            }
        }
    }

    function processDrugs($data, $id) {
        global $drugs;
        global $tmpDrugs;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrDrugs = explode("|", $data);

        foreach($arrDrugs as $drug) {
            $tmpArray = explode(":", $drug);
            if (count($tmpArray) < 2) {
                continue;
            }
            $val =  getTermValue($tmpArray[1]);
            if (isset($val) && strlen($val) > 2) {
                $drugs[$val] = '';
                array_push($tmpDrugs, [ "val" => $val, "id" => $id ]);
            }
        }
    }

    //Replace ', " character with \', \"
    function  getTermValue($val) {
        if ($val=='""' || strlen($val) < 1) {
            return;
        }
        //remove ""
        $newData = str_replace('"', '', $val);
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
        
        $newData = trim(strtolower(str_replace("'", "\'", str_replace("\\", "\\\\", $newData))));
        
        if (strlen($newData) < 1) {
            return;
        }

        return $newData;
    }
//////////////////////////////////////Save on conditions table/////////////////////////////////////////////////
    function saveData() {
        global $conditions;
        global $drugs;
        global $conn;
        saveEachData($conditions, "conditions", "condition_name");
        saveEachData($drugs, "drugs", "drug_name");
    }

    function saveEachData($data, $table, $columnName) {
        global $conn;
        
        // generate value array from key=>val array
        $valData = array();
        foreach($data as $key => $val) {
            array_push($valData, $key);
        }
        
        // Remove data which are in db already
        $nCnt = 0;
        $nUnit = 1000;
        $subData = array_slice($valData, 0, $nUnit);

        while(count($subData) > 0) {
            //Get new data which is not in db.
            $query = "SELECT `$columnName` FROM `$table` WHERE `$columnName` IN ('" . implode("', '", $subData) . "')";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    unset($data[$row[$columnName]]);
                }
                mysqli_free_result($result);    
            }
            $nCnt++;
            $subData = array_slice($valData, $nUnit * $nCnt, $nUnit);
        }

        // generate value array from key=>val array
        $valData = array();
        foreach($data as $key => $val) {
            array_push($valData, $key);
        }
        $nCnt = 0;
        $nUnit = 1000;
        $subData = array_slice($valData, 0, $nUnit);
        while(count($subData) > 0) {
            $query = "INSERT INTO `$table` (`$columnName`) VALUES ('" . implode("'), ('", $subData) . "')";
            if (!mysqli_query($conn, $query)) {
                echo "<br> Error in mysql query: " . mysqli_error($conn);
            }

            if (!mysqli_commit($conn)) {
                echo "Commit transaction failed";
            }
            $nCnt++;
            $subData = array_slice($valData, $nUnit * $nCnt, $nUnit);
        }
    }

    // Calculate elapsed time
    function time_diff_string($secs){
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

        // remove xxxs
        if (substr($newData, -1) == "s") {
            $val = substr($newData, 0, -1);
            if (isset($conditions[$val])) {
                return;
            }
        }
        
        $val = $newData . "s";
        if (isset($conditions[$val])) {
            return;
        }
        $conditions[$newData] = '';
    }

//////////////////////////////////////Save on conditions table/////////////////////////////////////////////////

    function saveStudyCondition() {
        global $conn;
        global $tmpConditions;

        $queryVals = "";
        foreach($tmpConditions as $condition) {
            if (strlen($queryVals) > 0) {
                $queryVals .= ", ";
            }
            $queryVals .= "('" . $condition["id"] . "', '" . $condition["val"] . "')";
        }

        $query = "INSERT INTO `study_id_conditions` (`nct_id`, `condition`) VALUES $queryVals";
        if (!mysqli_query($conn, $query)) {
            echo "<br> Query: " . $query;
            echo "<br> Error in mysql query: " . mysqli_error($conn);
        }
        if (!mysqli_commit($conn)) {
            echo "Commit transaction failed";
        }
    }

//////////////////////////////////////Save on drug table/////////////////////////////////////////////////
    function saveStudyDrug() {
        global $conn;
        global $tmpDrugs;
        
        $queryVals = "";
        foreach($tmpDrugs as $drug) {
            if (strlen($queryVals) > 0) {
                $queryVals .= ", ";
            }
            $queryVals .= "('" . $drug["id"] . "', '" . $drug["val"] . "')";
        }

        $query = "INSERT INTO `study_id_drugs` (`nct_id`, `drug`) VALUES $queryVals";
        if (!mysqli_query($conn, $query)) {
            echo "<br> Query: " . $query;
            echo "<br> Error in mysql query: " . mysqli_error($conn);
        }
        if (!mysqli_commit($conn)) {
            echo "Commit transaction failed";
        }
    }