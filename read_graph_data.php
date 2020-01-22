<?php
    require_once 'enable_error_report.php';
    require_once "db_connect.php";
    require_once 'generate_query_condition.php';

    if (!isset($_POST) || !isset($_POST["conditions"])) {
        echo "Invalid Parameters";
        exit;
    }

    $otherSearch = generateOtherSearchQuery($_POST);

    $conditionTree = $_POST["conditions"];
    $drugTree = $_POST["drugs"];

    $conditions = array();
    $parentConditions = array();// this is used to calculate total study Ids
    getAllConditions($conditionTree);
    
    $drugs = array();
    $parentDrugs = array();
    getAllDrugs($drugTree);

    $isAllCondition = ($conditionTree[0]["nodeId"] == "ROOT");
    $isAllDrug = ($drugTree[0]["nodeId"] == "ROOT");

    $condition_studyIds = array();    // array of key as study id
    $drug_studyIds = array();    // array of key as study id
    $studyIds = array();           // Merged (drug * condition) study Ids
    $studyIdVals = array(); // array of value as study id
    $filteredIds = array(); // array of key as filtered study id

    getAllStudyIds_Condition();
    getAllStudyIds_Drug();
    mergeDrugConditionIds();
    createValArray();

    searchStudies();
    
    $modifiers = readModifiers();
    calculateCnts();

    $response = array();
    $response["conditions"] = $conditions;
    $response["drugs"] = $drugs;

    echo json_encode($response);



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
    
    ////////////////////////////////GET ALL DRUGS////////////////////////////////////////
    function getAllDrugs($drugTree) {
        global $drugs;
        global $parentDrugs;

        if (!isset($drugTree) || count($drugTree) < 1) {
            return array();
        }
        // If All conditions are checked, not calculate
        if ($drugTree[0]["nodeId"] == "ROOT") {
            $drugTree = $drugTree[0]["nodeChild"];
        }

        foreach($drugTree as $node) {
            $key = substr($node["nodeId"], 10);
            $drugs[$key]["drug_name"] = $node["nodeText"];
            array_push($parentDrugs, $key);
            addChildNode_Drug($node);
        }
    }
    function addChildNode_Drug($node) {
        global $drugs;

        if (!isset($node["nodeChild"]) || count($node["nodeChild"]) < 1) {
            return;
        }
        foreach($node["nodeChild"] as $node) {
            $key = substr($node["nodeId"], 10);
            $drugs[$key] = array();
            addChildNode_Drug($node);
        }
    }

    ////////////////////////////EXTRACT study IDs related with condition///////////////////////////////////
    ///////Condition///////
    function getAllStudyIds_Condition() {
        global $conditions;
        global $parentConditions;
        global $condition_studyIds;
        $i=0;
        foreach($conditions as $key=>$condition) {
            $conditions[$key]["studyIds"] = getStudyIds_Condition($key, 1);
            if (in_array($key, $parentConditions)) {
                mergeStudyIds_Condition($conditions[$key]["studyIds"]);
            }
        }
    }
    function getStudyIds_Condition($conditionId, $modifierId) {
        $query = "SELECT `study_ids` FROM condition_hierarchy_modifier_stastics WHERE `hierarchy_id` = $conditionId AND `modifier_id` = $modifierId";
        $statistics = mysqlReadFirst($query);
        $ids = array();
        if (!isset($statistics) || !isset($statistics["study_ids"]) || strlen($statistics["study_ids"]) < 1) {
            return $ids;
        }

        return explode(",", $statistics["study_ids"]);
    }
    
    function mergeStudyIds_Condition($array) {
        global $condition_studyIds;

        foreach($array as $val) {
            $condition_studyIds[$val] = $val;
        }
    }

    ///////Drugs///////
    function getAllStudyIds_Drug() {
        global $drugs;
        global $parentDrugs;
        global $drug_studyIds;
        $i=0;
        foreach($drugs as $key=>$drug) {
            $drugs[$key]["studyIds"] = getStudyIds_Drug($key);
            if (in_array($key, $parentDrugs)) {
                mergeStudyIds_Drug($drugs[$key]["studyIds"]);
            }
        }
    }
    function getStudyIds_Drug($drugId) {
        $query = "SELECT `study_ids` FROM drug_hierarchy WHERE `id` = $drugId";
        $statistics = mysqlReadFirst($query);
        $ids = array();
        if (!isset($statistics) || !isset($statistics["study_ids"]) || strlen($statistics["study_ids"]) < 1) {
            return $ids;
        }

        return explode(",", $statistics["study_ids"]);
    }
    
    function mergeStudyIds_Drug($array) {
        global $drug_studyIds;

        foreach($array as $val) {
            $drug_studyIds[$val] = $val;
        }
    }
    ///////////////////////////////////////Merge ids and generate val array/////////////////////////////////////////////////
    function mergeDrugConditionIds() {
        global $drug_studyIds;
        global $condition_studyIds;
        global $studyIds;
        global $isAllCondition, $isAllDrug;

        if($isAllDrug) {
            if ($isAllCondition) {
                $studyIds = $drug_studyIds;
                foreach($condition_studyIds as $val) {
                    $studyIds[$val] = $val;
                }
            } else {
                $studyIds = $condition_studyIds;
            }
        } else {
            if ($isAllCondition) {
                $studyIds = $drug_studyIds;
            } else {
                $studyIds = array_intersect($condition_studyIds, $drug_studyIds);
            }
        }
        // echo "ok" . count($drug_studyIds) . "," . count($condition_studyIds) . "," . count($studyIds);
        // var_dump($condition_studyIds);
        // echo "<br>" . json_encode($drug_studyIds) . "<br>";
        // echo json_encode($condition_studyIds) . "<br>";
        // echo json_encode($studyIds) . "<br>";
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
        global $drugs;

        // Condition
        foreach($conditions as $key => $condition) {
            $nCnt = 0;
            foreach($conditions[$key]["studyIds"] as $id) {
                if ( isset($filteredIds[$id]) ) {
                    $nCnt++;
                }
            }
            // $conditions[$key]["count"]["All"] = count($conditions[$key]["studyIds"]);
            unset($conditions[$key]["studyIds"]);
            $conditions[$key]["count"]["All"] = $nCnt;
            foreach($modifiers as $modifier) {
                $condition_studyIds = getStudyIds_Condition($key, $modifier["id"]);
                $nCnt = 0;
                foreach($condition_studyIds as $id) {
                    if ( isset($filteredIds[$id]) ) {
                        $nCnt++;
                    }
                }
                $conditions[$key]["count"][$modifier["modifier"]] = $nCnt;
            }
        }
        
        // Drug
        foreach($drugs as $key => $drug) {
            $nCnt = 0;
            foreach($drugs[$key]["studyIds"] as $id) {
                if ( isset($filteredIds[$id]) ) {
                    $nCnt++;
                }
            }
            unset($drugs[$key]["studyIds"]);
            $drugs[$key]["count"]["All"] = $nCnt;
        }
        
    }
?>