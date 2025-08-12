<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <fieldset>
            <legend>Forgot Password</legend>
            <form action="forgot_password_process.php" method="post" onsubmit="return validateForm()">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" placeholder="Enter your username" name="username" id="username" required>
                    <span id="username-error" class="error"></span>
                </div>
                <div class="input-group">
                    <label for="new_password">Enter new password</label>
                    <input type="password" placeholder="Enter new password" name="new_password" id="new_password" required>
                    <span id="password-error" class="error"></span>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm new password</label>
                    <input type="password" placeholder="Confirm new password" name="confirm_password" id="confirm_password" required>
                    <span id="confirm-password-error" class="error"></span>
                </div>
                <button type="submit">Confirm</button>
            </form>
        </fieldset>
    </div>
    <script src="forgot_pass.js"></script>

</html>