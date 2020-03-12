<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/modifier/read_modifiers.php";

    $response = readModifierNamesAsKey();
    echo json_encode($response);
?>