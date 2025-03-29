<?php
session_start();
require 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Bạn cần đăng nhập để yêu cầu chỉnh sửa.');</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
        echo "<script>alert('Lỗi: Sự kiện không tồn tại.');</script>";
        exit();
    }
    $event_id = intval($_POST['event_id']); // Lấy event_id từ form
} else {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<script>alert('Lỗi: Sự kiện không tồn tại.');</script>";
        exit();
    }
    $event_id = intval($_GET['id']); // Lấy event_id từ URL khi mở form
}

$user_id = $_SESSION['user_id']; 

// Kiểm tra sự kiện có tồn tại và thuộc về user không
$sql = "SELECT * FROM events WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $event_id, $user_id);

// Debug: Hiển thị truy vấn thực tế đang chạy
$query_debug = "SELECT * FROM events WHERE id = '$event_id' AND user_id = '$user_id'";
echo "<script>console.log('DEBUG SQL: $query_debug');</script>";

$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "<script>alert('Lỗi: Bạn không có quyền chỉnh sửa sự kiện này hoặc sự kiện không tồn tại.');</script>";
    exit();
}

// Xử lý khi tổ chức gửi yêu cầu sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        isset($_POST["event_name"], $_POST["description"], $_POST["location"], 
              $_POST["goal"], $_POST["organizer_name"], $_POST["phone"])
    ) {
        $event_name = trim($_POST["event_name"]);
        $description = trim($_POST["description"]);
        $location = trim($_POST["location"]);
        $goal = floatval($_POST["goal"]);
        $organizer_name = trim($_POST["organizer_name"]);
        $phone = trim($_POST["phone"]);

        // Lưu yêu cầu chỉnh sửa vào bảng event_edits
        $sql_insert = "INSERT INTO event_edits (event_id, user_id, event_name, description, location, goal, organizer_name, phone) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("issssdis", $event_id, $user_id, $event_name, $description, $location, $goal, $organizer_name, $phone);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('Yêu cầu chỉnh sửa đã được gửi! Chờ quản trị viên duyệt.');
                    window.location.href = 'or_eventDetails.php?id={$event_id}';
                  </script>";
        } else {
            echo "<script>alert('Lỗi khi gửi yêu cầu chỉnh sửa: " . addslashes($stmt->error) . "');</script>";
        }
        
        $stmt->close();
        $conn->close();
        exit();
    } else {
        echo "<script>alert('Thiếu thông tin cần thiết.');</script>";
    }
}
?>
