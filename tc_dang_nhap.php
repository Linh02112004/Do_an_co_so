<?php
session_start();
require 'db_connect.php'; // Kết nối CSDL

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập email và mật khẩu.";
    } else {
        $sql = "SELECT id, name, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $name, $hashed_password);
                $stmt->fetch();
                
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    header("Location: tc_index.php");
                    exit();
                } else {
                    $error = "Mật khẩu không chính xác.";
                }
            } else {
                $error = "Email không tồn tại.";
            }
            $stmt->close();
        } else {
            $error = "Lỗi truy vấn.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="tc_dang_nhap.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            
            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
