<?php
require 'db_connect.php';

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Sự kiện không hợp lệ.");
}

$event_id = intval($_GET["id"]);

// Kiểm tra xem sự kiện có quyên góp nào không
$sql_check = "SELECT COUNT(*) as donation_count FROM donations WHERE event_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();
$stmt->close();

if ($donation["donation_count"] > 0) {
    die("Không thể xóa sự kiện vì đã có quyên góp.");
}

// Xóa các bản ghi liên quan trước khi xóa sự kiện
$tables = ["event_edits", "comments", "notifications"];
foreach ($tables as $table) {
    $sql_delete_related = "DELETE FROM $table WHERE event_id = ?";
    $stmt = $conn->prepare($sql_delete_related);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
}

// Xóa sự kiện
$sql_delete_event = "DELETE FROM events WHERE id = ?";
$stmt = $conn->prepare($sql_delete_event);
$stmt->bind_param("i", $event_id);
if ($stmt->execute()) {
    echo "Sự kiện đã được xóa thành công.";
} else {
    echo "Lỗi khi xóa sự kiện.";
}

$stmt->close();
$conn->close();

// Chuyển hướng về trang admin
header("Location: admin.php");
exit();
?>
