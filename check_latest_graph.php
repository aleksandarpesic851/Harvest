<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/graph_history.php";

    if(isset($_POST['date'])) {
        $date = $_POST['date'];
    } else {
        echo 'Invalid Parameter';
        exit;
    }

    $latestDate = getLatestGraphHistory();
    if(isset($latestDate)) {
        if ($latestDate == $date) {
            echo 'latest';
        } else {
            echo 'Need update';
        }
    } else {
        echo 'No data in history';
    }
