<?php
session_start();

// Database configuration
define('DBNAME', 'edusync');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBHOST', 'localhost');

try {
    // Establish a database connection using PDO
    $db = new PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die("Connection failed: " . $e->getMessage());
}
?>