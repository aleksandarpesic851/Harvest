<?php
// In order to speed up, calculate all study ids related all conditions in hierarchy.
    if (!isset($isScraping)) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    }

    $log = true;
    if (isset($_POST) && isset($_POST["post"])) {
        $log = false;
    }
    if ($log) {
        echo "<br>-----------------------Calculate Study Ids Related with Condition Hierarchy----------------------------";
    }
    $totalData = array();
    // $query = "DELETE FROM condition_hierarchy_modifier_stastics";
    // mysqli_query($conn, $query);
    mysqli_autocommit($conn,FALSE);

    calculateStudyConditions();

    if (!mysqli_commit($conn)) {
        echo "Commit transaction failed";
    }

    echo "ok";
    //echo json_encode($totalData);

    function calculateStudyConditions() {
        global $totalData;
        global $log;

        $modifiers = readModifiers();
        $conditions = readAllHierarchy();

        foreach($modifiers as $modifier) {
            if ($log) {
                echo "<br>-----------------------" . $modifier["modifier"] . "----------------------------";
            }
            $totalData = $conditions;
            changeSpecialCaracters();
            calculateStudyIds($modifier["modifier"]);
            mergeIds();
            saveData($modifier["id"]);
        }
    }

    function changeSpecialCaracters() {
        global $totalData;

        foreach($totalData as $key=>$val) {
            $totalData[$key]["condition_name"] = str_replace("'", "\'", $totalData[$key]["condition_name"]);
        }
    }

    function mysqlReadAll($query) {
        global $conn;
        
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);
        return isset($data) ? $data : array();
    }

    function mysqlRowCnt($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
        $nCnt = mysqli_num_rows($result);
        // Free result set
        mysqli_free_result($result);
        return $nCnt;
    }
    // Read All Modifiers
    function readModifiers() {
        $query = "SELECT * FROM modifiers";
        return mysqlReadAll($query);
    }

    //Read All conditions in hierarchy
    function readAllHierarchy() {
        $query = "SELECT `id`, `condition_name`, `synonym`, `parent_id`, `condition_id` FROM condition_hierarchy_view";
        return mysqlReadAll($query);
    }

    // Calculate study ids related with condition name
    function calculateStudyIds($modifier) {
        global $totalData;
        global $log;
        
        foreach($totalData as $key=>$condition) {
            $start = time();
            $query = "SELECT `nct_id` FROM study_id_conditions WHERE ( `condition` LIKE '%" . $condition["condition_name"] . "%' ";
            if (isset($condition["synonym"]) && strlen($condition["synonym"]) > 0) {
                $query .= " OR  `condition` LIKE '%" . $condition["synonym"] . "%' ";
            }
            $query .= ") ";
            if (strlen($modifier) > 0 && $modifier != "NONE") {
                $query .= " AND  `condition` LIKE '%" . $modifier . "%' ";
            }
            $query .= " GROUP BY `nct_id`";

            $nctIds = mysqlReadAll($query);
            $totalData[$key]["study_ids"] = array();
            
            foreach($nctIds as $id) {
                array_push($totalData[$key]["study_ids"], $id["nct_id"]);
            }
            
            $end = time();
            if ($log) {
                echo "<br>Calculate Study Id - ". $condition["condition_name"] . " : " . time_elapsed_string($end-$start);
                echo ", Count: " . count($nctIds);
                ob_flush();
                flush();
            }
        }
    }

    // merge Study Ids
    function mergeIds() {
        global $log;

        if ($log) {
            printStudyIdCnts("Before Merge");
        }
        global $totalData;

        $start = time();
        foreach($totalData as $key=>$condition) {
            if ($condition["parent_id"] == 0) {
                mergeParentChild($key);
            }
        }
        if ($log) {
            printStudyIdCnts("Merge Parent -> Child");
        }
        $end = time();
        if ($log) {
            echo "<br>Time : " . time_elapsed_string($end-$start);
        }
        $start = time();
        foreach($totalData as $key => $condition) {
            if (isset($condition["leaf"]) && $condition["leaf"]) {
                mergeChildParent($key);
            }
        }
        $end= time();
        if ($log) {
            printStudyIdCnts("Merge Child -> Parent");
            echo "<br>Time : " . time_elapsed_string($end-$start);
        }
    }

    // merge Parent -> Child
    function mergeParentChild($parentKey) {
        global $totalData;
        $isLeaf = true;
        foreach($totalData as $key => $condition) {
            if ($condition["parent_id"] != $totalData[$parentKey]["id"]) {
                continue;
            }
            $isLeaf = false;
            $totalData[$key]["study_ids"] = mergeArray($totalData[$key]["study_ids"], $totalData[$parentKey]["study_ids"]);
            mergeParentChild($key);
        }
        if ($isLeaf) {
            $totalData[$parentKey]["leaf"] = true;
        }
    }

    //merge Child -> Parent
    function mergeChildParent($childKey) {
        global $totalData;

        // Get Parent Node
        foreach($totalData as $key => $condition) {
            if ($condition["id"] == $totalData[$childKey]["parent_id"])  {
                $totalData[$key]["study_ids"] = mergeArray($totalData[$key]["study_ids"], $totalData[$childKey]["study_ids"]);
                mergeChildParent($key);
                break;
            }
        }
    }

    function mergeArray($array1, $array2) {
        $merged = $array1;
        foreach($array2 as $val2) {
            if (!in_array($val2, $array1)) {
                array_push($merged, $val2);
            }
        }
        return $merged;
    }

    function printStudyIdCnts($explain) {
        global $totalData;
        echo "<br> $explain:";
        foreach($totalData as $key=>$condition) {
            echo "<br>" .  $condition["condition_name"] . ": " . count($condition["study_ids"]);
        }
    }
    // Calculate elapsed time
    function time_elapsed_string($secs){
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
    
    function saveData($modifierID) {
        global $totalData;
        global $conn;

        foreach($totalData as $data) {
            $query = "SELECT `modifier_id` FROM `condition_hierarchy_modifier_stastics` WHERE `modifier_id` = $modifierID AND `hierarchy_id` = " . $data["id"];
            $nCnt = mysqlRowCnt($query);
            if ($nCnt < 1) {
                $query = "INSERT INTO `condition_hierarchy_modifier_stastics` (`hierarchy_id`, `modifier_id`, `condition_id`, `condition_name`, `study_ids`)";
                $query .= "VALUES ('" . $data["id"] . "', '$modifierID', '" . $data["condition_id"] . "', '" . $data["condition_name"] . "', '" . implode(",", $data["study_ids"]) . "'); ";
            } else {
                $query = "UPDATE `condition_hierarchy_modifier_stastics` SET `study_ids` = '" . implode(",", $data["study_ids"]) . "' WHERE  `modifier_id` = $modifierID AND `hierarchy_id` = " . $data["id"];
            }
            mysqli_query($conn, $query);
        }
    }
?>