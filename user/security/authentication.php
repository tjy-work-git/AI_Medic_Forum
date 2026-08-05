<?php
session_start();
if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID']) || !isset($_SESSION['sourcePage'])) {
    header("Location: ../../index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['verify_code'])) {
        echo "Code is left empty. Please enter your code.";
    } else {
        include('../../resource/conn.php');

        $stmt1 = $conn->prepare("
                    SELECT email, authCode 
                    FROM Authentication 
                    WHERE email = :email
                    ");
        $stmt1->bindParam(':email', $_SESSION['userEmail']);

        $stmt1->execute();
        $result = $stmt1->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            if ($_SESSION['userEmail'] == $result['email'] && $_POST['verify_code'] == $result['authCode']) {
                $stmt3 = $conn->prepare("DELETE FROM Authentication WHERE email = :email");
                $stmt3->bindParam(':email', $_SESSION['userEmail']);
                $stmt3->execute();
                
                switch ($_SESSION['sourcePage']) {
                    case 'Registration':
                        $stmt2 = $conn->prepare("INSERT INTO User (username, password, gender, email, role)
            VALUES (:uname, :pass, :gender, :email, 'User')");
                        $stmt2->bindParam(':uname', $_SESSION['userName']);
                        $stmt2->bindParam(':pass', $_SESSION['userPass']);
                        $stmt2->bindParam(':gender', $_SESSION['userGender']);
                        $stmt2->bindParam(':email', $_SESSION['userEmail']);
                        $stmt2->execute();

                        session_unset();
                        setcookie('justRegistered', true, time() + 300, "/");

                        session_start();
                        $_SESSION['successMessage'] = "Registration successful! You may now log in.";
                        header('Location: ../login.php');
                        exit();
                        break;

                    case 'Login':
                        header('Location: set_session.php');
                        exit();
                        break;

                    case 'Forgot':
                        $_SESSION['successMessage'] = "Verification successful. You can now reset your password.";
                        header('Location: ../reset_password.php');
                        exit();
                        break;
                }
            } else {
                $msg = "Your verification code mismatched. Please try again.";
            }
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
        <title>Authentication</title>
        <?php
        include("../../resource/BootstrapLibrary.php");
        ?>
    </head>
    <body>
        <div class="container" style="width: 25%; padding-top: 100px;">
            <h1>Authentication</h1>
            <?php if (isset($msg)) : ?>
                <span style="color: red;"><?= $msg ?></span>
            <?php endif; ?>
            <p>A 6-digit verification code has been sent to your associated email. Please enter the code here once you receive it.</p>
            <p>If you did not find the email, make sure to check the spam emails.</p>
            <form action="" method="post" autocomplete="off">
                <label for="verify_code">Verification Code : </label>
                <input type="text" maxlength="6" id="verify_code" name="verify_code"/>
                <input type="submit" class='btn btn-primary' value="Submit"/>
            </form>
            <br><a href="send_code.php">Haven't receive the code? Request another one.</a>
            <br>
            <a href="javascript:history.back()">Back</a>
        </div>
    </body>
</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>