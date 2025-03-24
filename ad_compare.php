<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ad_login.php");
    exit();
}

$event_id = $_GET['id'] ?? 0;

// Lấy dữ liệu sự kiện gốc
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$original_event = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy dữ liệu chỉnh sửa
$sql = "SELECT * FROM event_edits WHERE event_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$edited_event = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$edited_event) {
    echo "<p>Không có chỉnh sửa nào cần xét duyệt.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>So sánh chỉnh sửa</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>So sánh thay đổi</h2>
    <table border="1">
        <tr>
            <th>Trường</th>
            <th>Dữ liệu cũ</th>
            <th>Dữ liệu mới</th>
        </tr>
        <tr>
            <td>Tên sự kiện</td>
            <td><?php echo htmlspecialchars($original_event['event_name']); ?></td>
            <td><?php echo htmlspecialchars($edited_event['event_name']); ?></td>
        </tr>
        <tr>
            <td>Mô tả</td>
            <td><?php echo nl2br(htmlspecialchars($original_event['description'])); ?></td>
            <td><?php echo nl2br(htmlspecialchars($edited_event['description'])); ?></td>
        </tr>
        <tr>
            <td>Người phụ trách</td>
            <td><?php echo htmlspecialchars($original_event['organizer_name']); ?></td>
            <td><?php echo htmlspecialchars($edited_event['organizer_name']); ?></td>
        </tr>
        <tr>
            <td>Mục tiêu</td>
            <td><?php echo number_format($original_event['goal']); ?> VND</td>
            <td><?php echo number_format($edited_event['goal']); ?> VND</td>
        </tr>
    </table>

    <form method="post" action="ad_process_edit.php">
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
    <input type="hidden" name="edit_id" value="<?php echo $edited_event['id']; ?>">
    <button type="submit" name="action" value="approve">Chấp nhận</button>
    <button type="button" onclick="document.getElementById('reject-reason').style.display='block'">Từ chối</button>
    
    <div id="reject-reason" style="display:none;">
        <textarea name="reason" placeholder="Nhập lý do từ chối"></textarea>
        <button type="submit" name="action" value="reject">Xác nhận từ chối</button>
    </div>
</form>

</body>
</html>
