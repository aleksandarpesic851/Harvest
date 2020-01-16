<?php
    require_once "db_connect.php";
    require_once "enable_error_report.php";

    $arrData = getCategories();

    if (!isset($arrData)) {
        $arrData = array();
    }

    foreach($arrData as $key => $category) {
        $arrData[$key]["nodeId"] = "category_" . $arrData[$key]["nodeId"];
        $arrData[$key]["nodeChild"] = getCategoryData($category["nodeId"]);
    }
    mysqli_close($conn);
    
    $rootNode["nodeId"] = "ROOT";
    $rootNode["nodeText"] = "All";
    $rootNode["nodeChild"] = $arrData;

    $response = array();
    array_push($response, $rootNode);
    echo json_encode($response);

    //Read all condition category
    function getCategories() {
        global $conn;

        $query = "SELECT `id` AS `nodeId`, `category` AS `nodeText` FROM condition_categories";
        $result = mysqli_query($conn, $query);
        if ($result->num_rows < 1) {
            return;
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        return $arrData;
    }

    function getCategoryData($categoryID) {
        global $conn;

        // Get Category Root IDS
        $query = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `parent_id` from condition_hierarchy_view WHERE `category_id` = $categoryID AND `parent_id` = 0 ";
        $result = mysqli_query($conn, $query);
        
        if ($result->num_rows < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($data as $key => $parent) {
            $data[$key]["nodeChild"] = getChildren($parent["nodeId"]);
        }
        return $data;
    }

    function getChildren($parentId) {
        global $conn;
        
        $sql = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `parent_id` FROM `condition_hierarchy_view` WHERE `parent_id` = $parentId";
        $result = mysqli_query($conn, $sql);
        if ($result->num_rows < 1) {
            return array();
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($arrData as $key=>$data) {
            $arrData[$key]["nodeChild"] = getChildren($data["nodeId"]);
        }
        return $arrData;
    }
?>