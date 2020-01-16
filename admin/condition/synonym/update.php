<?php
require_once "../../../db_connect.php";

    $id = $_POST["id"];
    $newVal = $_POST["newVal"];
    $query = "UPDATE `conditions` SET `synonym` = '$newVal' WHERE `id` = '$id'";
    mysqli_query($conn, $query);
    mysqli_close($conn);
?>