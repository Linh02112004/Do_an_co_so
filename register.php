<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $cccd = $_POST['cccd'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra email, CCCD, số điện thoại đã tồn tại hay chưa
    $sql = "SELECT * FROM users WHERE email = ? OR cccd = ? OR phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $cccd, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email, CCCD hoặc số điện thoại đã được đăng ký.";
    } else {
        // Thêm người dùng mới vào cơ sở dữ liệu
        $sql = "INSERT INTO users (full_name, address, email, cccd, phone, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $full_name, $address, $email, $cccd, $phone, $password);
        
        if ($stmt->execute()) {
            echo "Đăng ký thành công!";
            header("Location: login.html");
        } else {
            echo "Đăng ký thất bại: " . $conn->error;
        }
    }
}
?>