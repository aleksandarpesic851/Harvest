<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/modifier/read_modifiers.php";
    $response = readAllModifiers();
    echo json_encode($response, JSON_INVALID_UTF8_IGNORE);
?>