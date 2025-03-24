<?php
require 'db_connect.php';

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);

// Lấy thông tin sự kiện
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Sự kiện không tồn tại.");
}

$stmt->close();

// Xử lý cập nhật sự kiện
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST["event_name"];
    $description = $_POST["description"];
    $location = $_POST["location"];
    $goal = $_POST["goal"];
    $organizer_name = $_POST["organizer_name"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];

    $sql_update = "UPDATE events SET event_name=?, description=?, location=?, goal=?, organizer_name=?, phone=?, address=? WHERE id=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssdsssi", $event_name, $description, $location, $goal, $organizer_name, $phone, $address, $event_id);

    if ($stmt->execute()) {
        echo "Sự kiện đã được cập nhật thành công.";
        header("Location: tc_event_detail.php?id=".$event_id);
        exit();
    } else {
        echo "Lỗi khi cập nhật sự kiện.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa Sự kiện</title>
</head>
<body>
    <h1>Chỉnh sửa Sự kiện</h1>
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
        <button type="submit">Cập nhật</button>
    </form>
    <button onclick="window.location.href='tc_event_detail.php?id=<?php echo $event_id; ?>'">Hủy</button>
</body>
</html>
