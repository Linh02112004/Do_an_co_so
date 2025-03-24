<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để yêu cầu chỉnh sửa.");
}

if (!isset($_GET['id'])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin sự kiện hiện tại
$sql = "SELECT * FROM events WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Bạn không có quyền chỉnh sửa sự kiện này.");
}

$stmt->close();

// Xử lý khi tổ chức gửi yêu cầu sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $goal = $_POST['goal'];
    $organizer_name = $_POST['organizer_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Lưu yêu cầu chỉnh sửa vào bảng event_edits
    $sql_insert = "INSERT INTO event_edits (event_id, user_id, event_name, description, location, goal, organizer_name, phone, address) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iisssdsss", $event_id, $user_id, $event_name, $description, $location, $goal, $organizer_name, $phone, $address);
    
    if ($stmt->execute()) {
        echo "<script>alert('Yêu cầu chỉnh sửa đã được gửi! Chờ quản trị viên duyệt.'); window.location.href='tc_index.php';</script>";
    } else {
        echo "Lỗi khi gửi yêu cầu chỉnh sửa: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Yêu cầu Sửa Sự kiện</title>
</head>
<body>
    <h1>Yêu cầu Sửa Sự kiện</h1>
    <form method="POST">
        <label>Tên sự kiện:</label>
        <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required><br>
        <label>Mô tả:</label>
        <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea><br>
        <label>Địa chỉ hỗ trợ:</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required><br>
        <label>Mục tiêu quyên góp:</label>
        <input type="number" name="goal" value="<?php echo htmlspecialchars($event['goal']); ?>" required><br>
        <label>Tên người phụ trách:</label>
        <input type="text" name="organizer_name" value="<?php echo htmlspecialchars($event['organizer_name']); ?>" required><br>
        <label>Số điện thoại:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($event['phone']); ?>" required><br>
        <label>Địa chỉ người phụ trách:</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($event['address']); ?>" required><br>
        <button type="submit">Gửi Yêu cầu</button>
    </form>
    <button onclick="window.location.href='tc_index.php'">Hủy</button>
</body>
</html>
