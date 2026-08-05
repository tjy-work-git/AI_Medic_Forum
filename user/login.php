<?php
session_start();
if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID'])) {
    header("Location: ../index.php");
    exit();
}
if (isset($_SESSION['successMessage'])) {
    echo "<script>alert('" . $_SESSION['successMessage'] . "');</script>";
    unset($_SESSION['successMessage']); // prevent showing it again on refresh
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email_login']) || empty($_POST['pass_login'])) {
        $msg = "Some fields were left missing. Please try again.";
    } else {
        include('../resource/conn.php');

        $stmt = $conn->prepare("SELECT email, password, status FROM User WHERE email = :email");
        $stmt->bindParam(':email', $_POST['email_login']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if ($result['email'] == $_POST['email_login'] && password_verify($_POST['pass_login'], $result['password'])) {
                if ($result['status'] == 'Banned') {
                    $msg = 'Your account has been banned for violating the community guidelines. If you wish to appeal for your ban, please contact the forum team.';
                } else {
                    if (isset($_POST['remember'])) {
                        $_SESSION['ToRememberUser'] = true;
                    }
                    if (isset($_COOKIE['justRegistered']) && $_COOKIE['justRegistered'] == "1" || isset($_COOKIE['justReset']) && $_COOKIE['justReset'] == "1") {
                        $_SESSION["userEmail"] = $_POST['email_login'];
                        header('Location: security/set_session.php');
                    } else {
                        $_SESSION["userEmail"] = $_POST['email_login'];
                        $_SESSION['sourcePage'] = $_POST['sourcePage'];
                        header("Location: security/send_code.php");
                    }
                }
            } else {
                $msg = "Your username / password is incorrect. Please try again.";
            }
        } else {
            $msg = "This account does not exist in our database. Consider register one.";
        }
    }
    $conn = null;
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
        <title>Medical Forum - Login</title>
        <?php
        include("../resource/BootstrapLibrary.php");
        ?>
    </head>
    <body>
        <div class="container" style="width: 25%; padding-top: 100px;">
            <h1>Login</h1>
            <?php if (isset($msg)) : ?>
                <span style="color: red;"><?= $msg ?></span>
<?php endif; ?>
            <div class="container border">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <label for="email_login">Email: </label><br>
                    <input type="text" maxlength="50" id="email_login" name="email_login" required/><br>

                    <label for="pass_login">Password: </label><br>
                    <input type="password" maxlength="20" id="pass_login" name="pass_login" required/><br>

                    <input type="checkbox" id="remember" name="remember" value="remember">
                    <label for="remember"> Remember me for a month</label><br>

                    <!-- This hidden field is to send the page source -->
                    <input type="hidden" name="sourcePage" value="Login" />
                    <input type="submit" class="btn btn-primary" value="Login"/>
                </form>
                <br>
                <a href="register.php">No Account? Register here</a>
                <a href="forgot_password.php" class="float-right">Forgot password?</a>
            </div>
            <a href="javascript:history.back()" style="color: red;">Back</a>
        </div>
    </body>
</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
