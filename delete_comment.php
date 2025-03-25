<?php
include 'db.php';
session_start();
$user_id = $_SESSION['user_id'];
$comment_id = $_POST['id'];

// Kiểm tra xem người dùng có phải chủ bình luận không
$query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
?>
