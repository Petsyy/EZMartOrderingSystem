<?php
session_start();
require '../config/db.php';

$error = '';
$success = '';

date_default_timezone_set('Asia/Manila'); // Ensure PHP runs in Manila time

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = trim($_POST['otp']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($otp) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Check if OTP is valid and not expired
        $check_sql = "SELECT email, reset_expiry FROM admins WHERE reset_token = :otp";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
        $check_stmt->execute();
        $user = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid OTP or expired.";
        } else {
            // Convert stored expiry to Manila time
            $stored_expiry = new DateTime($user['reset_expiry'], new DateTimeZone('UTC'));
            $stored_expiry->setTimezone(new DateTimeZone('Asia/Manila'));

            $current_time = new DateTime("now", new DateTimeZone('Asia/Manila'));

            if ($stored_expiry > $current_time) {
                $email = $user['email']; // Fetch email
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password and clear reset token
                $update_sql = "UPDATE admins SET password = :password, reset_token = NULL, reset_expiry = NULL WHERE email = :email";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $update_stmt->bindParam(':email', $email, PDO::PARAM_STR);

                if ($update_stmt->execute()) {
                    $_SESSION['success'] = "Password updated successfully.";
                    header("Location: admin_login.php");
                    exit();
                } else {
                    $error = "Password update failed.";
                }
            } else {
                $error = "OTP is expired!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reset Password</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        .error {
            color: red;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <fieldset>
            <legend>Admin Password Reset</legend>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (!empty($_SESSION['success'])) {
                echo "<p class='success'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            } ?>
            <form action="admin_reset_pass.php" method="post">
                <div class="input-group">
                    <label for="otp">OTP Code</label>
                    <input type="text" name="otp" required>
                </div>
                <div class="input-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
            <a href="admin_login.php" class="back-link">Back to Login</a>
        </fieldset>
    </div>
</body>

</html>