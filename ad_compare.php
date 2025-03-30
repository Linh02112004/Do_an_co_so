<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['id'] ?? '';

// Kiểm tra nếu event_id trống
if (empty($event_id)) {
    echo "<p>Sự kiện không hợp lệ.</p>";
    exit();
}

// Lấy dữ liệu sự kiện gốc
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$original_event = $result->fetch_assoc();
$stmt->close();

// Kiểm tra sự kiện có tồn tại không
if (!$original_event) {
    echo "<p>Sự kiện không tồn tại.</p>";
    exit();
}

// Lấy dữ liệu chỉnh sửa từ bảng event_edits (các chỉnh sửa đang chờ duyệt)
$sql = "SELECT * FROM event_edits WHERE event_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$edited_event = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Nếu không có bản chỉnh sửa nào
if (!$edited_event) {
    echo "<p>Không có chỉnh sửa nào cần xét duyệt.</p>";
    exit();
}
?>
