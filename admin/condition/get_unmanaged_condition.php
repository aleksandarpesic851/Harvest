<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/enable_error_report.php";

    $pageNum = isset($_POST["page"]) ? $_POST["page"] - 1 : 1;
    $searchKey = isset($_POST["search"]) ? $_POST["search"] : "";
    $conditionCnt = isset($_POST["cnt"]) ? $_POST["cnt"] : 100;
    $arrData = array();

    $query = "SELECT `id` AS `nodeId`, `condition_name` AS `nodeText`, `synonym` FROM conditions WHERE `condition_name` like '%$searchKey%' ORDER BY `condition_name` LIMIT $conditionCnt OFFSET " . $conditionCnt * $pageNum;
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
    
    mysqli_close($conn);

    echo json_encode($arrData);
?>