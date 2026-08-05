<?php
session_start();
include('../resource/conn.php');
$userID = $_GET['userID'] ?? null;
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

$stmt = $conn->prepare("
    SELECT Comment.commentID, Comment.postID, Post.title, Comment.description, Comment.commPhoto, Comment.commentDate,
           Comment.userID, User.username, COUNT(Upvote.contentNo) AS upvotes
    FROM Comment
    JOIN Post ON Post.postID = Comment.postID
    LEFT JOIN User ON Comment.userID = User.userID
    LEFT JOIN Upvote ON Upvote.contentNo = Comment.commentID AND Upvote.contentType = 'Comment'
    WHERE Comment.userID = :userID
    GROUP BY Comment.commentID
    ORDER BY Comment.commentDate DESC
");
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$conn = null;
?>

<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Comment History</title>
        <?php
        include("../resource/BootstrapLibrary.php");
        ?>
        <link rel='stylesheet' href="../resource/style.css">
    </head>
    <body>
        <header>
            <h1 class="text-white p-3"><a href="../index.php" class="text-white">WeDoCare</a></h1>
            <nav id="navigator" class="navbar text-white bg-dark">
                <ul><a href="../index.php">Home</a></ul>
                <ul><a href="../forum/main.php">Forum</a></ul>
                <ul><a href="../forum/bookmark.php">Bookmark</a></ul>
                <?php if ($logID && $logUser && $logUserRole == 'User'): ?>
                    <ul><a href="../feedback/create_feedback.php">Feedback</a></ul>
                <?php endif; ?>
                <?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
                    <ul><a href="../feedback/main.php">Feedbacks</a></ul>
                    <ul><a href="../report/main.php">Reports</a></ul>
                <?php endif; ?>
                <ul><a href="../about.php">About Us</a></ul>
                <ul style="padding-left: 60%;">
                    <?php if ($logID && $logUser): ?>
                        Welcome, <a href="profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
                    <?php else: ?>
                        <a href="login.php" class="float-right">Login</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <div class="container">
            <h1>Comment History</h1><br>
            <div style='margin-bottom: 100px;'>
                <?php if (!empty($comments)) : ?>
                    <p> <?= sizeof($comments) ?> results was retrieved. </p>
                    <?php foreach ($comments as $row): ?>
                        <div class='container border'>
                            <b>Commented on <a href='../forum/post.php?postID=<?= $row['postID'] ?>'>"<?= $row['title'] ?>"</a></b>
                            <?php if (!empty($row['commPhoto'])): ?>
                                <a href="../resource/img/comment/<? =$row['commPhoto']?>">1 image attached</a>
                            <?php endif; ?>
                            <p><?= $row['description'] ?></p>
                            <p>Posted by <b><?= $row['username'] ?></b> on <?= $row['commentDate'] ?>
                                <span class='float-right'><?= $row['upvotes'] ?> Upvotes</span></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class='container'>The comment history is clean.</div>
                <?php endif; ?>    
            </div>
        </div>
    </body>
    <footer class="bg-dark">
        WeDoCare Forum @2025
    </footer>
</html>

