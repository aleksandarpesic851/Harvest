<?php
// In order to speed up, calculate all study ids related all drugs in hierarchy.
    require_once "../../db_connect.php";
    require_once "../../enable_error_report.php";

    $log = true;
    if (isset($_POST) && isset($_POST["post"])) {
        $log = false;
    }
    if ($log) {
        echo "<br>-----------------------Calculate Study Ids Related with Drug Hierarchy----------------------------";
    }
    $totalData = array();
    mysqli_autocommit($conn,FALSE);

    calculateStudyDrugs();

    if (!mysqli_commit($conn)) {
        echo "Commit transaction failed";
    }

    echo "ok";
    //echo json_encode($totalData);

    function calculateStudyDrugs() {
        global $totalData;
        global $log;

        $totalData = readAllHierarchy();
        calculateStudyIds();
        mergeIds();
        saveData();
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

    //Read All drugs in hierarchy
    function readAllHierarchy() {
        $query = "SELECT `id`, `drug_name`, `parent_id`, `drug_id` FROM drug_hierarchy";
        return mysqlReadAll($query);
    }

    // Calculate study ids related with drug name
    function calculateStudyIds() {
        global $totalData;
        global $log;
        
        foreach($totalData as $key=>$drug) {
            $start = time();
            $query = "SELECT `nct_id` FROM study_id_drugs WHERE ( `drug` LIKE '%" . $drug["drug_name"] . "%') GROUP BY `nct_id`";
            
            $nctIds = mysqlReadAll($query);
            $totalData[$key]["study_ids"] = array();
            
            foreach($nctIds as $id) {
                array_push($totalData[$key]["study_ids"], $id["nct_id"]);
            }
            
            $end = time();
            if ($log) {
                echo "<br>Calculate Study Id - ". $drug["drug_name"] . " : " . time_elapsed_string($end-$start);
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
        foreach($totalData as $key=>$drug) {
            if ($drug["parent_id"] == 0) {
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
        foreach($totalData as $key => $drug) {
            if (isset($drug["leaf"]) && $drug["leaf"]) {
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
        foreach($totalData as $key => $drug) {
            if ($drug["parent_id"] != $totalData[$parentKey]["id"]) {
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
        foreach($totalData as $key => $drug) {
            if ($drug["id"] == $totalData[$childKey]["parent_id"])  {
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
        foreach($totalData as $key=>$drug) {
            echo "<br>" .  $drug["drug_name"] . ": " . count($drug["study_ids"]);
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
    
    function saveData() {
        global $totalData;
        global $conn;

        foreach($totalData as $data) {
            $query = "UPDATE `drug_hierarchy` SET `study_ids` = '" . implode(",", $data["study_ids"]) . "' WHERE `id` = " . $data["id"];
            mysqli_query($conn, $query);
        }
    }
?>