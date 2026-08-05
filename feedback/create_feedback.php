<?php
session_start();
$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

if (!isset($logID)) {
	header("Location: ../user/login.php");
	exit();
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
	<title>WeDoCare - Submit Feedback</title>
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
	<div class="container border">

		<h1>Submit Feedback</h1><br>
		<form method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<label for="title">Title<span style="color: red;">*</span> </label><br>
			<input type="text" maxlength="50" id="title" name="title" placeholder="Enter your title..." style="width: 100%;" required /><br>

			<label for="description">Description (maximum text length 1000)<span style="color: red;">*</span> </label><br>
			<textarea maxlength="1000" id="description" name="description" placeholder="Enter your description..." rows="10" style="width: 100%; resize:none;" required /></textarea><br>

			<label for="img">Select picture (optional):</label><br>
			<input type="file" name="img" id="img" placeholder="Insert image" accept="image/png, image/jpeg, image/gif">

			<input type="submit" class="btn btn-primary float-right" value="Feedback" />
		</form>
	</div>

	<?php
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty($_POST['title']) || empty($_POST['description'])) {
			echo "Please fill in missing fields.<br>";
		} else {
			if (!empty($_FILES['img']['name'])) {
				$image_name = $_FILES["img"]["name"];
				$image_tmp = $_FILES["img"]["tmp_name"];
				$image_folder = "../resource/img/feedback/";
				$image_path = $image_folder . $image_name;

				if (!file_exists($image_folder)) {
					mkdir($image_folder, 0775, true);
				}

				if (move_uploaded_file($image_tmp, $image_path)) {
					include('../resource/conn.php');

					$stmt1 = $conn->prepare("INSERT INTO Feedback (title, description, feedbackPhoto, userID)
                        VALUES (:title, :description, :feedbackPhoto, :userID)  ");
					$stmt1->bindParam(':title', $_POST['title']);
					$stmt1->bindParam(':description', $_POST['description']);
					$stmt1->bindParam(':feedbackPhoto', $image_name);
					$stmt1->bindParam(':userID', $logID);
					$stmt1->execute();
				} else {
					echo "Something went wrong while uploading the image. Please try again.";
				}
			} else {
				include('../resource/conn.php');

				$stmt1 = $conn->prepare("INSERT INTO Feedback (title, description, userID)
                        VALUES (:title, :description, :userID)  ");
				$stmt1->bindParam(':title', $_POST['title']);
				$stmt1->bindParam(':description', $_POST['description']);
				$stmt1->bindParam(':userID', $logID);
				$stmt1->execute();
			}
			$stmt2 = $conn->prepare("SELECT Feedback.feedbackID FROM Feedback JOIN User ON Feedback.userID = User.userID WHERE Feedback.userID = :userID ORDER BY Feedback.feedbackID DESC LIMIT 1");
			$stmt2->bindParam(':userID', $logID);
			$stmt2->execute();
			$newFeedback = $stmt2->fetch(PDO::FETCH_ASSOC);
			header("Location: feedback_success.php");
		}

		$conn = null;
	}
	?>
</body>
<footer class="bg-dark">
	WeDoCare Forum @2025
</footer>

</html>
