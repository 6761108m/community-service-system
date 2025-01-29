<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fyp1"; //ubah database name nanti

    // $servername = "localhost";
    // $username = "d032210233";
    // $password = "390";
    // $dbname = "student_d032210233";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error){
        die("Connection failed: ".$conn->connect_error);
    }
    else{
        echo "";
    }
?>