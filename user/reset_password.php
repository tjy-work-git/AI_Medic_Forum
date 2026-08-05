<?php
session_start();
if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID']) || $_SESSION['sourcePage'] != "Forgot") {
    header("Location: ../../index.php");
    exit();
}
if (isset($_SESSION['successMessage'])) {
    echo "<script>alert('" . $_SESSION['successMessage'] . "');</script>";
    unset($_SESSION['successMessage']); // prevent showing it again on refresh
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['pass'])) {
        echo "Your password field(s) is missing.";
        exit;
    } else if ($_POST['pass'] != $_POST['pass_re']) {
        echo "Password mismatched. Please try again.<br>";
    } else {
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        include('../resource/conn.php');

        $stmt = $conn->prepare("UPDATE User SET password = :pass WHERE email = :email");
        $stmt->bindParam(':pass', $pass);
        $stmt->bindParam(':email', $_SESSION['userEmail']);
        $stmt->execute();
        session_unset();

        setcookie('justReset', true, time() + 300, "/");
        header('Location: login.php');
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Forgot Password?</title>

        <?php
        include("../resource/BootstrapLibrary.php");
        ?>
    </head>
    <body>
        <div class="container" style="width: 25%; padding-top: 100px;">
            <h1>Reset password</h1>
            <p>Set a password with minimum of 8 length</p>
            <form action="" method="post">

                <label for="pass">Password: </label><br>
                <input type="password" minlength="8" maxlength="20" id="pass" name="pass" required/><br>

                <label for="pass_re">Re-type password: </label><br>
                <input type="password" minlength="8" maxlength="20" id="pass_re" name="pass_re" required/><br><br>

                <input type="submit" class='btn btn-primary' value="Submit"/>
            </form>
        </div>
    </body>
</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>