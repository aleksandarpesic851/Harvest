<?php
    require_once "db_connect.php";
    require_once "enable_error_report.php";
    require_once "tree_functions.php";
    
    $arrData = array();
    $arrCategory = getCategories_Drug();

    if (!isset($arrCategory)) {
        $arrCategory = array();
    }
    $rootNode["nodeId"] = "ROOT";
    $rootNode["nodeText"] = "All";
    $rootNode["nodeChild"] = array();

    foreach($arrCategory as $key => $category) {
        $rootNode["nodeChild"] =array_merge($rootNode["nodeChild"], getCategoryData_Drug($category["id"]));
    }
    mysqli_close($conn);
    
    $response = array();
    array_push($response, $rootNode);
    echo json_encode($response);
?>