<?php
	include "../../db_connect.php";
	include "../../enable_error_report.php";

    $arrData = getCategories();

    if (!isset($arrData)) {
        $arrData = array();
    }

    foreach($arrData as $key => $category) {
        $arrData[$key]["conditions"] = getCategoryData($category["id"]);
    }
    
    mysqli_close($conn);

    echo json_encode($arrData);

    //Read all condition category
    function getCategories() {
        global $conn;

        $query = "SELECT * FROM condition_categories";
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
        $query = "SELECT `id` AS `nodeId`, `condition` AS `nodeText`, `parentid`, `categoryid` AS `nodeCategory` from conditions WHERE `categoryid` = $categoryID AND `parentid` = 0 ";
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
        
        $sql = "SELECT `id` AS `nodeId`, `condition` AS `nodeText`, `parentid`, `categoryid` AS `nodeCategory` FROM `conditions` WHERE `parentid` = $parentId";
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