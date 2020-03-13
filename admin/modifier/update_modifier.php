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
            echo addModifier($_POST["modifier"]);
        break;
        case "UPDATE":
            echo updateModifier($_POST["currentId"], $_POST["modifier_name"]);
        break;
        case "DELETE":
            echo deleteModifier($_POST["currentId"]);
        break;
    }

    function addModifier($modifier) {
        global $conn;

        $modifiers = readModifierNamesAsKey();
        if (isset($modifiers[strtolower($modifier)]))
        {
            return "exist";
        }
        $modifier = strtolower($modifier);
        $query = "INSERT INTO `modifiers` (`modifier`) VALUES ('$modifier')";
        mysqli_query($conn, $query);
        mysqli_close($conn);

        return "ok";
    }

    function updateModifier($id, $modifier) {
        global $conn;

        $query = "UPDATE `modifiers` SET `modifier` = '$modifier' WHERE `id` = '$id'";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        return "ok";
    }

    function deleteModifier($id) {
        global $conn;

        $query = "DELETE FROM `modifiers` WHERE `id` = '$id'";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        return "ok";
    }
?>