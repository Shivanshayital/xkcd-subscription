<?php
require_once 'functions.php';

$message = '';

$tempDir = __DIR__ . '/temp_codes';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code = generateVerificationCode();
        file_put_contents("$tempDir/" . md5($email) . ".txt", $code);

        $subject = "Confirm Un-subscription";
        $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";

        if (mail($email, $subject, $body, $headers)) {
            $message = "Unsubscription code sent to $email.";
        } else {
            $message = "Failed to send unsubscription code.";
        }
    }

    if (isset($_POST['verification_code']) && isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code = trim($_POST['verification_code']);
        $codeFile = "$tempDir/" . md5($email) . ".txt";

        if (file_exists($codeFile)) {
            $storedCode = trim(file_get_contents($codeFile));
            if ($code === $storedCode) {
                if (unsubscribeEmail($email)) {
                    $message = "You have been unsubscribed.";
                } else {
                    $message = "Email was not found in our list.";
                }
                unlink($codeFile);
            } else {
                $message = "Invalid code.";
            }
        } else {
            $message = "No unsubscription code found.";
        }
    }
}
?>

<h2>Unsubscribe from XKCD Comics</h2>
<p><?= htmlspecialchars($message) ?></p>

<form method="POST">
    <input type="email" name="unsubscribe_email" required>
    <button id="submit-unsubscribe">Unsubscribe</button>
</form>

<form method="POST">
    <input type="email" name="unsubscribe_email" required>
    <input type="text" name="verification_code" maxlength="6" required>
    <button id="submit-verification">Verify</button>
</form>
