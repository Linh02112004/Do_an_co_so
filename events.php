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

// Lấy sự kiện

$sql = "SELECT events.id, events.title, events.description, users.full_name AS creator_name,

events.raised_amount, events.target_amount

FROM events

JOIN users ON events.created_by = users.id";

$result = $conn->query($sql);

$events = [];

if ($result->num_rows > 0) {

while ($row = $result->fetch_assoc()) {

$events[] = $row;

}

}

// Trả về dữ liệu JSON bao gồm cả sự kiện và tên người dùng

echo json_encode(['events' => $events, 'user_name' => $full_name ? $full_name : null]);

$conn->close();

?>