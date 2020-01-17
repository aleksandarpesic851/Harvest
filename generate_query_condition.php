<?php
    require_once "db_connect.php";
    require_once "enable_error_report.php";

    function generateOtherSearchQuery($manualSearch) {
        $arrQuery = array();
        if ( isset($manualSearch["search-title"])) {
            array_push($arrQuery, "`title` LIKE '%" . $manualSearch["search-title"] . "%'");
        }
        if ( isset($manualSearch["search-measure"])) {
            array_push($arrQuery, "`outcome_measures` LIKE '%" . $manualSearch["search-measure"] . "%'");
        }
        if ( isset($manualSearch["search-design"])) {
            array_push($arrQuery, "`study_designs` LIKE '%" . $manualSearch["search-design"] . "%'");
        }
        if ( isset($manualSearch["search-type"])) {
            array_push($arrQuery, "`study_types` = '" . $manualSearch["search-type"] . "'");
        }
        if ( isset($manualSearch["search-sex"])) {
            array_push($arrQuery, "( `gender` = '" . $manualSearch["search-sex"] . "' OR `gender` = 'All' )");
        }
        if ( isset($manualSearch["search-start"])) {
            $tmpArray = explode(" - ", $manualSearch["search-start"]);
            $from = date("Y-m-d", strtotime($tmpArray[0]));
            $to = date("Y-m-d", strtotime($tmpArray[1]));
            array_push($arrQuery, "`start_date` >= '$from' AND `start_date` <= '$to'");
        }
        if ( isset($manualSearch["search-complete"])) {
            $tmpArray = explode(" - ", $manualSearch["search-complete"]);
            $from = date("Y-m-d", strtotime($tmpArray[0]));
            $to = date("Y-m-d", strtotime($tmpArray[1]));
            array_push($arrQuery, "`completion_date` >= '$from' AND `completion_date` <= '$to'");
        }
        if ( isset($manualSearch["search-first-post"])) {
            $tmpArray = explode(" - ", $manualSearch["search-first-post"]);
            $from = date("Y-m-d", strtotime($tmpArray[0]));
            $to = date("Y-m-d", strtotime($tmpArray[1]));
            array_push($arrQuery, "`study_first_posted` >= '$from' AND `study_first_posted` <= '$to'");
        }
        if ( isset($manualSearch["search-last-post"])) {
            $tmpArray = explode(" - ", $manualSearch["search-last-post"]);
            $from = date("Y-m-d", strtotime($tmpArray[0]));
            $to = date("Y-m-d", strtotime($tmpArray[1]));
            array_push($arrQuery, "`last_update_posted` >= '$from' AND `last_update_posted` <= '$to'");
        }
        if ( isset($manualSearch["search-age-from"])) {
            array_push($arrQuery, "`min_age` <= " . $manualSearch["search-age-from"]);
        }
        if ( isset($manualSearch["search-age-group"])) {
            $ageGroups = $manualSearch["search-age-group"];
            if (is_array($ageGroups)) {
                $subQuery = array();
                foreach($ageGroups as $group) {
                    array_push($subQuery, "FIND_IN_SET('$group', `age_groups`)");
                }
                array_push($arrQuery, "( " . implode(" OR ", $subQuery) . " )");
            } else {
                array_push($arrQuery, "FIND_IN_SET('$ageGroups', `age_groups`)");
            }
            
        }
        if ( isset($manualSearch["search-status"])) {
            $statuses = $manualSearch["search-status"];
            if (is_array($statuses)) {
                $subQuery = array();
                foreach($statuses as $status) {
                    array_push($subQuery, "`status` = '$status'");
                }
                array_push($arrQuery, "( " . implode(" OR ", $subQuery) . " )");
            } else {
                array_push($arrQuery, "`status` = '$statuses'");
            }
        }
        if ( isset($manualSearch["search-phase"])) {
            $phases = $manualSearch["search-phase"];
            if (is_array($phases)) {
                $subQuery = array();
                foreach($phases as $phase) {
                    array_push($subQuery, "FIND_IN_SET('$phase', `phases`)");
                }
                array_push($arrQuery, "( " . implode(" OR ", $subQuery) . " )");
            } else {
                array_push($arrQuery, "FIND_IN_SET('$phases', `phases`)");
            }
        }
        
        return implode(" AND ", $arrQuery);
    }

    function generateConditionForTable($manualSearch) {
        global $conn;

        $conditions = $manualSearch["conditions"];

        // If condition is not specified, not calculate
        if (!isset($conditions) || count($conditions) < 1) {
            return "";
        }
        // If All conditions are checked, not calculate
        if ($conditions[0]["nodeId"] == "ROOT") {
            return "";
        }

        $arrStudyIds = array();
        foreach($conditions as $condition) {
            $nodeId = substr($condition["nodeId"], 10);
            $query = "SELECT `study_ids` FROM condition_hierarchy_modifier_stastics WHERE `hierarchy_id` = $nodeId AND `modifier_id` = 1";
            $statistics = mysqlReadFirst($query);
            if (!isset($statistics)) {
                continue;
            }
            $studyIds = explode(",", $statistics["study_ids"]);
            rsort($studyIds);
            if (count($arrStudyIds) < 1) {
                $arrStudyIds = $studyIds;
            } else {
                $arrStudyIds = mergeArray($arrStudyIds, $studyIds);
            }
        }
        rsort($arrStudyIds);
        $ids = "(" . implode(",",$arrStudyIds) . ")";
        return "nct_id IN $ids";
    }

    function mysqlReadAll($query) {
        global $conn;
        
        $result = mysqli_query($conn, $query);
        if ($result->num_rows < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);
        return isset($data) ? $data : array();
    }

    function mysqlReadFirst($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
            
        // if exist, update
        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return $row;
        }
    }

    function mergeArray($array1, $array2) {
        if (count($array1) < 1) {
            return $array2;
        }
        if (count($array2) < 1) {
            return $array1;
        }
        $merged = $array1;
        foreach($array2 as $val2) {
            if (!in_array($val2, $array1)) {
                array_push($merged, $val2);
            }
        }
        return $merged;
    }
?>