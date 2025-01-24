<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu người dùng đã đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo "Bạn cần đăng nhập để thay đổi mật khẩu.";
        exit;
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $sql = "SELECT password FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($current_password, $row['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    echo "Thay đổi mật khẩu thành công!";
                    header("Location: index.html");
                    exit;
                } else {
                    echo "Thay đổi mật khẩu thất bại: " . $conn->error;
                }
            } else {
                echo "Mật khẩu mới không khớp.";
            }
        } else {
            echo "Mật khẩu hiện tại không đúng.";
        }
    } else {
        echo "Không tìm thấy người dùng.";
    }
}
?>