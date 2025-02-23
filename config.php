<?php
session_start();

// Database configuration
define('DBNAME', 'edusync'); // if0_38283656_edusync
define('DBUSER', 'root'); // if0_38283656
define('DBPASS', ''); // 2vuGVwdAk8j8Tp
define('DBHOST', 'localhost'); // sql112.infinityfree.com

try {
    // Establish a database connection using PDO
    $db = new PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die("Connection failed: " . $e->getMessage());
}
?>