<?php
session_start();
include('../resource/conn.php');
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

$limit = 10; // feedbacks per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
	$search = "%" . $_GET['search'] . "%";
	switch ($_GET['searchType']) {
		case "post":
			$totalStmt = $conn->query("SELECT COUNT(*) FROM Feedback");
			$totalFeedbacks = $totalStmt->fetchColumn();
			$totalPages = ceil($totalFeedbacks / $limit);

			$stmt = $conn->prepare("
        SELECT Feedback.feedbackID, Feedback.title, Feedback.feedbackDate, User.username, COUNT(Upvote.contentNo) AS upvotes
        FROM Feedback
        LEFT JOIN User ON Feedback.userID = User.userID
        WHERE Feedback.title LIKE :search
        GROUP BY Feedback.feedbackID
        ORDER BY Feedback.feedbackID DESC
        LIMIT :limit OFFSET :offset");
			$stmt->bindParam(':search', $search, PDO::PARAM_STR);
			break;
		case "user":
			$totalStmt = $conn->query("SELECT COUNT(*) FROM User");
			$totalUser = $totalStmt->fetchColumn();
			$totalPages = ceil($totalUser / $limit);

			$stmt = $conn->prepare("
        SELECT userID, username, userPhoto, regDate, role
        FROM User
        WHERE username LIKE :search
        ORDER BY username
        LIMIT :limit OFFSET :offset");
			$stmt->bindParam(':search', $search, PDO::PARAM_STR);
			break;
	}
} else {
	$totalStmt = $conn->query("SELECT COUNT(*) FROM Feedback");
	$totalFeedbacks = $totalStmt->fetchColumn();
	$totalPages = ceil($totalFeedbacks / $limit);
	$stmt = $conn->prepare("
        SELECT Feedback.feedbackID, Feedback.title, Feedback.description, Feedback.feedbackDate, User.username
        FROM Feedback
        LEFT JOIN User ON Feedback.userID = User.userID
        GROUP BY Feedback.feedbackID
        ORDER BY Feedback.feedbackID DESC
        LIMIT :limit OFFSET :offset");
}
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
	<title>WeDoCare - Feedback</title>
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
			<?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
				<ul><a href="./main.php">Feedbacks</a></ul>
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
		</form>
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
						<p>Member since <?= $row['regDate'] ?></p>
					</div>
				<?php endforeach ?>
			<?php else : ?>
				<?php foreach ($results as $row): ?>
					<div class='container border'>
						<h2><a href='feedback.php?feedbackID=<?= $row['feedbackID'] ?>'><?= $row['title'] ?></a></h2>
						<p>Submitted by <b><?= $row['username'] ?? "[deleted]" ?></b> on <?= $row['feedbackDate'] ?>
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
			<div class='container'>The search result is empty.</div>
		<?php endif; ?>
	</div>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

</html>
