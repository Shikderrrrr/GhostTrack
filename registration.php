<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = 'Contractor'; // Fixed role for public contractor registration

    if (!empty($full_name) && !empty($username) && !empty($email) && !empty($password)) {
        try {
            // Check if username or email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM system_users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);

            if ($checkStmt->rowCount() > 0) {
                $error = "Username or Email already exists!";
            } else {
                // Hash the password securely
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert into system_users table with fixed role 'Contractor' and status 'Pending'
                $insertStmt = $pdo->prepare("INSERT INTO system_users (full_name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                $insertStmt->execute([$full_name, $username, $email, $hashedPassword, $role]);

                $message = "Registration successful as a <strong>Contractor</strong>! Your account is currently <strong>Pending Admin Approval</strong>. Once approved, you can <a href='index.html#login' style='color: #48cae4; font-weight: bold;'>Login here</a>.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostTrack - Contractor Registration</title>
</head>
<body style="background-color: #0b132b; color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
    <div style="background: #1c2541; padding: 40px; border-radius: 10px; width: 420px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); margin: 20px 0;">
        <h2 style="text-align: center; margin-bottom: 10px; color: #48cae4;">CREATE GHOSTTRACK ACCOUNT</h2>
        <p style="text-align: center; font-size: 13px; color: #94a3b8; margin-bottom: 20px;">Register to submit competitive tender bids and track progress.</p>
        
        <?php if (!empty($error)): ?>
            <div style="background: rgba(255, 77, 77, 0.2); border: 1px solid #ff4d4d; color: #ff4d4d; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div style="background: rgba(46, 196, 182, 0.2); border: 1px solid #2ec4b6; color: #5bc0be; padding: 12px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px; line-height: 1.5;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="registration.php" method="POST">
            <!-- Hidden role field locked to Contractor -->
            <input type="hidden" name="role" value="Contractor">

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Full Name</label>
                <input type="text" name="full_name" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;" placeholder="e.g. Toma Construction Ltd.">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;" placeholder="Choose a username">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;" placeholder="company@domain.com">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;" placeholder="Secure password">
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: #48cae4; color: #0b132b; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s;">REGISTER</button>
        </form>

        <p style="text-align: center; margin-top: 15px; color: #5bc0be; font-size: 14px;">
            Already have an account? <a href="index.html#login" style="color: #48cae4; text-decoration: none; font-weight: bold;">Login here</a>
        </p>
    </div>
</body>
</html>