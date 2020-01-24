<?php
    $ini = parse_ini_file("app.ini");

    $servername = $ini["db_host"];
    $username = $ini["db_user"];
    $password = $ini["db_password"];
    $dbname = $ini["db_name"];
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    function mysqlReconnect() {
        global $servername, $username, $password, $dbname, $conn;
        mysqli_close($conn);

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }   
    }