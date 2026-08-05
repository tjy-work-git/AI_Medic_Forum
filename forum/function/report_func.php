<?php

session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;

if (!$logID) {
    header("Location: ../../user/login.php");
    exit;
} else if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['reason'])) {
    header("Location: ../main.php");
    exit;
} else {
    $origin = $_POST['origin'];
    
    include('../../resource/conn.php');

    $stmt = $conn->prepare("INSERT INTO Report (reportDesc, reportType, reportNo, userID, status)
                    VALUES (:description, :type, :no, :userID, 'Pending')  ");
    $stmt->bindParam(':description', $_POST['reason']);
    $stmt->bindParam(':type', $_POST['reportType']);
    $stmt->bindParam(':no', $_POST['reportNo']);
    $stmt->bindParam(':userID', $logID);
    $stmt->execute();

    header("Location: ../post.php?postID=$origin");
}

$conn = null;
?>

