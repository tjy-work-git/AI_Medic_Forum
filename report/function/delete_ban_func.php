<?php

session_start();
$role = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

include('../../resource/conn.php');

if ($role == 'Admin') {
	if (isset($_POST['reportID']) && !isset($_POST['postID'])) {
		$reportID = $_POST['reportID'];
	} else if (isset($_POST['postID']) && isset($_POST['reportID'])) {
		$postID = $_POST['postID'];
		$reportID = $_POST['reportID'];
	} else {
		echo json_encode(['success' => false, 'error' => 'No content specified']);
		exit;
	}

	$stmtPost = $conn->prepare("
    SELECT Post.postID, Post.title, Post.description, Post.postPhoto, Post.postDate, Post.userID
    FROM Post
    WHERE Post.postID = :postID
");
	$stmtPost->bindParam(':postID', $postID, PDO::PARAM_INT);
	$stmtPost->execute();
	$post = $stmtPost->fetch(PDO::FETCH_ASSOC);

	$stmt = $conn->prepare("DELETE FROM Post WHERE postID = :postID OR userID = :userID");
	$stmt->bindParam(':postID', $postID);
	$stmt->bindParam(':userID', $userID);
	$stmt->execute();

	$image_folder = "../../resource/img/post/";
	$image_path = $image_folder . $post['postPhoto'];
	unlink($image_path);

	$update = $conn->prepare("UPDATE User SET status = 'Banned' WHERE userID = :userID");
	$update->execute([
		':userID' => $post['userID']
	]);

	// update report status
	$update = $conn->prepare("UPDATE Report SET status = 'Completed' WHERE reportID = :reportID");
	$update->execute([
		':reportID' => $reportID
	]);
	echo json_encode([
		'success' => true,
	]);
} else {
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}

$conn = null;
