<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];

require_once $rootPath . "/db_connect.php";

$runningCLI = false;
if (!isset($rootPath) || strlen($rootPath) < 1) {
    $rootPath = __DIR__ . "/../";
    $runningCLI = true;
}

ExtractObservationsals();


    //Extract Observational study Ids
    function ExtractObservationsals() {
        global $conn;

        $query = "TRUNCATE TABLE ovbservation_studies;";
        mysqli_query($conn, $query);
        mysqli_commit($conn);

        $query = "SELECT `nct_id` from studies WHERE `interventions` = ''";
        
        
        $result = mysqli_query($conn, $query);
        if (!$result || mysqli_num_rows($result) < 1) {
            return;
        }
        // Fetch all
        $data = mysqli_fetch_all($result);
        // Free result set
        mysqli_free_result($result);
        echo count($data);
        $resIDs = "";
        foreach($data as $row)
        {
            if (strlen($resIDs) > 0) {
                $resIDs .= ",";
            }
            $resIDs .= $row[0];
        }
        $query = "INSERT INTO `ovbservation_studies` (`nct_ids`) VALUES ('" . $resIDs . "'); ";
        mysqli_query($conn, $query);
        mysqli_commit($conn);
    }