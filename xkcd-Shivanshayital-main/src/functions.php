<?php

session_start();

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";

    return mail($email, $subject, $message, $headers);
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

    $emails = file($file, FILE_IGNORE_NEW_LINES);
    $filtered = array_filter($emails, fn($e) => trim($e) !== trim($email));

    return file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL, LOCK_EX) !== false;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $latest = json_decode(file_get_contents("https://xkcd.com/info.0.json"), true);
    $maxId = $latest['num'];
    $randomId = rand(1, $maxId);
    $comicData = json_decode(file_get_contents("https://xkcd.com/$randomId/info.0.json"), true);

    $imgUrl = $comicData['img'];
    $title = htmlspecialchars($comicData['safe_title'], ENT_QUOTES);

    return "
        <h2>XKCD Comic</h2>
        <img src=\"$imgUrl\" alt=\"XKCD Comic\">
        <p><a href=\"http://localhost:8000/unsubscribe.php\" id=\"unsubscribe-button\">Unsubscribe</a></p>
    ";
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $html = fetchAndFormatXKCDData();

    $subject = "Your XKCD Comic";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";

    foreach ($emails as $email) {
        mail(trim($email), $subject, $html, $headers);
    }
}
// Final version - trigger PR