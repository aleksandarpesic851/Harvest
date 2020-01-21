<?php
// Manage Terms for 
require_once "db_connect.php";
require_once "enable_error_report.php";

    $nCnt = 0;
    $nRows = 1000;
    $conditions = array();

    $query = "SELECT * FROM conditions LIMIT ? OFFSET ?;";

    while(true) {
        $offset = $nCnt*$nRows;
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
        break;
        }

        $stmt->bind_param("ii", $nRows, $offset);
        $stmt->execute();

        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {
            while($row = $result->fetch_assoc()) {
                pushData($row["condition"]);
            }
        } else {
            $stmt->close();
        break;
        }
        $nCnt++;
        $stmt->close();
    }

    print_r(count($conditions));
    echo implode(", ", $conditions);

    function pushData($newData) {
        global $conditions;
        $newData = str_replace('"', '', $newData);
        if (substr($newData, 0, 1) == "-") {
            $newData = trim(substr($newData, 1));
        }

        $splitedData = explode(",", $newData);
        foreach($splitedData as $item) {
            $data = trim($item);
            $isSame = false;
            // remove xxxs
            if (substr($data, -1) == "s") {
                $val = substr($data, 0, strlen($data)-1);
                if (in_array($val, $conditions)) {
                    continue;
                }
            } else {
                $i = 0;
                for ($i = 0 ; $i < count($conditions); $i++) {
                    if ($conditions[$i] == $data) {
                        $isSame = true;
                    break;
                    }
                    if (substr($conditions[$i], -1) == "s" && substr($conditions[$i], 0, strlen($conditions[$i])-1) == $data) {
                        $isSame = true;
                    break;
                    }
                }
            }
            
            if(!$isSame) {
                array_push($conditions, $data);
            }
        }

    }