<?php
session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;
?>
<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>

    <head>
        <meta charset="UTF-8">
        <title>WeDoCare - Home</title>
        <?php
        include("resource/BootstrapLibrary.php");
        ?>
        <link rel="stylesheet" href="resource/style.css" />
    </head>

    <body>
        <header>
            <h1 class="text-white p-3"><a href="index.php" class="text-white">WeDoCare</a></h1>
            <nav id="navigator" class="navbar text-white bg-dark">
                <ul><a href="index.php">Home</a></ul>
                <ul><a href="forum/main.php">Forum</a></ul>
                <ul><a href="forum/bookmark.php">Bookmark</a></ul>
                <?php if ($logID && $logUser && $logUserRole == 'User'): ?>
                    <ul><a href="feedback/create_feedback.php">Feedback</a></ul>
                <?php endif; ?>
                <?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
                    <ul><a href="feedback/main.php">Feedbacks</a></ul>
                    <ul><a href="report/main.php">Reports</a></ul>
                <?php endif; ?>
                <ul><a href="about.php">About Us</a></ul>
                <ul style="padding-left: 60%;">
                    <?php if ($logID && $logUser): ?>
                        Welcome, <a href="user/profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
                    <?php else: ?>
                        <a href="user/login.php" class="float-right">Login</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        <div style="margin-top: 10%; margin-bottom: 10%;">
            <h1>Forums for Health and Medical Information, with AI</h1><br>
            <a href="forum/main.php">Click here and start contribute</a>
        </div>
    </body>
    <footer class="bg-dark">
        WeDoCare Forum @2025
    </footer>
</html>
