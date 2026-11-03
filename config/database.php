<?php
// Database connection.
$host = 'localhost';
$dbname = 'unicare_db';
$username = 'root'; // Update with your database username
$password = ''; // Update with your database password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("The clinic service is temporarily unavailable. Please try again later.");
}
?>