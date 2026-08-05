<?php

session_start();

if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID'])) {
	header("Location: ../../index.php");
	exit();
}

include('../../resource/conn.php');

$stmt = $conn->prepare("SELECT userID, username, role FROM User WHERE email = :email");
$stmt->bindParam(':email', $_SESSION['userEmail']);

$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
	if (isset($_SESSION['ToRememberUser']) && $_SESSION['ToRememberUser'] == "1") {
		session_unset();
		setcookie('loggedID', $result['userID'], time() + (30 * 24 * 60 * 60), "/"); // Use cookie expires in a month
		setcookie('loggedUser', $result['username'], time() + (30 * 24 * 60 * 60), "/");
		setcookie('loggedUserRole', $result['role'], time() + (30 * 24 * 60 * 60), "/");
	} else {
		session_unset();
		$_SESSION['loggedID'] = $result['userID'];
		$_SESSION['loggedUser'] = $result['username']; // Use session
		$_SESSION['loggedUserRole'] = $result['role']; // Use session
	}

	setcookie("justRegistered", "", time() - 3600, "/");
	setcookie("justReset", "", time() - 3600, "/");

	$logID = $result['userID'];
	header("Location: ../../index.php");
	exit();
} else {
	echo "The system has experienced some issues. Please try again later";
}

$conn = null;

