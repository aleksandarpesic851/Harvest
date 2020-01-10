<?php
// Manage Terms for 
    include "../db_connect.php";
    include "../enable_error_report.php";
    
    $age_groups = array();
    $conditions = array();
    $phases = array();
    $intervention_types = array();
    $study_design_types = array();
    $statuses = array();
    $study_types = array();

    $start = time();
    extractData();
    //saveData();
    print_r("<br>Extracting was completed.<br>");
    print_r("<br>Age Groups: " . count($age_groups));
    print_r("<br>Conditions: " . count($conditions));
    print_r("<br>Phases: " . count($phases));
    print_r("<br>Intervention Types: " . count($intervention_types));
    print_r("<br>Study Design Types: " . count( $study_design_types));
    print_r("<br>Statuses: " . count($statuses));
    print_r("<br>Study Types: " . count($study_types));

    $end = time();
    print_r("<br><br>Total Elapsed Time" . time_elapsed($end-$start));
    mysqli_close($conn);

    function extractData() {
        global $conn;
        global $conditions;

        $nCnt = 0;
        $nRows = 1000;
        $query = "SELECT * FROM studies ORDER BY `nct_id` LIMIT ? OFFSET ?;";
        
        while(true) {
            $start = time();
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
                    extractConditions($row["conditions"]);
                    extractAgeGroups($row["age_groups"]);
                    extractPhases($row["phases"]);
                    extractInterventionTypes($row["interventions"]);
                    extractStudyDesignTypes($row["study_designs"]);
                    extractStatuses($row["status"]);
                    extractStudyTypes($row["study_types"]);
                }
            } else {
                $stmt->close();
            break;
            }
            $nCnt++;
            $stmt->close();

            $end=time();
            echo "<br> Now Extracted from " . $nCnt*$nRows . " data";
            echo "<br> The number of extracted diseases: " . count($conditions) . "</br>";
            ob_flush();
            flush();
        }
    }

    function extractConditions($data) {
        global $conditions;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrCondition = explode("|", $data);

        foreach($arrCondition as $condition) {
            $val = getValue($condition);
            pushData($val);
            // if (!in_array($val, $conditions) && strlen($val) > 0) {
            //     array_push($conditions, $val);
            // }
        }
    }
    
    function extractAgeGroups($data) {
        global $age_groups;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrAgeGroups = explode("|", $data);

        foreach($arrAgeGroups as $ageGroup) {
            $val = getValue($ageGroup);
            if (!in_array($val, $age_groups) && strlen($val) > 0) {
                array_push($age_groups, $val);
            }
        }
    }

    function extractPhases($data) {
        global $phases;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrPhases = explode("|", $data);

        foreach($arrPhases as $phase) {
            $val = getValue($phase);
            if (!in_array($val, $phases) && strlen($val) > 0) {
                array_push($phases, $val);
            }
        }
    }

    function extractInterventionTypes($data) {
        global $intervention_types;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrInterventions = explode("|", $data);

        foreach($arrInterventions as $intervention) {
            $interventionType = getValue(explode(":", $intervention)[0]);
            if (!in_array($interventionType, $intervention_types) && strlen($interventionType) > 0) {
                array_push($intervention_types, $interventionType);
            }
        }
    }

    function extractStudyDesignTypes($data) {
        global $study_design_types;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }
        $arrStudyDesigns = explode("|", $data);

        foreach($arrStudyDesigns as $studyDesign) {
            $studyDesignType = getValue(explode(":", $studyDesign)[0]);
            if (!in_array($studyDesignType, $study_design_types) && strlen($studyDesignType) > 0) {
                array_push($study_design_types, $studyDesignType);
            }
        }
    }

   function extractStatuses($data) {
        global $statuses;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }

        $data = getValue($data);
        if (!in_array($data, $statuses) && strlen($data) > 0) {
            array_push($statuses, $data);
        }
   }

   function extractStudyTypes($data) {
        global $study_types;

        if (!isset($data) || strlen($data) < 1) {
            return;
        }

        $data = getValue($data);
        if (!in_array($data, $study_types) && strlen($data) > 0) {
            array_push($study_types, $data);
        }
    }

    //Replace ', " character with \', \"
    function getValue($val) {
        if ($val=='""') {
            return "";
        }
        return trim(strtolower(str_replace("'", "\'", str_replace("\\", "\\\\", $val))));
    }

    function saveData() {
        global $age_groups;
        global $conditions;
        global $phases;
        global $intervention_types;
        global $study_design_types;
        global $statuses;
        global $study_types;
        global $conn;

        mysqli_autocommit($conn,FALSE);

        saveEachData($conditions, "conditions", "condition");
        saveEachData($age_groups, "age_groups", "age_group");
        saveEachData($phases, "phases", "phase");
        saveEachData($intervention_types, "intervention_types", "intervention_type");
        saveEachData($study_design_types, "study_design_types", "study_design_type");
        saveEachData($statuses, "statuses", "status");
        saveEachData($study_types, "study_types", "study_type");

        if (!mysqli_commit($conn)) {
            echo "Commit transaction failed";
        }

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
        if (strlen($newData) < 1) {
            return;
        }
        global $conditions;
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