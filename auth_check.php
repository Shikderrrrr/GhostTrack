<?php
/**
 * auth_check.php - Security Guard for GhostTrack Dashboards
 * Verifies if a user is logged in and optionally checks their required role.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to enforce authentication and role-based access
function enforce_access($required_role = null) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }

    // If a specific role is required, verify it matches the user's role
    if ($required_role !== null && $_SESSION['role'] !== $required_role) {
        // Redirect unauthorized users back to their respective portal or login
        header("Location: login.php");
        exit();
    }
}
?>