<?php
include "../../db_connect.php";
include "../../enable_error_report.php";

    if (!isset($_POST)) {
        return;
    }

    $action = $_POST["action"];

    switch($action) {
        case "Create":
            $newCategory = $_POST["category"];
            if (checkExist($newCategory)) {
                echo "exist";
                exit;
            }
            $query = "INSERT INTO `condition_categories` (`category`) VALUES ('$newCategory')";
            mysqli_query($conn, $query);
            $newCategoryId = mysqli_insert_id($conn);
            
            // Update condition table too
            $query = "INSERT INTO `conditions` (`condition`, `parentid`, `categoryid`) VALUES ('$newCategory', '0', '$newCategoryId')";
            $query .= " ON DUPLICATE KEY UPDATE `condition`='$newCategory', `parentid`='0', `categoryid`='$newCategoryId'";
            
            mysqli_query($conn, $query);
            $conditionId = mysqli_insert_id($conn);

            $result = ["category" => $newCategoryId, "condition" => $conditionId];
            echo json_encode($result);
        break;

    }

    mysqli_close($conn);

    function checkExist($category) {
        global $conn;

        $sql = "SELECT * FROM `condition_categories` WHERE `category` = '$category'";
        $result = mysqli_query($conn, $sql);
        
        // if there is category already, return true.
        if ($result->num_rows > 0) {
            return true;
        }
        mysqli_free_result($result);
        //if same condition is used in other category, return true
        $sql = "SELECT * FROM `conditions` WHERE `condition` = '$category'";
        $result = mysqli_query($conn, $sql);
        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row["categoryid"] > 0) {
                return true;
            }
        }

        return false;

    }

?>