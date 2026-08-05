<?php

session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;

if (!$logID) {
    header("Location: ../../user/login.php");
    exit;
}else if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_POST['comment'])) {
    header("Location: ../main.php");
    exit;
} else {
    $postID = $_POST['postID'];
    
    if (!empty($_FILES['img']['name'])) {
        $image_name = $_FILES["img"]["name"];
        $image_tmp = $_FILES["img"]["tmp_name"];
        $image_folder = "../../resource/img/comment/";
        $image_path = $image_folder . $image_name;

        if (!file_exists($image_folder)) {
            mkdir($image_folder, 0775, true);
        }

        if (move_uploaded_file($image_tmp, $image_path)) {
            include('../../resource/conn.php');

            $stmt = $conn->prepare("INSERT INTO Comment (description, commPhoto, userID, postID)
                        VALUES (:description, :commPhoto, :userID, :postID)  ");
            $stmt->bindParam(':description', $_POST['comment']);
            $stmt->bindParam(':commPhoto', $image_name);
            $stmt->bindParam(':userID', $logID);
            $stmt->bindParam(':postID', $postID);
            $stmt->execute();
        } else {
            echo "Something went wrong while uploading the image. Please try again.";
        }
    } else {
        include('../../resource/conn.php');

        $stmt = $conn->prepare("INSERT INTO Comment (description, userID, postID)
                        VALUES (:description, :userID, :postID)  ");
        $stmt->bindParam(':description', $_POST['comment']);
        $stmt->bindParam(':userID', $logID);
        $stmt->bindParam(':postID', $postID);
        $stmt->execute();
    }
    header("Location: ../post.php?postID=$postID");
}

$conn = null;
?>

