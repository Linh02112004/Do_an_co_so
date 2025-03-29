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
$stmt->close();

if (!$event) {
    die("Sự kiện không tồn tại.");
}

// Xử lý cập nhật sự kiện
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

        $sql_update = "UPDATE events 
                       SET event_name=?, description=?, location=?, goal=?, organizer_name=?, phone=? 
                       WHERE id=?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssdssi", $event_name, $description, $location, $goal, $organizer_name, $phone, $event_id);

        if ($stmt->execute()) {
            echo "Sự kiện đã được cập nhật thành công.";
            header("Location: or_eventDetails.php?id=" . $event_id);
            exit();
        } else {
            echo "Lỗi khi cập nhật sự kiện.";
        }

        $stmt->close();
    } else {
        echo "Thiếu thông tin cập nhật sự kiện.";
    }
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
    <button onclick="window.location.href='or_eventDetails.php?id=<?php echo $event_id; ?>'">Hủy</button>
</body>
</html>
