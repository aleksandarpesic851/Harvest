<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    if(isset($_POST["treeId"])) {
        $arrData = getCategoryData($_POST["treeId"]);
    } else {
        $arrData = getCategories();

        if (!isset($arrData)) {
            $arrData = array();
        }
    
        foreach($arrData as $key => $category) {
            $arrData[$key]["drugs"] = getCategoryData($category["id"]);
        }
    }
    
    mysqli_close($conn);
    echo json_encode($arrData, JSON_INVALID_UTF8_IGNORE);

    //Read all drug category
    function getCategories() {
        global $conn;

        $query = "SELECT * FROM drug_categories";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) < 1) {
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
        $query = "SELECT `id` AS `nodeId`, `drug_name` AS `nodeText`, `parent_id`, `category_id` AS `nodeCategory` from drug_hierarchy_view WHERE `category_id` = $categoryID AND `parent_id` = 0 ";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($data as $key => $parent) {
            $data[$key]["expanded"] = true;
            $data[$key]["nodeChild"] = getChildren($parent["nodeId"]);
        }
        return $data;
    }

    function getChildren($parentId) {
        global $conn;
        
        $sql = "SELECT `id` AS `nodeId`, `drug_name` AS `nodeText`, `parent_id`, `category_id` AS `nodeCategory` FROM `drug_hierarchy_view` WHERE `parent_id` = $parentId";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($arrData as $key=>$data) {
            $arrData[$key]["expanded"] = true;
            $arrData[$key]["nodeChild"] = getChildren($data["nodeId"]);
        }
        return $arrData;
    }
?>