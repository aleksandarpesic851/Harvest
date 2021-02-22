<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    if(isset($_POST["comment"])) {
        $feedback = $_POST["comment"];
        $query = "INSERT INTO `feedbacks` (`feedback`, `created_at`) VALUES ('$feedback', now())";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        echo "ok";
    }
?>