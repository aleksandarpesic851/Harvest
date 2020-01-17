<?php
    require_once 'enable_error_report.php';
    require_once "db_connect.php";
    require_once 'generate_query_condition.php';

    if (!isset($_POST) || !isset($_POST["conditions"])) {
        echo "Invalid Parameters";
        exit;
    }

    $otherSearch = generateOtherSearchQuery($_POST);


    $conditions = array();
    $parentConditions = array();// this is used to calculate total study Ids
    getAllConditions($_POST["conditions"]);
    
    $studyIds = array();    // array of key as study id
    $studyIdVals = array(); // array of value as study id
    $filteredIds = array(); // array of key as filtered study id
    getAllStudyIds();
    createValArray();

    searchStudies();
    
    $modifiers = readModifiers();
    calculateCnts();

    echo json_encode($conditions);
    ////////////////////////////////GET ALL CONDITIONS////////////////////////////////////////
    function getAllConditions($conditionTree) {
        global $conditions;
        global $parentConditions;

        if (!isset($conditionTree) || count($conditionTree) < 1) {
            return array();
        }
        // If All conditions are checked, not calculate
        if ($conditionTree[0]["nodeId"] == "ROOT") {
            $conditionTree = $conditionTree[0]["nodeChild"];
        }

        foreach($conditionTree as $node) {
            $key = substr($node["nodeId"], 10);
            $conditions[$key]["condition_name"] = $node["nodeText"];
            array_push($parentConditions, $key);
            addChildNode($node);
        }
    }
    function addChildNode($node) {
        global $conditions;

        if (!isset($node["nodeChild"]) || count($node["nodeChild"]) < 1) {
            return;
        }
        foreach($node["nodeChild"] as $node) {
            $key = substr($node["nodeId"], 10);
            $conditions[$key] = array();
            addChildNode($node);
        }
    }
    
    ////////////////////////////EXTRACT study IDs related with condition///////////////////////////////////
    function getAllStudyIds() {
        global $conditions;
        global $parentConditions;
        global $studyIds;
        $i=0;
        foreach($conditions as $key=>$condition) {
            $conditions[$key]["studyIds"] = getStudyIds($key, 1);
            if (in_array($key, $parentConditions)) {
                mergeStudyIds($conditions[$key]["studyIds"]);
            }
        }
    }
    function getStudyIds($conditionId, $modifierId) {
        $query = "SELECT `study_ids` FROM condition_hierarchy_modifier_stastics WHERE `hierarchy_id` = $conditionId AND `modifier_id` = $modifierId";
        $statistics = mysqlReadFirst($query);
        $ids = array();
        if (!isset($statistics) || !isset($statistics["study_ids"]) || strlen($statistics["study_ids"]) < 1) {
            return $ids;
        }

        return explode(",", $statistics["study_ids"]);
    }
    
    function mergeStudyIds($array) {
        global $studyIds;

        foreach($array as $val) {
            $studyIds[$val] = '';
        }
    }

    function createValArray() {
        global $studyIds;
        global $studyIdVals;

        foreach($studyIds as $key=>$val) {
            array_push($studyIdVals, $key);
        }
    }
    //////////////////////////////////EXTRACT STUDY IDS IN search terms////////////////////////////////////////////
    function searchStudies() {
        global $studyIds;
        global $studyIdVals;
        global $otherSearch;
        global $filteredIds;

        // in this case, there is no reason to search studies table
        if (count($studyIdVals) < 1 || strlen($otherSearch) < 1) {
            $filteredIds = $studyIds;
            return;
        }

        $query = "SELECT `nct_id` from studies ";
        $conditionSearch = " `nct_id` IN " . "(" . implode(",",$studyIdVals) . ") ";
        $query .= " WHERE $otherSearch AND $conditionSearch";

        $searchedRes = mysqlReadAll($query);

        foreach($searchedRes as $row) {
            $filteredIds[$row["nct_id"]] = '';
        }
    }
    ///////////////////////////////////////// Read All Modifiers/////////////////////////////
    function readModifiers() {
        $query = "SELECT * FROM modifiers WHERE `modifier` != 'NONE'";
        return mysqlReadAll($query);
    }
  
    function calculateCnts() {
        global $conditions;
        global $modifiers;
        global $filteredIds;

        foreach($conditions as $key => $condition) {
            $nCnt = 0;
            foreach($conditions[$key]["studyIds"] as $id) {
                if ( isset($filteredIds[$id]) ) {
                    $nCnt++;
                }
            }
            unset($conditions[$key]["studyIds"]);
            $conditions[$key]["count"]["All"] = $nCnt;
            foreach($modifiers as $modifier) {
                $studyIds = getStudyIds($key, $modifier["id"]);
                $nCnt = 0;
                foreach($studyIds as $id) {
                    if ( isset($filteredIds[$id]) ) {
                        $nCnt++;
                    }
                }
                $conditions[$key]["count"][$modifier["modifier"]] = $nCnt;
            }
        }
        
    }
?>