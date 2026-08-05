<?php
session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
?>
<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>

<head>
	<meta charset="UTF-8">
	<title>Feedback Submitted</title>
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
			<?php if ($logID && $logUser): ?>
				<ul><a href="./create_feedback.php">Feedback</a></ul>
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
	<div style="padding-top: 100px; padding-bottom: 100px;">
		<div class="container border">
			<h2>Feedback Submitted</h2>
			<p>Thank you for taking the time to share your thoughts with us. Your feedback has been successfully submitted and is greatly appreciated. We value your input as it helps us improve and serve you better.
			</p>
			<em>You will be redirected to homepage in 5 seconds...</em>
		</div>
	</div>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

<script>
	setTimeout(() => {
		window.location.replace('/index.php')
	}, 5000)
</script>

</html>
