<?php

require_once('config.php');

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

// Fetch notifications for the student
$sql = "SELECT message FROM notifications WHERE student_id = :student_id ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($notifications)) {
    echo "<li class='list-group-item'>No new notifications.</li>";
} else {
    foreach ($notifications as $notification) {
        echo "<li class='list-group-item'>{$notification['message']}</li>";
    }
}
?>