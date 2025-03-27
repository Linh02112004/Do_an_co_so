<?php
session_start();
require 'db_connect.php'; // Kết nối database

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Bạn chưa đăng nhập.");
}

$user_id = $_SESSION['user_id'];

// Nhận dữ liệu từ form
$event_name = $_POST['event_name'];
$description = $_POST['description'];
$location = $_POST['location'];
$goal = $_POST['goal'];
$organizer_name = $_POST['organizer_name'];
$phone = $_POST['phone'];
$bank_account = $_POST['bank_account'];
$bank_name = $_POST['bank_name'];
$status = 'ongoing'; // Mặc định là sự kiện đang diễn ra

// Kiểm tra dữ liệu đầu vào
if (empty($event_name) || empty($description) || empty($location) || empty($goal) || 
    empty($organizer_name) || empty($phone) || empty($bank_account) || empty($bank_name)) {
    die("Lỗi: Vui lòng điền đầy đủ thông tin.");
}

// Chèn dữ liệu vào bảng events
$sql = "INSERT INTO events (event_name, description, location, goal, organizer_name, phone, bank_account, bank_name, status, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdsssssss", $event_name, $description, $location, $goal, $organizer_name, $phone, $bank_account, $bank_name, $status, $user_id);

if ($stmt->execute()) {
    echo "<script>alert('Sự kiện đã được tạo thành công!'); window.location.href='organization.php';</script>";
} else {
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>