<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo "Bạn cần đăng nhập để tạo sự kiện.";
        exit;
    }

    $title = $_POST['title'];
    $description = $_POST['description'];
    $target_amount = $_POST['target_amount'];
    $created_by = $_SESSION['user_id'];
    
    $image_path = NULL;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['event_image']['name']);
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $sql = "INSERT INTO events (title, description, target_amount, created_by, image_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $title, $description, $target_amount, $created_by, $image_path);
    
    if ($stmt->execute()) {
        echo "Tạo sự kiện thành công!";
        header("Location: index.html");
        exit;
    } else {
        echo "Tạo sự kiện thất bại: " . $conn->error;
    }
}
?>