<?php

session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;

if (!$logID) {
    header("Location: ../../user/login.php");
    exit;
} else if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: ../main.php");
    exit;
} else {
    if (isset($_SESSION['loggedID'])) {
        $logID = $_SESSION['loggedID'];
    } else if (isset($_COOKIE['loggedID'])) {
        $logID = $_COOKIE['loggedID'];
    }

    include('../../resource/conn.php');

    if (isset($_POST['commID'])) {
        $origin = $_POST['origin'];
        
        $stmt = $conn->prepare("DELETE FROM Comment WHERE commentID = :commID AND userID = :userID");
        $stmt->bindParam(':commID', $_POST['commID']);
        $stmt->bindParam(':userID', $logID);
        $stmt->execute();
        
        $image_folder = "../../resource/img/comment/";
        $image_path = $image_folder . $_POST['photo'];
        unlink($image_path);
        
        header("Location: ../post.php?postID=$origin");
        exit;
    } else if (isset($_POST['postID'])) {
        $stmt = $conn->prepare("DELETE FROM Post WHERE postID = :postID AND userID = :userID");
        $stmt->bindParam(':postID', $_POST['postID']);
        $stmt->bindParam(':userID', $logID);
        $stmt->execute();
        
        $image_folder = "../../resource/img/post/";
        $image_path = $image_folder . $_POST['photo'];
        unlink($image_path);
        
        header("Location: ../main.php");
        exit;
    }
}

$conn = null;
?>

