<?php

/**
 * Generate a 6-digit numeric verification code.
 */

function generateVerificationCode() {
    return rand(100000, 999999);
}

function sendVerificationEmail($email, $code) {
    // For local development only
    echo "DEBUG: OTP for $email is $code";
    return true;
}


/**
 * Send a verification code to an email.
 */
function verifyCode($userInput) {
    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
        return false;
    }
    if (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
        return false; // Expired
    }
    if ((string)$userInput === (string)$_SESSION['otp']) {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
        return true; // Success
    }
    return false; // Wrong code
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];

    if (!in_array($email, $emails)) {
        return file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }

    return false;
}



/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = array_filter($emails, fn($e) => $e !== trim($email));

    return file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL) !== false;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $max = 2800;
    $random = rand(1, $max);
    $json = file_get_contents("https://xkcd.com/$random/info.0.json");

    if (!$json) return "<p>Failed to load comic.</p>";

    $data = json_decode($json, true);
    return "<h2>{$data['safe_title']}</h2><img src='{$data['img']}' alt='{$data['alt']}' title='{$data['alt']}' />";
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $comicHTML = fetchAndFormatXKCDData();

    $subject = "Your XKCD Update";
    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: updates@example.com\r\n";

    foreach ($emails as $email) {
        mail($email, $subject, $comicHTML, $headers);
    }
}



