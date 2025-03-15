<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Bạn cần đăng nhập để tạo sự kiện."]);
        exit;
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $target_amount = trim($_POST['target_amount']);
    $created_by = $_SESSION['user_id'];

    // Kiểm tra nếu thiếu dữ liệu
    if (empty($title) || empty($description) || empty($target_amount)) {
        echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin!"]);
        exit;
    }

    // Kiểm tra ảnh trước khi xử lý
    if (!isset($_FILES['event_image']) || $_FILES['event_image']['error'] !== 0) {
        echo json_encode(["success" => false, "message" => "Vui lòng tải lên một ảnh hợp lệ!"]);
        exit;
    }

    // Đọc dữ liệu ảnh
    $image_data = file_get_contents($_FILES['event_image']['tmp_name']);
    $image_data = addslashes($image_data);

    // Kiểm tra xem sự kiện đã tồn tại chưa (dựa vào tiêu đề)
$check_sql = "SELECT id FROM events WHERE title = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $title);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo "<script>alert('Sự kiện này đã tồn tại!'); window.location.href='create_event.html';</script>";
    exit;
}

    // Chèn dữ liệu vào bảng
    $sql = "INSERT INTO events (title, description, target_amount, created_by, image_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $title, $description, $target_amount, $created_by, $image_data);

    if ($stmt->execute()) {
    echo "<script>alert('Tạo sự kiện thành công!'); window.location.href='index.html';</script>";
    } else {
    echo "<script>alert('Tạo sự kiện thất bại: " . $conn->error . "'); window.history.back();</script>";
    }
}
?>
