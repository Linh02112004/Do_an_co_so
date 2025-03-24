<?php
require 'db_connect.php'; // File kết nối database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Kiểm tra email hoặc số điện thoại đã tồn tại chưa
    $check = $conn->prepare("SELECT id FROM participants WHERE email = ? OR phone = ?");
    $check->bind_param("ss", $email, $phone);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "Email hoặc số điện thoại đã tồn tại!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Chèn thông tin vào bảng participants
        $stmt = $conn->prepare("INSERT INTO participants (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);

        if ($stmt->execute()) {
            echo "Đăng ký thành công! Đang chuyển hướng...";
            header("Refresh: 2; url=ntg_dang_nhap.php"); // Điều hướng sau 2 giây
            exit();
        } else {
            echo "Lỗi: " . $stmt->error;
        }
    }

    $check->close();
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
</head>
<body>
    <h2>Đăng ký Người Tham Gia</h2>
    <form method="POST" action="">
        <label>Họ và Tên:</label>
        <input type="text" name="name" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required><br>

        <label>Số điện thoại:</label>
        <input type="text" name="phone" required><br>

        <label>Địa chỉ:</label>
        <input type="text" name="address" required><br>

        <button type="submit">Đăng ký</button>
    </form>
</body>
</html>
