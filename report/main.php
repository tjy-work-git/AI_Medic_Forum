<?php
session_start();
include('../resource/conn.php');
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

$limit = 10; // reports per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
	$search = "%" . $_GET['search'] . "%";
	$totalStmt = $conn->query("SELECT COUNT(*) FROM Report");
	$totalReports = $totalStmt->fetchColumn();
	$totalPages = ceil($totalReports / $limit);

	$stmt = $conn->prepare("
        SELECT Report.reportID, Report.reportDesc, Report.reportNo, Report.reportDate, Report.status, Post.title AS title, User.username
        FROM Report
        LEFT JOIN User ON Report.userID = User.userID
        LEFT JOIN Post ON Report.reportNo = Post.postID
        WHERE Report.title LIKE :search
        GROUP BY Report.reportID
        ORDER BY Report.reportID DESC
        LIMIT :limit OFFSET :offset");
	$stmt->bindParam(':search', $search, PDO::PARAM_STR);
} else {
	$totalStmt = $conn->query("SELECT COUNT(*) FROM Report");
	$totalReports = $totalStmt->fetchColumn();
	$totalPages = ceil($totalReports / $limit);
	$stmt = $conn->prepare("
        SELECT Report.reportID, Report.reportDesc, Report.reportNo, Report.status, Report.reportDate, Post.title AS title, User.username
        FROM Report
        LEFT JOIN User ON Report.userID = User.userID
        LEFT JOIN Post ON Report.reportNo = Post.postID
        GROUP BY Report.reportID
        ORDER BY Report.reportID DESC
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
	<title>WeDoCare - Reports</title>
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
				<ul><a href="./main.php">Reports</a></ul>
			<?php endif; ?>
			<ul><a href="../about.php">About Us</a></ul>
			<ul class="float-right">
				<?php if ($logID && $logUser): ?>
					Welcome, <a href="../user/profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
				<?php else: ?>
					<a href="../user/login.php" class="float-right">Login</a>
				<?php endif; ?>
			</ul>
		</nav>
	</header>

	<div class="container">
		<h1>Reports</h1><br>
		<form method="get">
			<input type="text" id="search" name="search" placeholder="Search keywords..." style="width: 90%;" />
			<input type="submit" class="btn btn-primary" value="Search" />
		</form>
	</div>
	<div style='margin-bottom: 100px;'>

		<?php if (!empty($results)) : ?>
			<?php foreach ($results as $row): ?>
				<div class='container border'>
					<?php
					$color = 'btn-primary';
					switch ($row['status']) {
						case 'Pending':
							$color = 'badge-warning';
							break;
						case 'Completed':
							$color = 'badge-success';
							break;
						case 'Rejected':
							$color = 'badge-danger';
							break;
					}
					?>
					<div class="float-right badge <?= $color ?>"><?= $row['status'] ?></div>
					<h2><a href='report.php?reportID=<?= $row['reportID'] ?>'>Report on <?= $row['title'] ?></a></h2>
					<p>Submitted by <b><?= $row['username'] ?? "[deleted]" ?></b> on <?= $row['reportDate'] ?>
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

		<?php else: ?>
			<div class='container'>The search result is empty.</div>
		<?php endif; ?>
	</div>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

</html>
