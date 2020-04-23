<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    function getLatestGraphHistory() {
        global $conn;

        $query = "SELECT * from graph_update_history ORDER BY id DESC";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if(isset($row)) {
            return $row['updated_at'];
        }
        return null;
    }

    function updateGraphHistory() {
        global $conn;
        $now = date("Y-m-d H:i:s", time());
        $query = "INSERT INTO `graph_update_history` (`updated_at`) VALUES ('$now')";
        
        mysqli_query($conn, $query);
        mysqli_commit($conn);
    }updateGraphHistory();