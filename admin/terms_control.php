<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/runPHPBackground.php";
    
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/admin/terms.php";
    executeBackgroundScript($targetPath);
    echo "Extracting was started...";
?>