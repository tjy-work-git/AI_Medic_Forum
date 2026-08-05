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
    $contentNo = $_POST['postID'];
    $contentType = 'Post';
} elseif (isset($_POST['commentID'])) {
    $contentNo = $_POST['commentID'];
    $contentType = 'Comment';
} else {
    echo json_encode(['success' => false, 'error' => 'No content specified']);
    exit;
}

// Check if already upvoted
$stmt = $conn->prepare("SELECT * FROM Upvote WHERE userID = :userID AND contentNo = :contentNo AND contentType = :contentType");
$stmt->execute([
    ':userID' => $userID,
    ':contentNo' => $contentNo,
    ':contentType' => $contentType
]);

$action = '';
if ($stmt->rowCount() > 0) {
    // Remove upvote
    $delete = $conn->prepare("DELETE FROM Upvote WHERE userID = :userID AND contentNo = :contentNo AND contentType = :contentType");
    $delete->execute([
        ':userID' => $userID,
        ':contentNo' => $contentNo,
        ':contentType' => $contentType
    ]);
    $action = 'unvoted';
} else {
    // Add upvote
    $insert = $conn->prepare("INSERT INTO Upvote (userID, contentNo, contentType) VALUES (:userID, :contentNo, :contentType)");
    $insert->execute([
        ':userID' => $userID,
        ':contentNo' => $contentNo,
        ':contentType' => $contentType
    ]);
    $action = 'upvoted';
}

// Recount upvotes
$countStmt = $conn->prepare("SELECT COUNT(*) FROM Upvote WHERE contentNo = :contentNo AND contentType = :contentType");
$countStmt->execute([
    ':contentNo' => $contentNo,
    ':contentType' => $contentType
]);
$upvotes = $countStmt->fetchColumn();

echo json_encode([
    'success' => true,
    'upvotes' => $upvotes,
    'action' => $action
]);
