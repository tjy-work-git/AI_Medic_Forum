<?php
// Session
session_start();
session_destroy();

// Cookies
setcookie("loggedID", "", time() - 3600, "/");
setcookie("loggedUser", "", time() - 3600, "/");

//Redirect back to home
header('Location: ../index.php');
?>