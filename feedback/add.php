<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    if(isset($_POST["content"])) {
        $feedback = $_POST["content"];
        $now = now();
        $query = "INSERT INTO `feedback` (`feedback`, `created_at`) VALUES ('$feedback', $now)";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        echo "ok";
    }
    echo "...";
?>