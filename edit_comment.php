<?php
include 'db.php';
session_start();
$user_id = $_SESSION['user_id'];
$comment_id = $_POST['id'];
$new_comment = $_POST['comment'];

// Kiểm tra xem người dùng có phải chủ bình luận không
$query = "UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $new_comment, $comment_id, $user_id);
$stmt->execute();
?>
