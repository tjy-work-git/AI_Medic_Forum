<?php
session_start();
$userID = $_GET['userID'] ?? null;
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

if (!isset($userID)) {
    header("Location: ../index.php");
    exit;
}

include('../resource/conn.php');

$stmt = $conn->prepare("
    SELECT User.userID, User.username, User.userPhoto, User.bio, User.gender, User.regDate, User.role,
            COALESCE(PostUp.postUpvotes, 0) AS postUpvotes,
            COALESCE(CommUp.commentUpvotes, 0) AS commentUpvotes,
            COALESCE(PostUp.postUpvotes, 0) + COALESCE(CommUp.commentUpvotes, 0) AS totalUpvotes
        FROM User
        LEFT JOIN (
            SELECT Post.userID, COUNT(Upvote.contentNo) AS postUpvotes
            FROM Post
            LEFT JOIN Upvote ON Upvote.contentNo = Post.postID
            GROUP BY Post.userID
        ) PostUp ON PostUp.userID = User.userID
        LEFT JOIN (
            SELECT Comment.userID, COUNT(Upvote.contentNo) AS commentUpvotes
            FROM Comment
            LEFT JOIN Upvote ON Upvote.contentNo = Comment.commentID
            GROUP BY Comment.userID
        ) CommUp ON CommUp.userID = User.userID
        WHERE User.userID = :userID");
$stmt->bindParam(':userID', $userID);

$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $username = $result['username'];
    $gender = $result['gender'];
    $role = $result['role'];
    $regDate = $result['regDate'];
    $bio = $result['bio'];
    $photo = $result['userPhoto'];
    $totalUpvotes = $result['totalUpvotes'];
}

$conn = null;
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title><?= $username ?>'s Profile</title>
        <?php
        include("../resource/BootstrapLibrary.php");
        ?>
        <link rel="stylesheet" href="../resource/style.css">
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
            <h1>Profile</h1><br>
            <div class="container" style='padding: 50px; margin-bottom: 100px;'>
                <a href='../resource/img/user/<?= $photo ?>'><img src="../resource/img/user/<?= $photo ?>"
                                                                  onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
                                                                  style="width: 250px; height: 250px;" 
                                                                  class="img-thumbnail rounded-circle float-sm-left ml-5 mr-5"></a>
                <div>
                    <h3><?= $username ?></h3>
                    <p><?= $role ?></p>
                    <p><?= $gender ?> member since <?= $regDate ?></p>
                    <p><b><?= $totalUpvotes ?> upvotes from contribution</b></p>
                    <i>
                        <?php if (!empty($bio)) : ?>
                            <?= nl2br($bio) ?>
                        <?php else : ?>
                            This user have not left anything here yet...
                        <?php endif; ?>
                    </i>
                </div>
                <br>
                <a href='post_history.php?userID=<?= $userID ?>' style='margin-right: 20px;'>Post History</a>
                <a href='comment_history.php?userID=<?= $userID ?>'>Comment History</a>
                <?php if (isset($_SESSION['loggedID']) && $_SESSION['loggedID'] == $userID || isset($_COOKIE['loggedID']) && $_COOKIE['loggedID'] == $userID) : ?>
                    <a href='logout.php'><button class='btn float-right' style='background-color: red; color: white;'>Logout</button></a>
                    <a href='edit.php'><button class='btn btn-primary float-right' style='margin-right: 20px'>Edit Details</button></a>
                <?php endif; ?>
            </div>    
        </div>
    </body>
    <footer class="bg-dark">
        WeDoCare Forum @2025
    </footer>
</html>

