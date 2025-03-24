<?php
session_start();
require 'db_connect.php'; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_phone = trim($_POST["email_or_phone"]);
    $password = trim($_POST["password"]);

    // Tìm user bằng email hoặc số điện thoại
    $stmt = $conn->prepare("SELECT id, name, email, password FROM participants WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email_or_phone, $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            $_SESSION["participant_logged_in"] = true;
            $_SESSION["participant_id"] = $user["id"];
            $_SESSION["participant_name"] = $user["name"];
            $_SESSION["participant_email"] = $user["email"];
            
            header("Location: ntg_index.php"); // Chuyển hướng sau khi đăng nhập
            exit();
        } else {
            $error_message = "Sai mật khẩu!";
        }
    } else {
        $error_message = "Tài khoản không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
</head>
<body>
    <h2>Đăng nhập</h2>
    <?php if (!empty($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
    
    <form method="POST" action="">
        <label>Email hoặc Số điện thoại:</label>
        <input type="text" name="email_or_phone" required><br>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>
