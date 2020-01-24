<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/runPHPBackground.php";
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/admin/condition/calculate_study_condition.php";
    executeBackgroundScript($targetPath);
    echo "ok";
?>