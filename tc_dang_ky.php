<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp.";
    } else {
        // Kiểm tra xem email đã tồn tại chưa
        $sql_check = "SELECT id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Email này đã được sử dụng.";
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Lưu vào database
            $sql_insert = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $name, $email, $hashed_password);
            if ($stmt_insert->execute()) {
                header("Location: tc_dang_nhap.php?success=1");
                exit();
            } else {
                $error = "Lỗi khi đăng ký. Vui lòng thử lại.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Tổ chức</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Đăng ký</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="tc_dang_ky.php" method="POST">
            <label for="name">Tên tổ chức:</label>
            <input type="text" name="name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required>

            <label for="confirm_password">Nhập lại mật khẩu:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Đăng ký</button>
        </form>
        <p>Đã có tài khoản? <a href="tc_dang_nhap.php">Đăng nhập</a></p>
    </div>
</body>
</html>
