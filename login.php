<?php
session_start();
require 'db_connect.php'; // Kết nối database

$error = ""; // Biến lưu thông báo lỗi

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['loginRole'];
    $identity = trim($_POST['loginIdentity']);
    $password = $_POST['loginPassword'];

    // Kiểm tra role hợp lệ
    $valid_roles = ['admin', 'organization', 'donor'];
    if (!in_array($role, $valid_roles)) {
        $error = "Vai trò không hợp lệ!";
    } else {
        // Truy vấn dựa trên role
        if ($role === 'donor') {
            $query = "SELECT id, password_hash FROM users WHERE (email = ? OR phone = ?) AND role = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $identity, $identity, $role);
        } else {
            $query = "SELECT id, password_hash FROM users WHERE email = ? AND role = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $identity, $role);
        }

        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password_hash);
            $stmt->fetch();

            if (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;

                // Điều hướng dựa trên role
                $redirect_page = [
                    'admin' => 'admin.php',
                    'organization' => 'organization.php',
                    'donor' => 'donor.php'
                ];

                // Hiển thị thông báo trước khi chuyển trang
                echo "<script>
                        alert('Đăng nhập thành công!');
                        window.location.href = '{$redirect_page[$role]}';
                      </script>";
                exit();
            }
        }

        // Nếu truy vấn thất bại hoặc sai mật khẩu
        $error = "Thông tin đăng nhập chưa chính xác!";
        $stmt->close();
    }

    $conn->close();
}

// Hiển thị alert nếu có lỗi
if (!empty($error)) {
    echo "<script>alert('$error'); window.history.back();</script>";
    exit();
}
?>
