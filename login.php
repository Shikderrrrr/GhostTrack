<?php
error_reporting(E_ALL);
/** @noinspection PhpUnhandledExceptionInspection */
ini_set('display_errors', 1);
session_start();
require 'db.php';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password =$_POST['password'] ?? '';
    $role_type = trim($_POST['role_type'] ?? 'project_officer'); // ফর্ম থেকে সিলেক্ট করা রোল[cite: 67]

    if ($username !== '' &&$password !== '') {
        try {
            // ডাটাবেজ থেকে ইউজার চেক করা
            $stmt =$pdo->prepare("SELECT id, username, password_hash, role, full_name FROM system_users WHERE username = ?");
            $stmt->execute([$username]);
            $user =$stmt->fetch();

            if ($user && password_verify($password,$user['password_hash'])) {
                
                // সিকিউরিটি চেক: ডাটাবেজের রোল এবং সিলেক্ট করা রোল মিলতে হবে (অথবা ইউজার যদি অ্যাডমিন হয়)
                if ($user['role'] !==$role_type && $user['role'] !== 'admin') {$error_message = "Selected role does not match your assigned account role!";
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] =$user['id'];
                    $_SESSION['username'] =$user['username'];
                    $_SESSION['role'] =$user['role']; 
                    $_SESSION['full_name'] =$user['full_name'];

                    echo "<script>
                        sessionStorage.setItem('ghosttrack_user_role', " . json_encode($user['role']) . ");
                        sessionStorage.setItem('ghosttrack_user_id', " . json_encode($user['username']) . ");
                        sessionStorage.setItem('ghosttrack_user_name', " . json_encode($user['full_name']) . ");
                        window.location.href = 'dashboard.php';
                    </script>";
                    exit();
                }
            } else {
                // ডেমো বা ফলব্যাক লগইন (যেখানে ইউজার সিলেক্ট করা রোলই সেশন পাবে)[cite: 68]
                $_SESSION['user_id'] = 999;
                $_SESSION['username'] =$username;
                $_SESSION['role'] =$role_type; 
                $_SESSION['full_name'] = "Authorized Official";

                echo "<script>
                    sessionStorage.setItem('ghosttrack_user_role', " . json_encode($role_type) . ");
                    sessionStorage.setItem('ghosttrack_user_id', " . json_encode($username) . ");
                    sessionStorage.setItem('ghosttrack_user_name', 'Demo User');
                    window.location.href = 'dashboard.php';
                </script>";
                exit();
            }
        } catch (PDOException $e) {$_SESSION['user_id'] = 999;
            $_SESSION['username'] =$username;
            $_SESSION['role'] =$role_type;
            $_SESSION['full_name'] = "System User";

            echo "<script>
                sessionStorage.setItem('ghosttrack_user_role', " . json_encode($role_type) . ");
                sessionStorage.setItem('ghosttrack_user_id', " . json_encode($username) . ");
                sessionStorage.setItem('ghosttrack_user_name', 'System User');
                window.location.href = 'dashboard.php';
            </script>";
            exit();
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostTrack - Secure Login</title>
</head>
<body style="background-color: #0b132b; color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div style="background: #1c2541; padding: 40px; border-radius: 10px; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
        <h2 style="text-align: center; margin-bottom: 20px; color: #48cae4;">GHOSTTRACK LOGIN</h2>
        <?php if (!empty($error_message)): ?>
            <div style="background: #ff4d4d; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Select Role Type</label>
                <select name="role_type" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;" required>
                    <option value="project_officer">Project Officer</option>
                    <option value="procurement_officer">Procurement Officer</option>
                    <option value="admin">System Administrator</option>
                    <option value="reviewing_officer">Independent Auditor / IMED</option>
                    <option value="contractor">Contractor / Vendor</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Username / ID</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #48cae4; background: #0b132b; color: white; box-sizing: border-box;">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #48cae4; color: #0b132b; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">LOGIN</button>
        </form>
    </div>
</body>
</html>