<?php
	include "../../db_connect.php";
	include "../../enable_error_report.php";

    $pageNum = isset($_POST["page"]) ? $_POST["page"] - 1 : 1;
    $searchKey = isset($_POST["search"]) ? $_POST["search"] : "";
    $conditionCnt = isset($_POST["cnt"]) ? $_POST["cnt"] : 1000;
    $arrData = array();

    $query = "SELECT `id` AS `nodeId`, `condition` AS `nodeText`, `parentid`, `categoryid` AS `nodeCategory` FROM conditions WHERE `condition` like '%$searchKey%' AND `categoryid` = 0 ORDER BY `id` LIMIT $conditionCnt OFFSET " . $conditionCnt * $pageNum;
    $stmt = $conn->prepare($query);

    if ($stmt != false) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($arrData, $row);
            }
        }
        $stmt->close();
    }
    
    mysqli_close($conn);

    echo json_encode($arrData);
?>