<?php
session_start();
require '../../resource/conn.php';

header('Content-Type: application/json');

$userID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;

if (!$userID) {
	echo json_encode(['success' => false, 'error' => 'User not logged in']);
	exit;
}

if (isset($_POST['reportID'])) {
	$reportID = $_POST['reportID'];
} else {
	echo json_encode(['success' => false, 'error' => 'No content specified']);
	exit;
}

// update report status
$update = $conn->prepare("UPDATE Report SET status = 'Rejected' WHERE reportID = :reportID");
$update->execute([
	':reportID' => $reportID
]);

echo json_encode([
	'success' => true,
]);
