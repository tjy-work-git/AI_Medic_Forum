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
	<title>WeDoCare - About Us</title>
	<?php
	include("resource/BootstrapLibrary.php");
	?>
	<link rel='stylesheet' href="resource/style.css">
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
			<ul class="float-right">
				<?php if ($logID && $logUser): ?>
					Welcome, <a href="user/profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
				<?php else: ?>
					<a href="user/login.php" class="float-right">Login</a>
				<?php endif; ?>
			</ul>
		</nav>
	</header>
	<div style="padding-top: 100px; padding-bottom: 100px;">
		<div class="container border">
			<h2>About Us</h2>
			<p>WeDoCare is a medical forum, dedicated for discussing medical findings, ask questions related to health issues, ask for advice and share experiences.
				<br>WeDoCare is integrated with the latest AI models offered by OpenAI, assisting you in latest discussions.
			</p>
			<h2>What are the community guidelines here?</h2>
			<p>1. Do not post any medical findings that are factually incorrect, unless the findings are meant to discuss its legitimacy.<br>
				2. Do not post any NSFW materials on this forum, such as explicit adult content. This is strictly a discussion forums.<br>
				3. Do not attempt to guide users into performing or following medical tips that are otherwise harmful,
				such as taking large dose of medicine that can prove to cause side effects.<br>
				4. Do not ask for other users personal information, such as phone numbers, addresses, etc.<br>
				5. Do not harass or bully other users here. Keep it civil here.
			</p>
			<h2>How do AI help me on this platform?</h2>
			<p>The AI implemented on our forums shall aid you in : <br>
				- Summarizing forum discussions : If you have any issues in understanding the discussion,
				an AI is ready to help you in summarizing discussion in for both the content and the links provided by the users abstractively, extractively, or in just general.<br>
				<small>(Note : AI summarizer may not be able to access all types of web links due to security measurements from the link, therefore it is encouraged to visit the link provided.)</small>
			</p>
			<h2>Where can I reach out to the team?</h2>
			<p>You can submit a feedback form with the "Feedback" tab, or reach out to the team directly using any available contact: </p>
			<b>Tan Jun Yi</b>
			<br>Email: jytan-wp22@student.tarc.edu.my
			<br><br><b>Thian Anong</b>
			<br>Email: thianat-wp22@student.tarc.edu.my
			</p>
		</div>
	</div>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

</html>
