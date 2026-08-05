<?php
// Change these parameters according to your MySQL account
$servername = "localhost";
$username = "root";
$password = "password";
$database = "FYP_Web";

try {

    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set the PDO error mode to exception
    // echo "Connected Successfully";
} catch (PDOException $e) {
    echo "Connection failed. Reach out to the platform team for issue report.";
}
?>
