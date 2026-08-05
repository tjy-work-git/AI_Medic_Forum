<?php
session_start();
include('../resource/conn.php');
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Set parameters for searching
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%"; // default to wildcard
$searchType = $_GET['searchType'] ?? 'post'; // default to post
$sortType = $_GET['sortType'] ?? 'latest';   // default to latest
$isSearching = $_SERVER["REQUEST_METHOD"] === "GET" && (isset($_GET['search']) || isset($_GET['sortType']));

if ($searchType === 'user') {
    // Determine how to sort
    switch ($sortType) {
        case 'upvote':
            $orderBy = "totalUpvotes DESC";
            break;
        case 'alphabet':
            $orderBy = "username ASC";
            break;
        case 'latest':
        default:
            $orderBy = "regDate DESC";
            break;
    }

    // Count total users matching search (for pagination)
    $totalStmt = $conn->prepare("SELECT COUNT(*) FROM User WHERE username LIKE :search");
    $totalStmt->bindParam(':search', $search, PDO::PARAM_STR);
    $totalStmt->execute();
    $totalCount = $totalStmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    // Fetch user info + upvotes from posts and comments
    $stmt = $conn->prepare("
        SELECT User.userID, User.username, User.userPhoto, User.regDate, User.role,
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
        WHERE User.username LIKE :search
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
} else {
    switch ($sortType) {
        case 'upvote':
            $orderBy = "upvotes DESC";
            break;
        case 'alphabet':
            $orderBy = $searchType === 'user' ? "username ASC" : "title ASC";
            break;
        case 'latest':
        default:
            $orderBy = $searchType === 'user' ? "regDate DESC" : "Post.postID DESC";
            break;
    }

    // Total count for pagination
    $totalStmt = $conn->prepare("SELECT COUNT(*) FROM Post WHERE title LIKE :search");
    $totalStmt->bindParam(':search', $search, PDO::PARAM_STR);
    $totalStmt->execute();
    $totalCount = $totalStmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    // Data query with upvote JOIN
    $stmt = $conn->prepare("
        SELECT Post.postID, Post.title, Post.description, Post.postDate, User.username, COUNT(Upvote.contentNo) AS upvotes
        FROM Post
        LEFT JOIN User ON Post.userID = User.userID
        LEFT JOIN Upvote ON Upvote.contentNo = Post.postID
        WHERE Post.title LIKE :search
        GROUP BY Post.postID
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
}

// Apply pagination
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <title>WeDoCare - Forum</title>
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
            <h1>Forums</h1><br>
            <form method="get">
                <input type="text" id="search" name="search" placeholder="Search keywords..." style="width: 90%;" />
                <input type="submit" class="btn btn-primary" value="Search" />
                <br>
                <label for="searchType">Search: </label>
                <input type="radio" name="searchType" value="post" checked /> Post
                <input type="radio" name="searchType" value="user" /> User
                <br>
                <label for="sortType">Sort by: </label>
                <input type="radio" name="sortType" value="latest" checked /> Latest
                <input type="radio" name="sortType" value="alphabet" /> Alphabets
                <input type="radio" name="sortType" value="upvote" /> Most Upvotes
            </form>
            <a href="create_post.php"><button class="btn text-white float-right" style='background-color: green;'>Add post</button></a><br>
        </div>
        <div style='margin-bottom: 100px;'>

            <?php if (!empty($results)) : ?>
                <?php if (isset($_GET['searchType']) && $_GET['searchType'] == "user"): ?>
                    <?php foreach ($results as $row): ?>
                        <div class="container border">
                            <img src="../resource/img/user/<?= htmlspecialchars($row['userPhoto']) ?>"
                                 onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
                                 class="img-thumbnail rounded-circle float-sm-left mr-3"
                                 style="width: 100px; height: 100px;">
                            <a href='../user/profile.php?userID=<?= $row['userID'] ?>'>
                                <h3><?= $row['username'] ?></h3>
                            </a>
                            <p><?= $row['role'] ?></p>
                            <p>Member since <?= $row['regDate'] ?><span class="float-right"><?= $row['totalUpvotes'] ?> contribution upvotes</span></p>
                        </div>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php foreach ($results as $row): ?>
                        <div class='container border'>
                            <h2><a href='post.php?postID=<?= $row['postID'] ?>'><?= $row['title'] ?></a></h2>
                            <p>Posted by <b><?= $row['username'] ?? "[deleted]" ?></b> on <?= $row['postDate'] ?>
                                <span class="float-right"><?= $row['upvotes'] ?> Upvotes</span>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($totalPages > 1): ?>
                        <form method="get" class="pagination-form">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="btn btn-secondary">Previous</a>
                            <?php endif; ?>

                            Page <input type="number" name="page" value="<?= $page ?>" min="1" max="<?= $totalPages ?>" style="width:60px; text-align:center;">
                            of <?= $totalPages ?>
                            <button type="submit" class="btn btn-primary">Go</button>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary">Next</a>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>

                <?php endif; ?>
            <?php else: ?>
                <div class='container'>The search result is empty. Maybe...It's time to ask?</div>
            <?php endif; ?>
        </div>
    </body>
    <footer class="bg-dark">
        WeDoCare Forum @2025
    </footer>

</html>
