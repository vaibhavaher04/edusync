<?php
session_start();
session_destroy(); // Destroy the session
header("Location: index.html"); // Redirect to the home page
exit();
?>