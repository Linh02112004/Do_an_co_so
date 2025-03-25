<?php
require 'db_connect.php';

// Kiểm tra xem có dữ liệu được gửi đến không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id']);
    $amount = floatval($_POST['amount']);

    // Kiểm tra số tiền quyên góp
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Số tiền quyên góp phải lớn hơn 0.']);
        exit;
    }

    // Lấy tên người quyên góp từ session hoặc yêu cầu nhập
    // Ở đây giả sử tên người quyên góp được cung cấp qua một biến (có thể từ session hoặc form)
    $donor_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Người quyên góp ẩn danh';

    // Thêm quyên góp vào bảng donations
    $sql = "INSERT INTO donations (event_id, donor_name, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $event_id, $donor_name, $amount);

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