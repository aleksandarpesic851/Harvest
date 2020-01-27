<?php
    // require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    // require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    $ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/app.ini");

    $servername = $ini["db_host"];
    $username = $ini["db_user"];
    $password = $ini["db_password"];
    $dbname = $ini["db_name"];
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

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