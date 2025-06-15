<?php
require_once 'functions.php';

$message = '';

// Ensure temp folder exists
$tempDir = __DIR__ . '/temp_codes';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();
        file_put_contents("$tempDir/" . md5($email) . ".txt", $code);
        if (sendVerificationEmail($email, $code)) {
            $message = "Verification code sent to $email.";
        } else {
            $message = "Failed to send verification email.";
        }
    }

    if (isset($_POST['verification_code']) && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $code = trim($_POST['verification_code']);
        $codeFile = "$tempDir/" . md5($email) . ".txt";

        if (file_exists($codeFile)) {
            $storedCode = trim(file_get_contents($codeFile));
            if ($code === $storedCode) {
                if (registerEmail($email)) {
                    $message = "Email verified and registered.";
                } else {
                    $message = "Email already registered.";
                }
                unlink($codeFile);
            } else {
                $message = "Invalid verification code.";
            }
        } else {
            $message = "No verification code found for this email.";
        }
    }
}
?>

<h2>Subscribe to XKCD Comics</h2>
<p><?= htmlspecialchars($message) ?></p>

<form method="POST">
    <input type="email" name="email" required>
    <button id="submit-email">Submit</button>
</form>

<form method="POST">
    <input type="email" name="email" required>
    <input type="text" name="verification_code" maxlength="6" required>
    <button id="submit-verification">Verify</button>
</form>
