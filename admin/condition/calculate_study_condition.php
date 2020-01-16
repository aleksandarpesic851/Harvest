<?php
// In order to speed up, calculate all study ids related all conditions in hierarchy.
    require_once "../../db_connect.php";
    require_once "../../enable_error_report.php";

    $totalData = array();
    readAllHierarchy();
    calculateStudyIds();
    calculateLeafIds();
    //echo json_encode($totalData);
    printStudyIdCnts();

    function mysqlReadAll($query) {
        global $conn;
        
        $result = mysqli_query($conn, $query);
        if ($result->num_rows < 1) {
            return;
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);
        return $data;
    }

    //Read All conditions in hierarchy
    function readAllHierarchy() {
        global $totalData;
        $query = "SELECT h.*, c.`condition_name`, c.`synonym` FROM condition_hierarchy AS h JOIN conditions as c ON c.id = h.`condition_id`";
        $totalData = mysqlReadAll($query);
    }

    // Calculate study ids related with condition name
    function calculateStudyIds() {
        global $totalData;

        foreach($totalData as $key=>$condition) {
            $start = time();
            $query = "SELECT `nct_id` FROM study_id_conditions WHERE `condition` LIKE '%" . $condition["condition_name"] . "%' GROUP BY `nct_id`";
            $nctIds = mysqlReadAll($query);
            $totalData[$key]["study_ids"] = array();
            foreach($nctIds as $id) {
                array_push($totalData[$key]["study_ids"], $id["nct_id"]);
            }
            $end = time();
            print_r("<br>Calculate Study Id - ". $condition["condition_name"] . " : " . time_elapsed($end-$start));
            echo ", Count: " . count($nctIds);
            ob_flush();
            flush();
        }
    }

    // Calculate Study Ids for Leaf node.
    function calculateLeafIds() {
        global $totalData;
        foreach($totalData as $key=>$condition) {
            if ($condition["parent_id"] == 0) {
                mergeParentChild($key);
            }
        }
        foreach($totalData as $key => $condition) {
            if ($condition["leaf"]) {
                mergeChildParent($key);
            }
        }
    }

    // merge Parent -> Child
    function mergeParentChild($parentKey) {
        global $totalData;
        $isLeaf = true;
        foreach($totalData as $key => $condition) {
            if ($condition["parent_id"] != $totalData[$parentKey]["condition_id"]) {
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
            if ($condition["category_id"])
        }
        $parentId = $totalData[$childKey]["parent_id"];

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

    function printStudyIdCnts() {
        global $totalData;
        echo "<br> Final Results:";
        foreach($totalData as $key=>$condition) {
            echo "<br>" .  $condition["condition_name"] . ": " . count($condition["study_ids"]);
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
    
?>