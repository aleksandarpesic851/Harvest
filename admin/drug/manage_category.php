<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    if (!isset($_POST)) {
        if (isset($_GET) && isset($_GET["id"])) {
            $categoryID = $_GET["id"];
            $categoryName = $_GET["value"];
            if (checkExist($categoryName)) {
                echo "fail";
            } else {
                $query = "UPDATE `drug_categories` SET `category` = '$categoryName' WHERE `id` = '$categoryID'";
                mysqli_query($conn, $query);
                echo "ok";
            }
        }
        exit;
    }

    if (isset($_POST["action"])) {
        $action = $_POST["action"];
    } else if(isset($_POST["id"])) {
        $categoryID = $_POST["id"];
        $categoryName = $_POST["value"];
        if (checkExist($categoryName)) {
            echo $categoryName;
        } else {
            $query = "UPDATE `drug_categories` SET `category` = '$categoryName' WHERE `id` = '$categoryID'";
            mysqli_query($conn, $query);
            echo $categoryName;
        }
        exit;
    }
    

    switch($action) {
        case "Create":
            $newCategory = $_POST["category"];
            if (checkExist($newCategory)) {
                echo "exist";
                exit;
            }
            $query = "INSERT INTO `drug_categories` (`category`) VALUES ('$newCategory')";
            mysqli_query($conn, $query);
            $newCategoryId = mysqli_insert_id($conn);
            
            // check drug is already exist
            $query = "SELECT * FROM `drugs` WHERE `drug_name` = '$newCategory'";
            $result = mysqli_query($conn, $query);
            
            // if exist, update
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                $drugId = $row["id"];
                $query = "INSERT INTO `drug_hierarchy` (`drug_id`, `parent_id`, `category_id`) VALUES ('$drugId', '0', '$newCategoryId'); ";
                mysqli_query($conn, $query);
                $drugHierarchyID = mysqli_insert_id($conn);
            } 
            // else, insert
            else {
                $query = "INSERT INTO `drugs` (`drug_name`) VALUES ('$newCategory')";
                mysqli_query($conn, $query);
                $drugId = mysqli_insert_id($conn);
                $query = "INSERT INTO `drug_hierarchy` (`drug_id`, `parent_id`, `category_id`) VALUES ('$drugId', '0', '$newCategoryId'); ";
                mysqli_query($conn, $query);
                $drugHierarchyID = mysqli_insert_id($conn);
            }
            
            $result = ["category" => $newCategoryId, "drug" => $drugHierarchyID];
            echo json_encode($result);
        break;
        case "Delete":
            $categoryID = $_POST["id"];
            $query = "DELETE FROM `drug_categories` WHERE `id` = '$categoryID'";
            mysqli_query($conn, $query);
            $query = "DELETE FROM `drug_hierarchy` WHERE `category_id` = '$categoryID'";
            mysqli_query($conn, $query);
            echo "delete_ok";
        break;
    }

    mysqli_close($conn);

    function checkExist($category) {
        global $conn;

        $sql = "SELECT * FROM `drug_categories` WHERE `category` = '$category'";
        $result = mysqli_query($conn, $sql);
        
        // if there is category already, return true.
        if (mysqli_num_rows($result) > 0) {
            return true;
        }
        mysqli_free_result($result);
        // //if same drug is used in other category, return true
        // $sql = "SELECT * FROM `drugs` WHERE `drug_name` = '$category'";
        // $result = mysqli_query($conn, $sql);
        // if (mysqli_num_rows($result) > 0) {
        //     $row = mysqli_fetch_assoc($result);
        //     mysqli_free_result($result);
        //     if ($row["categoryid"] > 0) {
        //         return true;
        //     }
        // }

        return false;

    }

?>