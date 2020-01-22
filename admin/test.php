<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/db_connect.php";

    $query = "SELECT rank FROM studies ORDER BY rank";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $currentRank = 0;
        $nCnt = 0;
        $invalidPoints = 0;
        while($row = mysqli_fetch_assoc($result)) {
            if ($row["rank"]-$currentRank > 1) {
                echo "<br> Error Point at $currentRank";
                ob_flush();
                flush();
                $invalidPoints++;
            }
            $nCnt++;
            $currentRank = $row["rank"];
        }
    } else {
        echo "0 results";
    }

    if ($invalidPoints > 0) {
        echo "The scraping was not successed. There are missed data.";
    } else {
        echo "The scraping was successed!!!";
    }
    mysqli_close($conn);