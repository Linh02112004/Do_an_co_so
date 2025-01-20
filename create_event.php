<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu người dùng đã đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo "Bạn cần đăng nhập để tạo sự kiện.";
        exit;
    }

    // Lấy thông tin từ form
    $title = $_POST['title'];
    $description = $_POST['description'];
    $target_amount = $_POST['target_amount'];
    $created_by = $_SESSION['user_id'];

    // Chuẩn bị câu lệnh SQL để chèn sự kiện mới
    $sql = "INSERT INTO events (title, description, target_amount, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $title, $description, $target_amount, $created_by);
    
    // Thực thi câu lệnh
    if ($stmt->execute()) {
        echo "Tạo sự kiện thành công!";
        header("Location: index.html"); // Điều hướng về trang chính
        exit;
    } else {
        echo "Tạo sự kiện thất bại: " . $conn->error;
    }
}
?>