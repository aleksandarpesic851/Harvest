<?php
    // In order to speed up, calculate all study ids related all drugs in hierarchy.
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $runningCLI = false;

    if (!isset($rootPath) || strlen($rootPath) < 1) {
        $rootPath = __DIR__ . "/../../";
        $runningCLI = true;
    }
    $logMethodFile = true;
    $drugCalcLogFile = fopen($rootPath . "/logs/calculate_study_drug_log.txt", "w") or die("Unable to open file!");
    fwrite($drugCalcLogFile, date("Y-m-d h:i:sa"));

    if (!isset($isScraping)) {
        require_once $rootPath . "/db_connect.php";
        require_once $rootPath . "/enable_error_report.php";
        require_once $rootPath . "/admin/graph_history.php";
    }

    $log = "\r\n-----------------------Calculate Study Ids Related with Drug Hierarchy----------------------------";
    logOrPrintDrugs($log);

    $totalData = array();
    mysqli_autocommit($conn,FALSE);

    calculateStudyDrugs();

    updateGraphHistory();

    if (!mysqli_commit($conn)) {
        $log = "Commit transaction failed";
        logOrPrintDrugs($log);
    }
    
    if ($logMethodFile) {
        fclose($drugCalcLogFile);
    }

    function calculateStudyDrugs() {
        global $totalData;

        $totalData = readAllDrugHierarchy();
        changeSpecialCaracters_Drug();
        mysqlReconnect();
        calculateDrugStudyIds();
        mergeDrugIds();
        mysqlReconnect();
        saveDrugData();
    }

    function changeSpecialCaracters_Drug() {
        global $totalData;

        foreach($totalData as $key=>$val) {
            $totalData[$key]["drug_name"] = str_replace("'", "\'", $totalData[$key]["drug_name"]);
        }
    }

    function mysqlReadAll_Drug($query) {
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

    function mysqlRowCnt_Drug($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
        $nCnt = mysqli_num_rows($result);
        // Free result set
        mysqli_free_result($result);
        return $nCnt;
    }

    //Read All drugs in hierarchy
    function readAllDrugHierarchy() {
        $query = "SELECT `id`, `drug_name`, `synonym`, `parent_id`, `drug_id` FROM drug_hierarchy_view";
        $hierarchyData = mysqlReadAll_Drug($query);
        foreach($hierarchyData as $key => $element) {
            $hierarchyData[$key]["leaf"] = true;
            foreach($hierarchyData as $subKey => $subElement) {
                if ($element["id"] == $subElement["parent_id"]) {
                    $hierarchyData[$key]["leaf"] = false;
                    break;
                }
            }
        }
        return $hierarchyData;
    }

    // Calculate study ids related with drug name
    function calculateDrugStudyIds() {
        global $totalData;
        $nCnt = 0;
        foreach($totalData as $key=>$drug) {
            $start = time();
            $query = "SELECT `nct_id` FROM study_id_drugs WHERE " . generateSearchString_DRUG('drug', $drug["drug_name"]);
            // $query = "SELECT `nct_id` FROM study_id_drugs WHERE ( `drug` LIKE '%" . $drug["drug_name"] . "%') GROUP BY `nct_id`";
            if (isset($drug["synonym"]) && strlen($drug["synonym"]) > 0) {
                $synonyms = explode(",", $drug["synonym"]);
                foreach($synonyms as $synonym) {
                    $query .= " OR " . generateSearchString_DRUG('drug', trim($synonym));
                }
            }

            $query .=  " GROUP BY `nct_id`";
            $nctIds = mysqlReadAll_Drug($query);
            $totalData[$key]["study_ids"] = array();
            
            foreach($nctIds as $id) {
                $totalData[$key]["study_ids"][$id["nct_id"]] = '';
            }
            
            $end = time();
            $log = "\r\nCalculate Study Id - ". $drug["drug_name"] . " : " . time_elapsed_string_Drug($end-$start) .
                    ", Count: " . count($nctIds);
            logOrPrintDrugs($log);
            $nCnt++;
            if ($nCnt > 30) {
                $nCnt = 0;
                mysqlReconnect();
            }
        }
    }

    function generateSearchString_DRUG($column, $value)
    {
        $res = ' (';
        $res .= '`' . $column . '` LIKE "%' . $value . ' %"';
        $res .= ' OR `' . $column . '` LIKE "%' . $value . ',%"';
        $res .= ' OR `' . $column . '` LIKE "%' . $value . '.%"';
        $res .= ' OR `' . $column . '` LIKE "%' . $value . '"';
		$res .= ' OR `' . $column . '` LIKE "%' . $value . '|%"';
		$res .= ' OR `' . $column . '` LIKE "%' . $value . 's %"';
		$res .= ' OR `' . $column . '` LIKE "%' . $value . 's"';
        $res .=') ';
        return $res;
    }
    // merge Study Ids
    function mergeDrugIds() {

        $log = "Merging...";
        logOrPrintDrugs($log);

        global $totalData;

        // $start = time();
        // foreach($totalData as $key=>$drug) {
        //     if ($drug["parent_id"] == 0) {
        //         mergeParentChild_Drug($key);
        //     }
        // }
        $start = time();
        foreach($totalData as $key => $drug) {
            if (isset($drug["leaf"]) && $drug["leaf"]) {
                mergeChildParent_Drug($key);
            }
        }

        foreach($totalData as $key => $drug) {
            $log = "\r\nCalculate Study Id - ". $drug["drug_name"] . " : "  . count($drug['study_ids']);
            logOrPrintDrugs($log);
        }
        
        $log ="\r\n" . time_elapsed_string_Drug(time()-$start) . "\r\nMerge complete";
        $log = "Merge complete";
        logOrPrintDrugs($log);

    }

    // merge Parent -> Child
    function mergeParentChild_Drug($parentKey) {
        global $totalData;
        $isLeaf = true;
        foreach($totalData as $key => $drug) {
            if ($drug["parent_id"] != $totalData[$parentKey]["id"]) {
                continue;
            }
            $isLeaf = false;
            $totalData[$key]["study_ids"] = mergeArray_Drug($totalData[$key]["study_ids"], $totalData[$parentKey]["study_ids"]);
            mergeParentChild_Drug($key);
        }
        if ($isLeaf) {
            $totalData[$parentKey]["leaf"] = true;
        }
    }

    //merge Child -> Parent
    function mergeChildParent_Drug($childKey) {
        global $totalData;

        // Get Parent Node
        foreach($totalData as $key => $drug) {
            if ($drug["id"] == $totalData[$childKey]["parent_id"])  {
                //$totalData[$key]["study_ids"] = mergeArray_Drug($totalData[$key]["study_ids"], $totalData[$childKey]["study_ids"]);
                if (count($totalData[$key]["study_ids"]) < 1) {
                    $totalData[$key]["study_ids"] = $totalData[$childKey]["study_ids"];
                } else {
                    foreach($totalData[$childKey]["study_ids"] as $studyIdKey => $val) {
                        $totalData[$key]["study_ids"][$studyIdKey] = '';
                    }
                }
                mergeChildParent_Drug($key);
                break;
            }
        }
    }

    function mergeArray_Drug($array1, $array2) {
        $merged = $array1;
        foreach($array2 as $val2) {
            if (!in_array($val2, $array1)) {
                array_push($merged, $val2);
            }
        }
        return $merged;
    }

    function printStudyIdCnts_Drug($explain) {
        global $totalData;
        $log = "\r\n $explain:";
        foreach($totalData as $key=>$drug) {
            $log .= "\r\n" .  $drug["drug_name"] . ": " . count($drug["study_ids"]);
        }
        logOrPrintDrugs($log);
    }
    // Calculate elapsed time
    function time_elapsed_string_Drug($secs){
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
    
    function saveDrugData() {
        global $totalData;
        global $conn;

        foreach($totalData as $data) {
            $studyIds = array_keys($data["study_ids"]);
            $query = "UPDATE `drug_hierarchy` SET `study_ids` = '" . implode(",", $studyIds) . "' WHERE `id` = " . $data["id"];
            mysqli_query($conn, $query);
        }
    }

    function logOrPrintDrugs($log) {
        global $logMethodFile;
        global $drugCalcLogFile;
        global $_POST;

        if (isset($_POST) && isset($_POST["post"])) {
            return;
        }

        if ($logMethodFile) {
            fwrite($drugCalcLogFile, $log);
        } else {
            echo $log;
            //ob_flush();
            flush();
        }
    }
?>