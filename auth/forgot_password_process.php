<?php
require '../config/db.php'; // Ensure the correct path to db.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        die("❌ Passwords do not match!");
    }

    // Check if user exists
    $sql = "SELECT * FROM admins WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("❌ User not found!");
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update password in the database
    $update_sql = "UPDATE admins SET password = :password WHERE username = :username";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
    $update_stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $update_stmt->execute();

    echo "✅ Password updated successfully!";
    echo "<br><a href='login.php'>Go back to login</a>";
}
