<?php
require 'db_connect.php';

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);

// Kiểm tra xem sự kiện có quyên góp nào không
$sql_check = "SELECT COUNT(*) as donation_count FROM donations WHERE event_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();

if ($donation["donation_count"] > 0) {
    die("Không thể xóa sự kiện vì đã có quyên góp.");
}

$stmt->close();

// Xóa sự kiện
$sql_delete = "DELETE FROM events WHERE id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("i", $event_id);
if ($stmt->execute()) {
    echo "Sự kiện đã được xóa thành công.";
} else {
    echo "Lỗi khi xóa sự kiện.";
}

$stmt->close();
$conn->close();

header("Location: tc_index.php");
exit();
?>
