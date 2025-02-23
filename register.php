<?php
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect and sanitize form data
        $name = trim($_POST['name']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $mobile = trim($_POST['mobile']);
        $gender = trim($_POST['gender']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);

        // Validate mandatory fields
        if (empty($name) || empty($email) || empty($mobile) || empty($gender) || empty($password) || empty($confirmPassword)) {
            throw new Exception("All fields are required.");
        }

        if (!$email) {
            throw new Exception("Invalid email format.");
        }

        if (!preg_match('/^\d{10}$/', $mobile)) {
            throw new Exception("Invalid mobile number. Must be 10 digits.");
        }

        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match.");
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists in the database
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists.");
        }

        // Insert new user into the database
        $sql = "INSERT INTO users (name, email, mobile, gender, password) VALUES (:name, :email, :mobile, :gender, :password)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();

        // Redirect to login page after successful registration
        header("Location: login.php");
        exit();
    } catch (Exception $e) {
        // Display error message
        echo "Error: " . $e->getMessage();
    }
}
?>