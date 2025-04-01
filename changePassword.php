<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Lỗi: Bạn chưa đăng nhập.'); window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng từ CSDL
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); 
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Xử lý thay đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới và mật khẩu xác nhận có trùng nhau không
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Mật khẩu mới và xác nhận mật khẩu không trùng nhau!');</script>";
        exit();
    }

    // Kiểm tra mật khẩu hiện tại
    $query = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!password_verify($current_password, $user_data['password_hash'])) {
        echo "<script>alert('Mật khẩu hiện tại không đúng!');</script>";
        exit();
    }

    // Cập nhật mật khẩu mới
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET password_hash = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $new_password_hash, $user_id);
    $stmt->execute();

    // Thông báo thành công và chuyển hướng về trang phù hợp
    echo "<script>
        alert('Mật khẩu đã được thay đổi thành công!');
        window.location.href = '" . ($user['role'] === 'donor' ? 'donor.php' : 'organization.php') . "';
    </script>";
    exit();
}
?>
