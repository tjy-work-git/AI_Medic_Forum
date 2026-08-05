<?php
include('../resource/conn.php');
session_start();

$logID = $_SESSION['loggedID'] ?? $_COOKIE['loggedID'] ?? null;
$logUser = $_SESSION['loggedUser'] ?? $_COOKIE['loggedUser'] ?? null;
$logUserRole = $_SESSION['loggedUserRole'] ?? $_COOKIE['loggedUserRole'] ?? null;

if (!$logID) {
    header("Location: login.php");
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'profile':
            if (!empty($_POST['removePhoto']) && !empty($_POST['userPhoto'])) {
                $stmt = $conn->prepare("UPDATE User SET userPhoto = NULL WHERE userID = :userID");
                $stmt->bindParam(':userID', $logID);
                $stmt->execute();

                $image_path = "../resource/img/user/" . $_POST['userPhoto'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                $msg = "Photo removed successfully.";
            }

            $updateFields = [];
            $params = [':userID' => $logID];

            if (!empty($_POST['username'])) {
                $updateFields[] = "username = :username";
                $params[':username'] = $_POST['username'];
            }

            if (isset($_POST['bio'])) {
                $updateFields[] = "bio = :bio";
                $params[':bio'] = $_POST['bio'];
            }

            if (!empty($_POST['gender'])) {
                $updateFields[] = "gender = :gender";
                $params[':gender'] = $_POST['gender'];
            }

            if (!empty($updateFields)) {
                $sql = "UPDATE User SET " . implode(', ', $updateFields) . " WHERE userID = :userID";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                // Update session/cookie only if username was updated
                if (!empty($_POST['username'])) {
                    $logUser = $_POST['username'];
                    if (isset($_SESSION['loggedUser'])) {
                        $_SESSION['loggedUser'] = $logUser;
                    } else if (isset($_COOKIE['loggedUser'])) {
                        setcookie('loggedUser', $logUser, time() + (30 * 24 * 60 * 60), "/");
                    }
                }

                $msg = "Profile updated successfully.";
            }

            if (!empty($_FILES['photo']['name'])) {
                $image_name = basename($_FILES['photo']['name']);
                $image_path = "../resource/img/user/" . $image_name;
                if (!file_exists(dirname($image_path))) {
                    mkdir(dirname($image_path), 0775, true);
                }
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $image_path)) {
                    $stmt = $conn->prepare("UPDATE User SET userPhoto = :photo WHERE userID = :userID");
                    $stmt->execute([':photo' => $image_name, ':userID' => $logID]);
                }
                $msg = "Profile updated successfully.";
            }
            break;

        case 'account':
            if (!empty($_POST['deleteAccount'])) {
                $stmt = $conn->prepare("DELETE FROM User WHERE userID = :userID");
                $stmt->execute([':userID' => $logID]);
                header("Location: logout.php");
                exit;
            }
            if (!empty($_POST['deleteActivities'])) {
                $stmt1 = $conn->prepare("DELETE FROM Post WHERE userID = :userID");
                $stmt1->execute([':userID' => $logID]);
                
                $stmt2 = $conn->prepare("DELETE FROM Comment WHERE userID = :userID");
                $stmt2->execute([':userID' => $logID]);
                $msg2 = "All activities deleted successfully.";
                break;
            }

            $emailChanged = false;
            $passwordChanged = false;

            // Check if email is submitted and different from current
            if (!empty($_POST['email'])) {
                // Check if the new email already exists for another user
                $stmt = $conn->prepare("SELECT userID FROM User WHERE email = :email AND userID != :userID");
                $stmt->execute([':email' => $_POST['email'], ':userID' => $logID]);
                $existingEmail = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingEmail) {
                    $msg2 = "Email is already in use.";
                    break;
                } else {
                    $stmt = $conn->prepare("UPDATE User SET email = :email WHERE userID = :userID");
                    $stmt->execute([':email' => $_POST['email'], ':userID' => $logID]);
                    $emailChanged = true;
                }
            }

            // Check if password and confirm password are both entered
            if (!empty($_POST['password']) || !empty($_POST['confirmPassword'])) {
                if ($_POST['password'] === $_POST['confirmPassword']) {
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE User SET password = :password WHERE userID = :userID");
                    $stmt->execute([':password' => $hashedPassword, ':userID' => $logID]);
                    $passwordChanged = true;
                } else {
                    $msg2 = "Password mismatch.";
                    break;
                }
            }

            // Set final message
            if ($emailChanged && $passwordChanged) {
                $msg2 = "Email and password updated.";
            } elseif ($emailChanged) {
                $msg2 = "Email updated.";
            } elseif ($passwordChanged) {
                $msg2 = "Password updated.";
            } else {
                $msg2 = "No changes made.";
            }
            break;
    }
}

$stmt = $conn->prepare("SELECT username, bio, gender, email, userPhoto FROM User WHERE userID = :userID");
$stmt->execute([':userID' => $logID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Profile</title>
        <?php include("../resource/BootstrapLibrary.php"); ?>
        <link rel="stylesheet" href="../resource/style.css">
    </head>
    <body>
        <header>
            <h1 class="text-white p-3"><a href="../index.php" class="text-white">WeDoCare</a></h1>
            <nav id="navigator" class="navbar text-white bg-dark">
                <ul><a href="../index.php">Home</a></ul>
                <ul><a href="../forum/main.php">Forum</a></ul>
                <ul><a href="../forum/bookmark.php">Bookmark</a></ul>
                <?php if ($logID && $logUser && $logUserRole == 'User'): ?>
                    <ul><a href="../feedback/create_feedback.php">Feedback</a></ul>
                <?php endif; ?>
                <?php if ($logID && $logUser && $logUserRole == 'Admin'): ?>
                    <ul><a href="../feedback/main.php">Feedbacks</a></ul>
                    <ul><a href="../report/main.php">Reports</a></ul>
                <?php endif; ?>
                <ul><a href="../about.php">About Us</a></ul>
                <ul style="padding-left: 60%;">
                    <?php if ($logID && $logUser): ?>
                        Welcome, <a href="profile.php?userID=<?= $logID ?>"><?= htmlspecialchars($logUser) ?></a>
                    <?php else: ?>
                        <a href="login.php" class="float-right">Login</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <div class="container" style="width: 50%; padding-top: 100px; margin-bottom: 100px;">
            <a href="profile.php?userID=<?= $logID ?>" class="float-right" style='color: red;'>Back</a><h1>Account and Profile Management</h1>
            <div class="container border" style='padding: 25px;'>
                <h2>Profile Details</h2>
                <?php if (isset($msg)) echo "<span style='color: blue;'>$msg</span>"; ?>
                <img src="../resource/img/user/<?= $user['userPhoto'] ?>" 
                     onerror="this.onerror=null; this.src='../resource/img/user/placeholder.png';" 
                     style="width: 100px; height: 100px;" 
                     class="img-thumbnail rounded-circle float-left mx-3">

                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="profile">
                    <input type="hidden" name="userPhoto" value="<?= $user['userPhoto'] ?>">


                    <div style='padding-top: 25px; padding-bottom: 25px;'>
                        <label for="photo">Photo:</label>
                        <input type="file" name="photo" id="photo" accept="image/*">
                        <input class="btn" style='background-color: red; color: white;' type="submit" name="removePhoto" value="Remove Profile Photo"><br><br>
                    </div>


                    <label for="username">Username</label><br>
                    <input type="text" id="username" name="username" placeholder="<?= $user['username'] ?? "Error: no username" ?>"><br>

                    <label for="bio">Bio</label><br>
                    <textarea id="bio" name="bio" rows="4" placeholder="Enter bio..." style="width: 60%; resize: none;"><?= $user['bio'] ?></textarea><br>

                    <label>Gender:</label><br>
                    <input type="radio" name="gender" value="Male" <?= $user['gender'] == 'Male' ? 'checked' : '' ?>> Male
                    <input type="radio" name="gender" value="Female" <?= $user['gender'] == 'Female' ? 'checked' : '' ?>> Female

                    <input type="submit" class="btn float-right" style='background-color: green; color: white;' value="Save">
                </form>
            </div>

            <div class="container border" style='padding: 25px;'>
                <h2>Account & Security</h2>
                <?php if (isset($msg2)) echo "<span style='color: blue;'>$msg2</span>"; ?>

                <form id="accountForm" action="" method="post">
                    <input type="hidden" name="action" value="account">
                    <input type="hidden" id="deleteAccount" name="deleteAccount">
                    <input type="hidden" id="deleteActivities" name="deleteActivities">

                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" placeholder="<?= $user['email'] ?? "Error: No email" ?>"><br>

                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password" minlength="8" maxlength="20">
                    <small class="form-text text-muted">Password must be at least 8 characters.</small>

                    <label for="confirmPassword">Re-type Password:</label><br>
                    <input type="password" id="confirmPassword" name="confirmPassword" minlength="8" maxlength="20"><br><br>

                    <input type="submit" class="btn float-right" style='background-color: green; color: white;' value="Save">
                    <button type="button" onclick="confirmDelAcc()" class='btn' style='background-color: red; color: white'>Delete Account</button>
                    <button type="button" onclick="confirmDelAct()" class='btn' style='background-color: red; color: white'>Delete All Activities</button>
                </form>
            </div>
        </div>

        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            function confirmDelAcc() {
                const confirmMsg = "Are you sure you want to delete your account? Your posts and comments will remain.\n\
        \nWe recommend you to clean up your activities for privacy purposes, unless you wish to keep them on our platforms.\n\
\nNOTE : \n\
- All of your accout details , such as your profile photo, will be deleted off the platform.\n\
- Your post and comments will not be deleted. This process must be done manually with 'Delete All Activities'.\n\
- Your username that appears in every of your post and comments will be privatized.\n\
- Once the process is complete, your account will be logged out.";
                if (confirm(confirmMsg)) {
                    document.getElementById('deleteAccount').value = 'true';
                    document.getElementById('accountForm').submit();
                }
            }
            
            function confirmDelAct() {
                const confirmMsg = "Are you sure you want to delete your activities? Your posts and comments will be deleted.\n\
For selective deletion, you can go to 'Post History' and 'Comment History' and find all your activities.";
                if (confirm(confirmMsg)) {
                    document.getElementById('deleteActivities').value = 'true';
                    document.getElementById('accountForm').submit();
                }
            }
        </script>
    </body>
    <footer class="bg-dark">
        WeDoCare Forum @2025
    </footer>
</html>
