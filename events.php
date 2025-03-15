<?php
include 'db.php';
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['events' => [], 'user_name' => null]);
    exit;
}

// Lấy ID người dùng từ session
$userId = $_SESSION['user_id'];

// Lấy thông tin người dùng
$userSql = "SELECT full_name FROM users WHERE id = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Debug: Kiểm tra tên người dùng
if ($full_name) {
    echo json_encode(['events' => [], 'user_name' => $full_name]);
} else {
    echo json_encode(['events' => [], 'user_name' => null]);
}
exit;

// Lấy sự kiện
$sql = "SELECT id, title, description, target_amount, image_path FROM events ORDER BY id DESC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Trả về dữ liệu JSON bao gồm cả sự kiện và tên người dùng
echo json_encode(['events' => $events, 'user_name' => $full_name]);
?>