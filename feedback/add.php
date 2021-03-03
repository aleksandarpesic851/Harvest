<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    if(isset($_POST["comment"])) {
        $feedback = validate_input($_POST["comment"]);
        $query = "INSERT INTO `feedbacks` (`feedback`, `created_at`) VALUES ('$feedback', now())";
        mysqli_query($conn, $query);
        mysqli_close($conn);
        echo "ok";
    }

    function validate_input($data) {
        $regex = "/[^A-Za-z0-9.,!@#$%_&*()? ]+/";
        $data = trim($data);
        $data = stripslashes($data);
        $data = preg_replace($regex, '', $data);
        $data = htmlspecialchars($data);
        return $data;
      }
    
?>