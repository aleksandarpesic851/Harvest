<?php

require_once "../../db_connect.php";
require_once "../../enable_error_report.php";

    if (!isset($_POST) || !isset($_POST["action"])) {
        exit;
    }

    switch($_POST["action"]) {
        case "UPDATE PARENT":
            $currentId = $_POST["currentId"];
            $parentId = isset($_POST["parentId"]) ? $_POST["parentId"] : 0;
            $prev_parentId = isset($_POST["prev_parentId"]) ? $_POST["prev_parentId"] : -1;
            $category = $_POST["category"];
            $prev_category = $_POST["prev_category"];
        
            // if dropped on unmanaged category, delete the node in hierarchy.
            if ($category == 0) {
                if ($prev_category != 0) {
                    $query = "DELETE FROM `condition_hierarchy` WHERE `id` = $currentId";
                    mysqli_query($conn, $query);
                }
                break;
            }

            //if category is equal with prev category, update parent id
            if ($category == $prev_category) {
                $query = "UPDATE `condition_hierarchy` SET `parent_id` = $parentId WHERE `id` = '$currentId'";
                mysqli_query($conn, $query);
                break;
            }

            if ($prev_category == 0) {
                $condition_id = $currentId;
            } else {
                $query = "SELECT condition_id FROM condition_hierarchy WHERE id=$currentId;";
                $result = mysqli_query($conn, $query);
                // if exist, update
                if ($result->num_rows < 1) {
                    break;
                }
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                $condition_id = $row["condition_id"];
            }
            $query = "INSERT INTO `condition_hierarchy` (`condition_id`, `parent_id`, `category_id`) VALUES ('$condition_id', '$parentId', '$category'); ";
            mysqli_query($conn, $query);
            break;
            
            // // If current node has child nodes, update all childs's categoryid.
            // if ($hasChildren) {
            //     $children = getChildren($currentId);
            //     if(isset($children)) {
            //         mysqli_autocommit($conn,FALSE);
            //         updateCategories($children, $category);
            //         mysqli_commit($conn);
    
            //         echo json_encode($children);
            //     }
            // }
        break;

        case "UPDATE TEXT":
            $editedId = $_POST["editedId"];
            $newText = $_POST["newText"];
            $category = $_POST["categoryId"];
            if ($category == 0) {
                $condition_id = $editedId;
            } else {
                $query = "SELECT condition_id FROM condition_hierarchy WHERE id=$editedId;";
                $result = mysqli_query($conn, $query);
                // if exist, update
                if ($result->num_rows < 1) {
                    break;
                }
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                $condition_id = $row["condition_id"];
            }
            $query = "UPDATE `conditions` SET `condition_name` = '$newText' WHERE `id` = '$condition_id'";
            mysqli_query($conn, $query);
        break;
    }

    mysqli_close($conn);

    // function getChildren($parentId) {
    //     global $conn;
        
    //     $sql = "SELECT * FROM `conditions` WHERE `parentid` =$parentId";
    //     $result = mysqli_query($conn, $sql);
    //     if ($result->num_rows < 1) {
    //         return;
    //     }
    //     // Fetch all
    //     $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //     // Free result set
    //     mysqli_free_result($result);

    //     foreach($arrData as $key=>$data) {
    //         $arrData[$key]["nodeChild"] = getChildren($data["id"]);
    //     }
    //     return $arrData;
    // }

    // function updateCategories($children, $category) {
    //     global $conn;

    //     foreach($children as $child) {
    //         $childId = $child["id"];
    //         $query = "UPDATE `conditions` SET `categoryid` = $category WHERE `id` = '$childId'";
    //         mysqli_query($conn, $query);
    //         if (isset($child["nodeChild"])) {
    //             updateCategories($child["nodeChild"], $category);
    //         }
    //     }
    // }
?>