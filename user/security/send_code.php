<?php

session_start();
if (isset($_SESSION['loggedID']) || isset($_COOKIE['loggedID']) || !isset($_SESSION['sourcePage'])) {
    header("Location: ../../index.php");
    exit();
}

require '../../vendor/autoload.php';

//Prepare components
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

//Generate code
$code = rand(100000, 1000000);

// Configure mail transport
// for developer : Keep your email and app password secure.
$transport = Transport::fromDsn('smtp://jytan.work.personal@gmail.com:elawtkdnggdnadkz@smtp.gmail.com:587');
$mailer = new Mailer($transport);

// Prepare message
switch ($_SESSION['sourcePage']) {
    case 'Login':
        $message = '<p><strong>Welcome to AI-Integrated Online Medical Forum!</strong></p>'
                . '<p>You have received this message because your account is attempting to login to our healthcare forum platform. '
                . 'To proceed to login, you may enter this code at the verification screen: </p>'
                . '<h1>' . $code . '</h1>'
                . '<p>If this is not you, please contact our customer support immediately.</p>'
                . '<p>This is an automated message. Please do not reply to this message.</p>';
        break;
    case 'Registration':
        $message = '<p><strong>This is a message from AI-Integrated Online Medical Forum</strong></p> '
                . '<p>You have received this message for creating an account on our healthcare forum platform. '
                . 'To proceed creating your account, you may enter this code at the verification screen : </p>'
                . '<h1>' . $code . '</h1>'
                . '<p>If you did not request to create an account on our platform, it is advised to contact our customer support immediately.</p>'
                . '<p>This is an automated message. Please do not reply to this message.</p>';
        break;
    case 'Forgot':
        $message = '<p><strong>This is a message from AI-Integrated Online Medical Forum</strong></p> '
                . '<p>You have received this message for attempting to reset your password. '
                . 'To proceed with the procedure, you may enter this code at the verification screen : </p>'
                . '<h1>' . $code . '</h1>'
                . '<p>If you did not request to create an account on our platform, it is advised to contact our customer support immediately.</p>'
                . '<p>This is an automated message. Please do not reply to this message.</p>';
        break;
}

// Prepare email
$email = (new Email())
        ->from('jytan.work.personal@gmail.com')
        ->to($_SESSION['userEmail'])
        ->subject('Account authentication')
        ->text('Verify your identity first')
        ->html($message);

include('../../resource/conn.php');

// Send email
$mailer->send($email);

// Check existing authentication code
$stmt1 = $conn->prepare("
    SELECT email, authCode 
    FROM Authentication 
    WHERE email = :email 
    LIMIT 1");
$stmt1->bindParam(':email', $_SESSION['userEmail']);

$stmt1->execute();
$result = $stmt1->fetch(PDO::FETCH_ASSOC);
if ($result) {
    // Override existing code
    $stmt2 = $conn->prepare("
        UPDATE Authentication 
        SET authCode = :code
        WHERE email = :email
        ");
    $stmt2->bindParam(':email', $_SESSION['userEmail']);
    $stmt2->bindParam(':code', $code);
    $stmt2->execute();
} else {
    // Add code
    $stmt2 = $conn->prepare("INSERT INTO Authentication (email, authCode)
        VALUES (:email, :code)");
    $stmt2->bindParam(':email', $_SESSION['userEmail']);
    $stmt2->bindParam(':code', $code);
    $stmt2->execute();
}

$conn = null;
header("Location: authentication.php");
exit();
?>