<?php

include "../../db_connect.php";
include "../../enable_error_report.php";

    if (!isset($_POST) || !isset($_POST["action"])) {
        exit;
    }

    switch($_POST["action"]) {
        case "UPDATE PARENT":
            $currentId = $_POST["currentId"];
            $parentId = isset($_POST["parentId"]) ? $_POST["parentId"] : 0;
            $category = $_POST["category"];
            $hasChildren = $_POST["hasChildren"];
        
            if ($parentId == 0) {
                if ($category == 0) {
                    $parentId = -1;
                }
            }
            // Update current dropped node's parentid and categoryid.
            $query = "UPDATE `conditions` SET `parentid` = $parentId, `categoryid` = $category WHERE `id` = '$currentId'";
            mysqli_query($conn, $query);

            // If current node has child nodes, update all childs's categoryid.
            if ($hasChildren) {
                $children = getChildren($currentId);
                if(isset($children)) {
                    mysqli_autocommit($conn,FALSE);
                    updateCategories($children, $category);
                    mysqli_commit($conn);
    
                    echo json_encode($children);
                }
            }
        break;

        case "UPDATE TEXT":
            $editedId = $_POST["editedId"];
            $newText = $_POST["newText"];
            $query = "UPDATE `conditions` SET `condition` = $newText WHERE `id` = '$editedId'";
            mysqli_query($conn, $query);
        break;
    }

    mysqli_close($conn);

    function getChildren($parentId) {
        global $conn;
        
        $sql = "SELECT * FROM `conditions` WHERE `parentid` =$parentId";
        $result = mysqli_query($conn, $sql);
        if ($result->num_rows < 1) {
            return;
        }
        // Fetch all
        $arrData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        // Free result set
        mysqli_free_result($result);

        foreach($arrData as $key=>$data) {
            $arrData[$key]["nodeChild"] = getChildren($data["id"]);
        }
        return $arrData;
    }

    function updateCategories($children, $category) {
        global $conn;

        foreach($children as $child) {
            $childId = $child["id"];
            $query = "UPDATE `conditions` SET `categoryid` = $category WHERE `id` = '$childId'";
            mysqli_query($conn, $query);
            if (isset($child["nodeChild"])) {
                updateCategories($child["nodeChild"], $category);
            }
        }
    }
?>