<?php
require 'db_connect.php';
session_start(); // Bắt đầu session để lấy user_id

// Kiểm tra xem có dữ liệu được gửi đến không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id']);
    $amount = floatval($_POST['amount']);

    // Kiểm tra số tiền quyên góp
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Số tiền quyên góp phải lớn hơn 0.']);
        exit;
    }

    // Kiểm tra người dùng đã đăng nhập chưa
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để quyên góp.']);
        exit;
    }

    $donor_id = $_SESSION['user_id']; // Lấy ID người quyên góp từ session

    // Thêm quyên góp vào bảng donations
    $sql = "INSERT INTO donations (event_id, donor_id, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $event_id, $donor_id, $amount);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi ghi dữ liệu.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}

$conn->close();
?>
