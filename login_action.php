<?php
session_start();
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrMobile = trim($_POST['emailOrMobile']);
    $password = trim($_POST['password']);

    if (empty($emailOrMobile) || empty($password)) {
        // If fields are empty, show error message
        header("Location: login.php?error=Please fill in all fields");
        exit();
    }

    // Check if the email/mobile exists in the database
    $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ? OR mobile = ?");
    $stmt->execute([$emailOrMobile, $emailOrMobile]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // If email/mobile is correct, verify the password
        if (password_verify($password, $user['password'])) {
            // If credentials are correct, start the session and redirect
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['role'] = 'student';
            $_SESSION['name'] = $user['name'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            // If password is incorrect, show error message
            header("Location: login.php?error=Invalid password");
            exit();
        }
    } else {
        // If email/mobile is incorrect, show error message
        header("Location: login.php?error=Invalid email or mobile number");
        exit();
    }
}
?>