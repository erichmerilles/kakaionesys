<?php
// config/db.php

date_default_timezone_set('Asia/Manila');

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "kakaikrk_system";

try {
    // Enforcing strict utf8mb4 encoding for security and character support
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // Forces real prepared statements
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    // Sync MySQL timezone with PHP timezone
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    // In a production environment, log this to a file instead of displaying it
    die("Database Connection Failed: " . $e->getMessage());
}
