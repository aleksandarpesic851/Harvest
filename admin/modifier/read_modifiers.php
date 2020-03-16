<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    
    if (isset($_POST) && isset($_POST["modifier_table"])) {
        $category = isset($_POST["category"]) ? $_POST["category"] : 0;
        $modifiers = readModifiers($category, false);
        $nCnt = 0;
        foreach($modifiers as $modifier)
        {
            $nCnt++;
            echo '<tr id="' . $modifier["id"] . '"><td>' . $nCnt . '</td><td>' . $modifier["modifier"] . '</td></tr>';
        }
    }

    ///////////////////////////////////////// Read All Modifiers/////////////////////////////
    function readAllModifiers() {
        global $conn;

        $query = "SELECT * FROM modifiers WHERE `modifier` != 'NONE'";

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
    // category: category id.
    // all:      get all modifier or get only speicial modifier for category
    function readModifiers($category = 0, $all = true) {
        global $conn;

        if ($all) {
            $query = "SELECT * FROM modifiers WHERE `modifier` != 'NONE' AND ( `category` = 0 OR `category` = $category)";
        } else {
            $query = "SELECT * FROM modifiers WHERE `modifier` != 'NONE' AND `category` = $category";
        }

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

    function readModifierNamesAsKey($category = 0, $all = true) {
        $modifiers = readModifiers($category, $all);
        $arrModifier = array();
    
        foreach($modifiers as $modifier) {
            $arrModifier[$modifier["modifier"]] = "";
        }
        return $arrModifier;
    }

    function readModifierNames($category = 0, $all = true) {
        $modifiers = readModifiers($category, $all);
        $arrModifier = array();
    
        foreach($modifiers as $modifier) {
            array_push($arrModifier, $modifier["modifier"]);
        }
        return $arrModifier;
    }
?>