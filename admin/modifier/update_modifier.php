<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    require_once "read_modifiers.php";

    if (!isset($_POST) || !isset($_POST["action"])) {
        echo "Invalid Parameters";
        exit;
    }

    $action = $_POST["action"];
    switch($action) {
        case "ADD":
            echo addModifier($_POST["modifier"], $_POST["category"]);
        break;
        case "UPDATE":
            echo updateModifier($_POST["currentId"], $_POST["modifier_name"], $_POST["category"]);
        break;
        case "DELETE":
            echo deleteModifier($_POST["currentId"], $_POST["category"]);
        break;
    }

    function addModifier($modifier, $category) {
        global $conn;

        $modifiers = readModifierNamesAsKey($category);
        if (isset($modifiers[strtolower($modifier)]))
        {
            return "exist";
        }
        $modifier = strtolower($modifier);
        $query = "INSERT INTO `modifiers` (`modifier`, `category`) VALUES ('$modifier', $category)";
        mysqli_query($conn, $query);
        mysqli_close($conn);

        return "ok";
    }

    function updateModifier($id, $modifier, $category) {
        global $conn;
        $modifiers = readModifierNamesAsKey($category);
        if (isset($modifiers[strtolower($modifier)]))
        {
            return "exist";
        }
        
        $query = "UPDATE `modifiers` SET `modifier` = '$modifier' WHERE `id` = '$id' AND `category` = $category";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        return "ok";
    }

    function deleteModifier($id, $category) {
        global $conn;

        $query = "DELETE FROM `modifiers` WHERE `id` = '$id' AND `category` = $category";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        return "ok";
    }
?>