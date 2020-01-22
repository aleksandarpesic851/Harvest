<?php
    //Read all condition category
    function getCategories() {
        global $conn;

        $query = "SELECT `id` FROM condition_categories";
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
        $query = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `parent_id` from condition_hierarchy_view WHERE `category_id` = $categoryID AND `parent_id` = 0 ";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($data as $key => $parent) {
            $data[$key]["nodeChild"] = getChildren($parent["nodeId"]);
            $data[$key]["nodeId"] = "CONDITION-" . $data[$key]["nodeId"];
        }
        return $data;
    }

    function getChildren($parentId) {
        global $conn;

        $sql = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `parent_id` FROM `condition_hierarchy_view` WHERE `parent_id` = $parentId";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($arrData as $key=>$data) {
            $arrData[$key]["nodeChild"] = getChildren($data["nodeId"]);
            $arrData[$key]["nodeId"] = "CONDITION-" . $arrData[$key]["nodeId"];
        }
        return $arrData;
    }

    ////////////////////////////////// Drug ////////////////////////////////////////////////
    //Read all drug category
    function getCategories_Drug() {
        global $conn;

        $query = "SELECT `id` FROM drug_categories";
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

    function getCategoryData_Drug($categoryID) {
        global $conn;

        // Get Category Root IDS
        $query = "SELECT `id` AS `nodeId`, `drug_name` AS `nodeText`, `parent_id` from drug_hierarchy_view WHERE `category_id` = $categoryID AND `parent_id` = 0 ";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($data as $key => $parent) {
            $data[$key]["nodeChild"] = getChildren_Drug($parent["nodeId"]);
            $data[$key]["nodeId"] = "DRUGNODES-" . $data[$key]["nodeId"];
        }
        return $data;
    }

    function getChildren_Drug($parentId) {
        global $conn;

        $sql = "SELECT `id` AS `nodeId`, `drug_name` AS `nodeText`, `parent_id` FROM `drug_hierarchy_view` WHERE `parent_id` = $parentId";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) < 1) {
            return array();
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($arrData as $key=>$data) {
            $arrData[$key]["nodeChild"] = getChildren_Drug($data["nodeId"]);
            $arrData[$key]["nodeId"] = "DRUGNODES-" . $arrData[$key]["nodeId"];
        }
        return $arrData;
    }
?>