<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/runPHPBackground.php";
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/admin/drug/calculate_study_drug.php";
    executeBackgroundScript($targetPath);
    echo "ok";
?>