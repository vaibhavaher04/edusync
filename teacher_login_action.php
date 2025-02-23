<?php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college_id = $_POST['college_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM teachers WHERE college_id = ? AND username = ?");
    $stmt->execute([$college_id, $username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher && $teacher['password'] === $password) {
        session_start();
        $_SESSION['teacher_id'] = $teacher['teacher_id'];
        $_SESSION['college_name'] = $teacher['college_name'];

        header("Location: teacher_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid credentials. Please try again.'); window.location.href='teacher_login.php';</script>";
    }
}
?>
