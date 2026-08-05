<?php
session_start();

if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID'])) {
    header("Location: ../index.php");
    exit();
}

$msg = ""; // Default message

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get input values
    $uname = trim($_POST['uname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['pass'] ?? '';
    $pass_re = $_POST['pass_re'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $sourcePage = $_POST['sourcePage'] ?? 'Registration';

    // Validation
    if (empty($uname) || empty($pass) || empty($pass_re) || empty($gender) || empty($email)) {
        $msg = "Some essential fields are left missing.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $uname)) {
        $msg = "Name fields must contain only alphabetic characters.";
    } elseif ($pass !== $pass_re) {
        $msg = "Password mismatched. Please try again.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Email format is invalid.";
    } else {
        // If validation passed
        include('../resource/conn.php');
        $stmt = $conn->prepare("SELECT email FROM User WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $msg = "An account has already been registered with this email address.";
        } else {
            // Store to session
            $_SESSION['userName'] = $uname;
            $_SESSION['userPass'] = password_hash($pass, PASSWORD_DEFAULT);
            $_SESSION['userGender'] = $gender;
            $_SESSION['userEmail'] = $email;
            $_SESSION['sourcePage'] = $sourcePage;

            // Proceed to verification step
            header("Location: security/send_code.php");
            exit();
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
        <title>Medical Forum - Register</title>

        <?php
        include("../resource/BootstrapLibrary.php");
        ?>
    </head>
    <body>
        <div class="container" style="width: 25%; padding-top: 100px;">
            <h1>Register Account</h1>
            <?php if (isset($msg)) : ?>
                <span style="color: red;"><?= $msg ?></span>
            <?php endif; ?>
            <div class="container border">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

                    <label for="uname">Username<span style="color: red;">*</span></label><br>
                    <input type="text" maxlength="50" id="uname" name="uname" required/><br>

                    <label for="email">Email<span style="color: red;">*</span></label><br>
                    <input type="text" maxlength="50" id="email" name="email" required/><br>

                    <label for="pass">Password<span style="color: red;">*</span></label><br>
                    <input type="password" minlength="8" maxlength="20" id="pass" name="pass" required/><br>

                    <label for="pass_re">Re-type password<span style="color: red;">*</span></label><br>
                    <input type="password" minlength="8" maxlength="20" id="pass_re" name="pass_re" required/><br>

                    <label for="gender">Gender<span style="color: red;">*</span></label>
                    <input type="radio" id="gender" name="gender" value="Male" required/>Male
                    <input type="radio" id="gender" name="gender" value="Female" required/>Female<br>

                    <!-- This hidden field is to send the page source -->
                    <input type="hidden" name="sourcePage" value="Registration" />
                    <input type="submit" class='btn btn-primary' value="Submit"/>
                </form>
            </div>
            <br>
            <a href="javascript:history.back()">Back</a>
        </div>
        <br>
    </body>
</html>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
