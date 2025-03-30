<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['id'];

$query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $comment_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Xóa bình luận thành công"]);
} else {
    echo json_encode(["success" => false, "message" => "Không thể xóa bình luận"]);
}

$stmt->close();
$conn->close();
?>
