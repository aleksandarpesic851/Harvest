<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    $query = "SELECT `modifier` FROM modifiers WHERE id > 1";
    $result = mysqli_query($conn, $query);
    $data = array();
    if (mysqli_num_rows($result) > 1) {
        // Fetch all
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);
    }
    $response = array();
    foreach($data as $modifier) {
        array_push($response, $modifier["modifier"]);
    }
    echo json_encode($response);
?>