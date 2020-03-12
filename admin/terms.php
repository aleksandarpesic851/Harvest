<?php
    // Manage Terms for 
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $runningCLI = false;
    if (!isset($rootPath) || strlen($rootPath) < 1) {
        $rootPath = __DIR__ . "/../";
        $runningCLI = true;
    }

    $logMethodFile = true;
    $termsLogFile = fopen($rootPath . "/logs/terms_log.txt", "w") or die("Unable to open file!");
    fwrite($termsLogFile, date("Y-m-d h:i:sa"));

    require_once $rootPath . "/db_connect.php";
    require_once $rootPath . "/enable_error_report.php";
    require_once $rootPath . "/admin/condition/stop_keywords.php";

    $log = "\r\n-----------------------Extracting Data from scraped Studies-----------";
    logOrPrintTerms($log);

    $conditions = array();
    $drugs = array();

    $start = time();
    
    mysqli_autocommit($conn,FALSE);

    // Remove all data in study_id_condition table
    $query = "TRUNCATE `study_id_conditions`";
    if (!mysqli_query($conn, $query)) {
        $log = "\r\n Error in mysql query: " . mysqli_error($conn);
        logOrPrintTerms($log);
    }
    
    if (!mysqli_commit($conn)) {
        $log = "Commit transaction failed";
        logOrPrintTerms($log);
    }

    // Remove all data in study_id_condition table
    $query = "TRUNCATE `study_id_drugs`";
    if (!mysqli_query($conn, $query)) {
        $log = "\r\n Error in mysql query: " . mysqli_error($conn);
        logOrPrintTerms($log);
    }

    if (!mysqli_commit($conn)) {
        $log = "Commit transaction failed";
        logOrPrintTerms($log);
    }
    
    processData();
    saveTerms();

    $end = time();
    $log = "\r\nExtracting was completed.\r\n" . 
        "\r\nConditions: " . count($conditions) .
        "\r\nDrugs: " . count($drugs) . 
        "\r\n\r\nTotal Elapsed Time in extracting terms" . time_diff_string($end-$start);
        logOrPrintTerms($log);

    // mysqli_close($conn);
    if ($logMethodFile) {
        fclose($termsLogFile);
    }
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
                logOrPrintTerms("ENDS");
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
            
            $log = "\r\n Now Extracted from " . $nCnt*$nRows . " data" .
                "\r\n The number of extracted diseases: " . count($conditions) . 
                "\r\n The number of extracted drugs: " . count($drugs) . "</br>";
            logOrPrintTerms($log);
        }
    }

    function processConditions($data, $id) {
        global $conditions;
        global $tmpConditions;
        global $stopKeywords;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }

        $delimiters = array("~", "`", ";", "；" , ",", ".", "|", ":", " ", "/", "\\", "、", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "_", "+", "[", "]", "{", "}", ";", "'", '"', "?", ">", "<", "／", "，");
        $arrCondition = multiexplode($delimiters, $data);

        foreach($arrCondition as $condition) {
            $val = trim(strtolower($condition));
            if (is_numeric(substr($condition, 0, 1)) || strlen($condition) < 3) {
                continue;
            }
            if (isset($stopKeywords[$val])) {
                continue;
            }
            pushData($val);
            array_push($tmpConditions, [ "val" => $val, "id" => $id ]);
        }
    }

    function multiexplode ($delimiters,$string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
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
    function saveTerms() {
        global $conditions;
        global $drugs;
        global $conn;
        saveEachData($conditions, "conditions", "condition_name");
        //saveEachData($drugs, "drugs", "drug_name");
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
                $log = "\r\n Error in mysql query: " . mysqli_error($conn);
                logOrPrintTerms($log);
            }

            if (!mysqli_commit($conn)) {
                $log = "Commit transaction failed";
                logOrPrintTerms($log);
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
            
            $log = "\r\n Query: " . $query . 
                "\r\n Error in mysql query: " . mysqli_error($conn);
            logOrPrintTerms($log);

        }
        if (!mysqli_commit($conn)) {
            $log = "Commit transaction failed";
            logOrPrintTerms($log);
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
            $log = "\r\n Query: " . $query .
                "\r\n Error in mysql query: " . mysqli_error($conn);
            logOrPrintTerms($log);
        }
        if (!mysqli_commit($conn)) {
            $log = "Commit transaction failed";
            logOrPrintTerms($log);
        }
    }

    function logOrPrintTerms($log) {
        global $logMethodFile;
        global $termsLogFile;

        if ($logMethodFile) {
            fwrite($termsLogFile, $log);
        } else {
            echo $log;
            //ob_flush();
            flush();
        }
    }