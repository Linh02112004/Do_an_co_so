<?php
include 'db.php';
session_start();

$sql = "SELECT * FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Kiểm tra nếu người dùng đã đăng nhập
        if (isset($_SESSION['user_id']) && $row['created_by'] == $_SESSION['user_id']) {
            $events[] = $row;
        }
    }
}
echo json_encode($events);
?>