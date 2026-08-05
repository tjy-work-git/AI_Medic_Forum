<?php
session_start();

if (!isset($_SESSION['loggedID']) && !isset($_COOKIE['loggedID'])) {
    header("Location: ../user/login.php");
    exit();
}

include('../resource/conn.php');

$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

$stmt = $conn->prepare("
        SELECT Post.postID, Post.title, Post.description, Post.postDate, User.username, COUNT(Upvote.contentNo) AS upvotes
        FROM Post
        JOIN User ON Post.userID = User.userID
        LEFT JOIN Bookmark ON Bookmark.postID = Post.postID
        LEFT JOIN Upvote on Upvote.contentNo = Post.postID
        WHERE Bookmark.userID = :logID");
$stmt->bindParam(':logID', $logID);
$stmt->execute();
$bookmark = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <title>Your Bookmark</title>
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
                <ul><a href="./main.php">Forum</a></ul>
                <ul><a href="./bookmark.php">Bookmark</a></ul>
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
                        Welcome, <a href="../user/profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
                    <?php else: ?>
                        <a href="../user/login.php" class="float-right">Login</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <div class="container">
            <h1>Bookmark</h1><br>
            <p>Your bookmarked post shall appear here.</p>

            <?php if (!empty($bookmark)) : ?>
                <p> <?= sizeof($bookmark) ?> results was retrieved. </p>
                <?php foreach ($bookmark as $row): ?>
                    <div class='container border' />
                    <h2><a href='post.php?postID=<?= $row['postID'] ?>'><?= $row['title'] ?></a></h2>
                    <p>Posted by <b><?= $row['username'] ?></b> on <?= $row['postDate'] ?>
                        <span class='float-right'><?= $row['upvotes'] ?> Upvotes</span>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class='container'>You haven't bookmark anything yet.</div>
        <?php endif; ?>
    </div>
</body>
<footer class="bg-dark">
    WeDoCare Forum @2025
</footer>

</html>
