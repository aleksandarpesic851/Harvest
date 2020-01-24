<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    $query = "SELECT * from update_history ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    if(isset($row)) {
        $updated_at = strtotime($row["updated_at"]); 
        $now = time();
        $diffDate = ($now - $updated_at) / (3600 * 24);
        
        //every 7 days from start server, update data automatically
        if ($diffDate >= 7) {
            startScrape();
        }
    } else {
        startScrape();
    }
    mysqli_close($conn);

    function startScrape() {
        echo '<script type="text/javascript">setTimeout(function() { location.reload(true); }, 1000)</script>';
        //ob_flush();
        flush();
        global $conn;
        require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/scrape_controll.php";
    }