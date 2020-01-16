<?php 
    require_once 'vendor/autoload.php';
    require_once 'enable_error_report.php';
    use Ozdemir\Datatables\DB\MySQL;
    use Ozdemir\Datatables\Datatables;

    $ini = parse_ini_file("app.ini");

    $config = [ 'host'     => $ini["db_host"],
                'port'     => $ini["db_port"],
                'username' => $ini["db_user"],
                'password' => $ini["db_password"],
                'database' => $ini["db_name"] ];

    $dt = new Datatables( new MySQL($config) );

    $query = "SELECT `nct_id`, `title`, `enrollment`, `status`, `study_types`, `conditions`, `interventions`, `outcome_measures`, `phases`, `study_designs` FROM studies ";
    if (isset($_POST) && isset($_POST["manual_search"])) {
        $manualSearch = $_POST["manual_search"];
        $searchQuery = generateQueryCondition($manualSearch);

        if (strlen($searchQuery) > 0) {
            $query .= " WHERE $searchQuery";
        }
    }
    $query .= " ORDER BY nct_id DESC";
    $dt->query($query);
    
    $dt->edit('nct_id', function ($data) {
        $nctPattern = "NCT00000000";
        return substr($nctPattern, 0, -strlen($data['nct_id'])) . $data['nct_id'];
    });

    $dt->edit('title', function ($data) {
        $url = "https://ClinicalTrials.gov/show/NCT00000000";
        $url = substr($url, 0, -strlen($data['nct_id'])) . $data['nct_id'];
        return '<a href="' . $url . '">' . $data["title"] . '</a>';
    });

    $dt->edit('conditions', function ($data) {
        if(strlen($data["conditions"]) < 1) {
            return "";
        }
        $conditionHtml = '<ul>';
        $arrCondition = explode("|", $data["conditions"]);
        foreach($arrCondition as $condition) {
            $conditionHtml .= '<li>' . getValue($condition) . '</li>';
        }
        $conditionHtml .= '</ul>';
        return $conditionHtml;
    });

    $dt->edit('phases', function ($data) {
        if(strlen($data["phases"]) < 1) {
            return "";
        }
        $phaseHtml = '<ul>';
        $arrPhases = explode(",", $data["phases"]);
        foreach($arrPhases as $phase) {
            $phaseHtml .= "<li>$phase</li>";
        }
        $phaseHtml .= '</ul>';
        return $phaseHtml;
    });

    $dt->edit('interventions', function ($data) {
        return editTypeValColumn($data["interventions"]);
    });

    $dt->edit('outcome_measures', function ($data) {
        if(strlen($data["outcome_measures"]) < 1) {
            return "";
        }
        $html = '<ul>';
        $array = explode("|", $data["outcome_measures"]);
        foreach($array as $item) {
            $html .= "<li>$item</li>";
        }
        $html .= '</ul>';
        return $html;
    });

    $dt->edit('study_designs', function ($data) {
        return editTypeValColumn($data["study_designs"]);
    });
    
    // $dt->add('rank', function () {
    //     return '';
    // });

    echo $dt->generate();

    function getValue($val) {
        if ($val=='""') {
            return "";
        }
        return trim(strtolower(str_replace("'", "\'", str_replace("\\", "\\\\", $val))));
    }

    function editTypeValColumn($data) {
        if(strlen($data) < 1) {
            return "";
        }
        $arrRes = array();
        $arrOrg = explode("|", $data);
        foreach($arrOrg as $item) {
            $tmp = explode(":", $item);
            $type = $tmp[0];
            $val = $tmp[1];

            if(!isset($arrRes["$type"])) {
                $arrRes["$type"] = array();
            }
            array_push($arrRes["$type"], $val);
        }

        $html = '';
        foreach($arrRes as $key => $items) {
            $html = '<label class="font-bold">' . $key . '</label>';
            $html .= '<ul>';
            foreach($items as $item) {
                $html .= '<li>' . $item . '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function generateQueryCondition($manualSearch) {
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
        if (isset($manualSearch["condition"])) {
            $arrIds = getIdFromCondition($manualSearch["condition"]);
            $ids = "('" . implode("','",$arrIds) . "')";
            array_push($arrQuery, "nct_id IN $ids");
        }
        return implode(" AND ", $arrQuery);
    }
?>