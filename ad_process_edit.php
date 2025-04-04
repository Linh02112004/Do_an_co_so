<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Bạn chưa đăng nhập.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'], $_POST['action'])) {
    $event_id = intval($_POST['event_id']);
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    // Kiểm tra xem bản chỉnh sửa có tồn tại không
    $sql_check = "SELECT * FROM event_edits WHERE event_id = ? AND status = 'pending'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $event_id);
    $stmt_check->execute();
    $edited_event = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$edited_event) {
        die("Không tìm thấy bản chỉnh sửa hoặc đã được xử lý.");
    }

    if ($action === "approve") {
        // Cập nhật sự kiện từ event_edits
        $sql_update = "UPDATE events SET 
                       event_name = ?, 
                       description = ?, 
                       location = ?, 
                       goal = ?, 
                       organizer_name = ?, 
                       phone = ? 
                       WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param(
            "sssdssi", 
            $edited_event['event_name'], 
            $edited_event['description'], 
            $edited_event['location'], 
            $edited_event['goal'], 
            $edited_event['organizer_name'], 
            $edited_event['phone'], 
            $event_id
        );
        if (!$stmt_update->execute()) {
            die("Lỗi khi cập nhật sự kiện: " . $stmt_update->error);
        }
        $stmt_update->close();

        // Lấy tên sự kiện đã được duyệt
        $event_name = htmlspecialchars($edited_event['event_name']);

        // Gửi thông báo cho tổ chức
        $sql_notify = "INSERT INTO notifications (user_id, message) 
                       VALUES ((SELECT user_id FROM events WHERE id = ?), ?)";
        $stmt_notify = $conn->prepare($sql_notify);
        $message = "Yêu cầu chỉnh sửa sự kiện $event_name của bạn đã được duyệt và cập nhật thành công.";
        $stmt_notify->bind_param("is", $event_id, $message);
        $stmt_notify->execute();
        $stmt_notify->close();

    } elseif ($action === "reject") {
        if (empty($reason)) {
            die("Lý do từ chối không được để trống.");
        }

        // Lấy tên sự kiện đã bị từ chối
        $event_name = htmlspecialchars($edited_event['event_name']);

        // Gửi thông báo từ chối cho tổ chức
        $sql_notify = "INSERT INTO notifications (user_id, message) 
                       VALUES ((SELECT user_id FROM events WHERE id = ?), ?)";
        $stmt_notify = $conn->prepare($sql_notify);
        $message = "Yêu cầu chỉnh sửa sự kiện $event_name của bạn bị từ chối: " . $reason;
        $stmt_notify->bind_param("is", $event_id, $message);
        $stmt_notify->execute();
        $stmt_notify->close();
    }

    // Xóa bản ghi chỉnh sửa sau khi xử lý
    $sql_delete_edit = "DELETE FROM event_edits WHERE event_id = ?";
    $stmt_delete = $conn->prepare($sql_delete_edit);
    $stmt_delete->bind_param("i", $event_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    header("Location: admin.php");
    exit();
} else {
    die("Yêu cầu không hợp lệ.");
}
?>
