<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    
    if (isset($_POST) && isset($_POST["modifier_table"])) {
        $modifiers = readModifiers();
        $nCnt = 0;
        foreach($modifiers as $modifier)
        {
            $nCnt++;
            echo '<tr id="' . $modifier["id"] . '"><td>' . $nCnt . '</td><td>' . $modifier["modifier"] . '</td></tr>';
        }
    }

    ///////////////////////////////////////// Read All Modifiers/////////////////////////////
    function readModifiers() {
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

    function readModifierNamesAsKey() {
        $modifiers = readModifiers();
        $arrModifier = array();
    
        foreach($modifiers as $modifier) {
            $arrModifier[$modifier["modifier"]] = "";
        }
        return $arrModifier;
    }

    function readModifierNames() {
        $modifiers = readModifiers();
        $arrModifier = array();
    
        foreach($modifiers as $modifier) {
            array_push($arrModifier, $modifier["modifier"]);
        }
        return $arrModifier;
    }
?>