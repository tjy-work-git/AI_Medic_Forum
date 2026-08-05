<?php
session_start();
require '../../resource/conn.php';

header('Content-Type: application/json');

$userID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;

if (!$userID) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if (isset($_POST['postID'])) {
    $postID = $_POST['postID'];
} else {
    echo json_encode(['success' => false, 'error' => 'No content specified']);
    exit;
}

// Check if already bookmarked
$stmt = $conn->prepare("SELECT * FROM Bookmark WHERE userID = :userID AND postID = :postID");
$stmt->execute([
    ':userID' => $userID,
    ':postID' => $postID
]);

$action = '';
if ($stmt->rowCount() > 0) {
    // Remove upvote
    $delete = $conn->prepare("DELETE FROM Bookmark WHERE userID = :userID AND postID = :postID");
    $delete->execute([
        ':userID' => $userID,
        ':postID' => $postID
    ]);
    $action = 'removed';
} else {
    // Add upvote
    $insert = $conn->prepare("INSERT INTO Bookmark (userID, postID) VALUES (:userID, :postID)");
    $insert->execute([
        ':userID' => $userID,
        ':postID' => $postID
    ]);
    $action = 'added';
}

echo json_encode([
    'success' => true,
    'action' => $action
]);
