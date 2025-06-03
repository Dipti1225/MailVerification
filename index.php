<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle email submission
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();
        $_SESSION['otp'] = $code;
        $_SESSION['otp_expiry'] = time() + 300; // 5 minutes
        $_SESSION['email'] = $email;

        if (sendVerificationEmail($email, $code)) {
            $message = "Verification code sent to <strong>$email</strong>";
        } else {
            $message = "❌ Failed to send verification email.";
        }
    }

    // Handle code verification
    if (isset($_POST['verification_code'])) {
        $userInput = trim($_POST['verification_code']);
        $email = $_SESSION['email'] ?? '';

        if (verifyCode($userInput)) {
            if (registerEmail($email)) {
                $message = "✅ Email verified and registered successfully!";
            } else {
                $message = "⚠️ Email was already registered.";
            }
        } else {
            $message = "❌ Verification failed. Incorrect or expired code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Welcome</h1>

    <form method="POST">
        <input type="email" name="email" required>
<button id="submit-email">Submit</button>
    </form>

    <form method="POST">
        <input type="text" name="verification_code" maxlength="6" required>
<button id="submit-verification">Verify</button>
    </form>

    <p><?= $message ?></p>
</body>
</html>