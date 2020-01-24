<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/runPHPBackground.php";
    
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/admin/scrape.php";
    executeBackgroundScript($targetPath);
    echo "Scrape was started...";
?>