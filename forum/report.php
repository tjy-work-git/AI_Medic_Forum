<?php
if (!isset($_POST['postID']) && !isset($_POST['commentID'])) {
	header("Location: main.php");
	exit;
} else {
	session_start();
	$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
	$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
        
        if (!isset($logID)) {
            header ("Location: ../user/login.php");
        }
        
	$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

	include "../resource/conn.php";


	if (isset($_POST['postID'])) {
		$reportType = "Post";
		$reportNo = $_POST['postID'];
		$stmt = $conn->prepare("SELECT title, description FROM Post WHERE postID = :postID");
		$stmt->bindParam(':postID', $_POST['postID']);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			$title = $result['title'];
			$desc = $result['description'];
		}
	} else if (isset($_POST['commentID'])) {
		$reportType = "Comment";
		$reportNo = $_POST['commentID'];
		$stmt = $conn->prepare("SELECT description FROM Comment WHERE commentID = :commentID");
		$stmt->bindParam(':commentID', $_POST['commentID']);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			$desc = $result['description'];
		}
	}
}
?>

<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>

<head>
	<meta charset="UTF-8">
	<title>Reporting Content</title>
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
		<h1>Report</h1><br>
		<p>You are attempting to report this content : </p>
		<div class="container border p-3">
			<?php
			if (isset($title)) {
				echo "<h2>$title</h2>";
			}
			echo substr($desc, 0, 500), "...";
			?>
		</div><br>
		<form action="function/report_func.php" method="POST">
			<label for="reason">Please tell use why : </label><br>
			<textarea id="reason" name="reason" placeholder="Enter details" maxlength="200" style="width: 100%; resize: none;" required></textarea>

			<p>NOTE : By submitting this report, you hereby confirm that the content you are reporting does indeed violates our Community Guidelines. Any misleading reports may lead to punishment of your account.</p>
			<input type="hidden" id="reportNo" name="reportNo" value="<?= $reportNo ?>" />
			<input type="hidden" id="reportType" name="reportType" value="<?= $reportType ?>" />
			<input type="hidden" id="origin" name="origin" value='<?= $_POST['postID'] ?>' />
			<input type="submit" value="Report" />
		</form>
	</div>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

</html>
