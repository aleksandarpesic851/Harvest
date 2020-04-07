<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/modifier/read_modifiers.php";
    
    $pageNum = isset($_POST["page"]) ? $_POST["page"] - 1 : 0;
    $searchKey = isset($_POST["search"]) ? $_POST["search"] : "";
    $conditionCnt = isset($_POST["cnt"]) ? $_POST["cnt"] : 100;
    $arrData = array();
    $category = isset($_POST["category"]) ? $_POST["category"] : 0;

    $modifiers = readModifierNames($category);

    $query = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText` FROM conditions WHERE `is_active` = 1 AND  `condition_name` like '%$searchKey%' AND `condition_name` NOT IN ('" . implode("', '", $modifiers) . "') ORDER BY `condition_name` LIMIT $conditionCnt OFFSET " . $conditionCnt * $pageNum;
    // $query = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `synonym` FROM conditions WHERE `is_active` = 1 AND  `condition_name` like '%$searchKey%' AND `condition_name` NOT IN ('" . implode("', '", $modifiers) . "') ORDER BY `condition_name` LIMIT $conditionCnt OFFSET " . $conditionCnt * $pageNum;
    
    $stmt = $conn->prepare($query);

    if ($stmt != false) {
        $stmt->execute();
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($arrData, $row);
            }
        }
        $stmt->close();
    }
    //var_dump($arrData);
    mysqli_close($conn);

    echo json_encode($arrData, JSON_INVALID_UTF8_IGNORE);
?>