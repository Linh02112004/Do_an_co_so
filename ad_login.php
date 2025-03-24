<?php
session_start();
require 'db_connect.php';

// Tài khoản quản trị viên cố định
$admin_email = "admin@example.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT); // Mật khẩu được mã hóa trước

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($email === $admin_email && password_verify($password, $admin_password)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: ad_index.php");
        exit();
    } else {
        $error = "Email hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Quản trị viên</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập Quản trị viên</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required><br>
            <label>Mật khẩu:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
