<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';

$message = '';
$step = 'email';
$email = '';

// Step 1: Unsubscribe email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['unsubscribe_code'][$email] = [
                'code' => (string)$code,
                'expiry' => time() + 300
            ];
            sendVerificationEmail($email, $code);
            $message = "Verification code sent to $email.";
            $step = 'verify';
        } else {
            $message = "Invalid email address.";
        }
    } elseif (isset($_POST['verification_code'])) {
        $email = $_POST['email'] ?? '';
        $code = $_POST['verification_code'] ?? '';
        if ($email && isset($_SESSION['unsubscribe_code'][$email])) {
            $saved = $_SESSION['unsubscribe_code'][$email];
            if ((string)$saved['code'] === (string)$code) {
                if (time() <= $saved['expiry']) {
                    if (unsubscribeEmail($email)) {
                        unset($_SESSION['unsubscribe_code'][$email]);
                        $message = "You have been unsubscribed. Thank you!";
                        $step = 'done';
                    } else {
                        $message = "Error unsubscribing email or email not found.";
                        $step = 'email';
                    }
                } else {
                    unset($_SESSION['unsubscribe_code'][$email]);
                    $message = "Verification code expired. Please try again.";
                    $step = 'email';
                }
            } else {
                $message = "Invalid verification code.";
                $step = 'verify';
            }
        } else {
            $message = "Invalid verification code or session expired.";
            $step = 'email';
        }
    }
    // Always keep $email populated for the verify step
    if ($step === 'verify') {
        if (empty($email)) {
            $email = $_POST['email'] ?? '';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
</head>
<body>
    <h2>Unsubscribe from XKCD Comics</h2>
    <p><?= htmlspecialchars($message) ?></p>

    <?php if ($step === 'email'): ?>
        <form method="POST">
            <label for="unsubscribe_email">Enter your email:</label>
            <input type="email" name="unsubscribe_email" required>
            <button id="submit-unsubscribe">Unsubscribe</button>
        </form>
    <?php elseif ($step === 'verify'): ?>
        <form method="POST">
            <!-- Always populate the hidden email field -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <label for="verification_code">Enter verification code:</label>
            <input type="text" name="verification_code" maxlength="6" required>
            <button id="submit-verification">Verify</button>
        </form>
    <?php elseif ($step === 'done'): ?>
        <p>You have been unsubscribed. Thank you!</p>
    <?php endif; ?>
</body>
</html>