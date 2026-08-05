<?php
$feedbackID = $_GET['feedbackID'] ?? null;

if (!isset($feedbackID)) {
	header("Location: main.php");
	exit;
}

session_start();

$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

include('../resource/conn.php');

// Feedback
$stmt1 = $conn->prepare("
    SELECT Feedback.title, Feedback.description, Feedback.feedbackPhoto, Feedback.feedbackDate, Feedback.userID, User.username, User.userPhoto
    FROM Feedback
    LEFT JOIN User ON Feedback.userID = User.userID
    WHERE Feedback.feedbackID = :feedbackID
    GROUP BY Feedback.feedbackID
");
$stmt1->bindParam(':feedbackID', $feedbackID, PDO::PARAM_INT);
$stmt1->execute();
$feedback = $stmt1->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Title : <?= htmlspecialchars($feedback['title']) ?></title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<?php include("../resource/BootstrapLibrary.php"); ?>
	<link rel='stylesheet' href="../resource/style.css">
</head>

<body style="margin-bottom: 200px;">
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
		<?php if ($feedback): ?>
			<h1><?= htmlspecialchars($feedback['title']) ?></h1>
			<div>
				<img src="../resource/img/user/<?= htmlspecialchars($feedback['userPhoto']) ?>"
					onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png'"
					class="img-thumbnail"
					style="width: 50px; height: 50px;">
				<p> Posted by
					<?php if (isset($feedback['username'])): ?>
						<a href="../user/profile.php?userID=<?= $feedback['userID'] ?>">
							<?= htmlspecialchars($feedback['username']) ?>
						</a>
					<?php else: ?>
						[deleted]
					<?php endif; ?>
				</p>
				<p><?= htmlspecialchars($feedback['feedbackDate']) ?></p>
				<p><?= nl2br(htmlspecialchars($feedback['description'])) ?></p>
				<?php if (!empty($feedback['feedbackPhoto'])): ?>
					<a href="../resource/img/feedback/<?= htmlspecialchars($feedback['feedbackPhoto']) ?>"><img src="../resource/img/feedback/<?= htmlspecialchars($feedback['feedbackPhoto']) ?>" width="500" alt="<?= htmlspecialchars($feedback['feedbackPhoto']) ?>"></a><br>
				<?php endif; ?><br>
			</div>
		<?php else: ?>
			<p>Feedback not found.</p>
		<?php endif; ?>
	</div>
</body>

</html>

<script>
	document.addEventListener('DOMContentLoaded', function() {});
</script>
