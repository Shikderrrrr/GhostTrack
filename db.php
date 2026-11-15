<?php
/**
 * db.php - GhostTrack Secure Database Connection
 * Uses PDO with Prepared Statements for maximum SQL Injection protection.
 */

// Hide detailed errors in production for security best practices
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'project_ghosttrack'; // Your existing database name
$username = 'root';            // Local server username
$password = '';                // Local server password (default is empty)
$charset = 'utf8mb4';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Strictly disable emulation to prevent SQL injection
    ]);
} catch (PDOException $e) {
    // Log the error securely and show a generic message to the user
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>