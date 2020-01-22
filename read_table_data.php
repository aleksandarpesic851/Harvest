<?php 
    // require_once 'vendor/autoload.php';
    // require_once 'enable_error_report.php';
    
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/generate_query_condition.php";

    // use Ozdemir\Datatables\DB\MySQL;
    // use Ozdemir\Datatables\Datatables;

    // $ini = parse_ini_file("app.ini");

    // $config = [ 'host'     => $ini["db_host"],
    //             'port'     => $ini["db_port"],
    //             'username' => $ini["db_user"],
    //             'password' => $ini["db_password"],
    //             'database' => $ini["db_name"] ];

    // $dt = new Datatables( new MySQL($config) );

    $query = "SELECT `nct_id`, `title`, `enrollment`, `status`, `study_types`, `conditions`, `interventions`, `outcome_measures`, `phases`, `study_designs` FROM studies ";
    $countQuery = "SELECT COUNT(*) AS cnt FROM studies ";

    if (isset($_POST) && isset($_POST["manual_search"])) {
        $manualSearch = $_POST["manual_search"];
        $searchQuery = generateOtherSearchQuery($manualSearch);
        $conditionQuery = generateIDListsforTree($manualSearch);
        if (strlen($searchQuery) > 0 && strlen($conditionQuery) > 0) {
            $searchQuery .= " AND ";
        }
        $searchQuery .= $conditionQuery;
        
        if (strlen($searchQuery) > 0) {
            $query .= " WHERE $searchQuery";
            $countQuery .= " WHERE $searchQuery";
        }
    }
    $result = mysqlReadFirst($countQuery);
    $totalRecords = $result["cnt"];

    $query .= " ORDER BY nct_id DESC ";

    $start = $_POST["start"];
    $length = $_POST["length"];
    $draw = $_POST["draw"];
    $query .= "LIMIT $length OFFSET $start";

    $totalData = mysqlReadAll($query);
    // if (count($totalData) < $length) {
    //     $totalRecords = $start + count($totalData);
    // } else {
    //     $totalRecords = $start + 2 * $length;
    // }
    
    // Edit Columns
    foreach($totalData as $key => $data) {
        // Edit nct id
        $nctPattern = "NCT00000000";
        $totalData[$key]["nct_id"] = substr($nctPattern, 0, -strlen($data['nct_id'])) . $data['nct_id'];

        // Title
        $url = "https://ClinicalTrials.gov/show/NCT00000000";
        $url = substr($url, 0, -strlen($data['nct_id'])) . $data['nct_id'];
        $totalData[$key]["title"] = '<a href="' . $url . '">' . $data["title"] . '</a>';

        // Condition
        if(strlen($data["conditions"]) > 0) {
            $conditionHtml = '<ul>';
            $arrCondition = explode("|", $data["conditions"]);
            foreach($arrCondition as $condition) {
                $conditionHtml .= '<li>' . getValue($condition) . '</li>';
            }
            $conditionHtml .= '</ul>';
            $totalData[$key]["conditions"] = $conditionHtml;
        }
        
        // Phase
        if(strlen($data["phases"]) > 0) {
            $phaseHtml = '<ul>';
            $arrPhases = explode(",", $data["phases"]);
            foreach($arrPhases as $phase) {
                $phaseHtml .= "<li>$phase</li>";
            }
            $phaseHtml .= '</ul>';
            $totalData[$key]["phases"] = $phaseHtml;
        }
      
        // Intervention
        $totalData[$key]["interventions"] = editTypeValColumn($data["interventions"]);

        // Outcome Measures
        if(strlen($data["outcome_measures"]) > 0) {
            $html = '<ul>';
            $array = explode("|", $data["outcome_measures"]);
            foreach($array as $item) {
                $html .= "<li>$item</li>";
            }
            $html .= '</ul>';
            $totalData[$key]["outcome_measures"] = $html;
        }
        
        // Study Design
        $totalData[$key]["study_designs"] = editTypeValColumn($data["study_designs"]);
    };

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecords,
        "aaData" => $totalData
    );

    echo json_encode($response);

    // $dt->edit('nct_id', function ($data) {
    //     $nctPattern = "NCT00000000";
    //     return substr($nctPattern, 0, -strlen($data['nct_id'])) . $data['nct_id'];
    // });

    // $dt->edit('title', function ($data) {
    //     $url = "https://ClinicalTrials.gov/show/NCT00000000";
    //     $url = substr($url, 0, -strlen($data['nct_id'])) . $data['nct_id'];
    //     return '<a href="' . $url . '">' . $data["title"] . '</a>';
    // });

    // $dt->edit('conditions', function ($data) {
    //     if(strlen($data["conditions"]) < 1) {
    //         return "";
    //     }
    //     $conditionHtml = '<ul>';
    //     $arrCondition = explode("|", $data["conditions"]);
    //     foreach($arrCondition as $condition) {
    //         $conditionHtml .= '<li>' . getValue($condition) . '</li>';
    //     }
    //     $conditionHtml .= '</ul>';
    //     return $conditionHtml;
    // });

    // $dt->edit('phases', function ($data) {
    //     if(strlen($data["phases"]) < 1) {
    //         return "";
    //     }
    //     $phaseHtml = '<ul>';
    //     $arrPhases = explode(",", $data["phases"]);
    //     foreach($arrPhases as $phase) {
    //         $phaseHtml .= "<li>$phase</li>";
    //     }
    //     $phaseHtml .= '</ul>';
    //     return $phaseHtml;
    // });

    // $dt->edit('interventions', function ($data) {
    //     return editTypeValColumn($data["interventions"]);
    // });

    // $dt->edit('outcome_measures', function ($data) {
    //     if(strlen($data["outcome_measures"]) < 1) {
    //         return "";
    //     }
    //     $html = '<ul>';
    //     $array = explode("|", $data["outcome_measures"]);
    //     foreach($array as $item) {
    //         $html .= "<li>$item</li>";
    //     }
    //     $html .= '</ul>';
    //     return $html;
    // });

    // $dt->edit('study_designs', function ($data) {
    //     return editTypeValColumn($data["study_designs"]);
    // });
    
    // $dt->add('rank', function () {
    //     return '';
    // });

    // echo $dt->generate();

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
?>