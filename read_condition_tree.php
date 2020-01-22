<?php
    
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/tree_functions.php";
    
    $arrData = array();
    $arrCategory = getCategories();

    if (!isset($arrCategory)) {
        $arrCategory = array();
    }
    $rootNode["nodeId"] = "ROOT";
    $rootNode["nodeText"] = "All";
    $rootNode["nodeChild"] = array();

    foreach($arrCategory as $key => $category) {
        $rootNode["nodeChild"] =array_merge($rootNode["nodeChild"], getCategoryData($category["id"]));
    }
    mysqli_close($conn);
    
    $response = array();
    array_push($response, $rootNode);
    echo json_encode($response);
?>