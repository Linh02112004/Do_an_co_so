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
        
        // Kiểm tra mật khẩu hiện tại
        if (password_verify($current_password, $row['password'])) {
            // Kiểm tra nếu mật khẩu mới giống mật khẩu hiện tại
            if (password_verify($new_password, $row['password'])) {
                echo "<script>alert('Mật khẩu mới không được trùng với mật khẩu hiện tại.'); window.history.back();</script>";
                exit;
            }

            // Kiểm tra nếu mật khẩu mới khớp với xác nhận
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    echo "<script>alert('Thay đổi mật khẩu thành công!'); window.location.href = 'login.html';</script>";
                    exit;
                } else {
                    echo "<script>alert('Thay đổi mật khẩu thất bại!'); window.history.back();</script>";
                }
            } else {
                echo "<script>alert('Mật khẩu mới không khớp.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Mật khẩu hiện tại không đúng.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy người dùng.'); window.history.back();</script>";
    }
}
?>
