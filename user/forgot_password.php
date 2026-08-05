<?php
session_start();
if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID'])) {
    header("Location: ../index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['email'])) {
        echo "Your email field is missing.";
        exit;
    } else {
        include('../resource/conn.php');

        $stmt = $conn->prepare("SELECT email FROM User WHERE email = :email");
        $stmt->bindParam(':email', $_POST['email']);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            session_start();
            $_SESSION['userEmail'] = $_POST['email'];
            $_SESSION['sourcePage'] = $_POST['sourcePage'];
            header("Location: security/send_code.php");
            exit();
        } else {
            $msg = "There is no account associated with this email.";
        }
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
            <h1>Forgot your password?</h1>
            <?php if (isset($msg)) :?>
            <span style="color: red;"><?= $msg ?></span>
            <?php endif; ?>
            <p>Reset your password by entering your email registered on this website.</p>
            <p>Your email shall receive a 6-digit code used to authenticate your identity. </p>
            <div>
                <form action="" method="post">

                    <label for="email">Email : </label>
                    <input type="text" maxlength="50" id="email" name="email" required/>

                    <!-- This hidden field is to send the page source -->
                    <input type="hidden" name="sourcePage" value="Forgot" />
                    <input type="submit" class="btn btn-primary" value="Submit"/>
                </form>
            </div>
        </div>
    </body>
</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>