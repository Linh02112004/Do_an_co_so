<?php
require 'db_connect.php';
session_start();

if (!isset($_POST["event_id"]) || !isset($_POST["comment"])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Bạn cần đăng nhập để bình luận."]);
    exit;
}

$event_id = intval($_POST["event_id"]);
$user_id = $_SESSION["user_id"];
$comment = trim($_POST["comment"]);

$sql = "INSERT INTO comments (event_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $event_id, $user_id, $comment);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Không thể thêm bình luận."]);
}

$stmt->close();
$conn->close();
?>
