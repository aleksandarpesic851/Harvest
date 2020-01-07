<?php

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

    function startScrape() {
        global $conn;

        $now = date("Y-m-d H:i:s", time());
        $query = "INSERT INTO `update_history` (`updated_at`) VALUES ('$now')";
        
        if (!mysqli_query($conn, $query)) {
            echo mysqli_error($conn);
            return;
        }

        echo "Server is updating...<br>";
        echo '<script type="text/javascript">setTimeout(function() { location.reload(true); }, 1000)</script>';
        
        ob_flush();
        flush();

        include "scrape.php";
    }