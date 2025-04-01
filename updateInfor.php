<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Bạn chưa đăng nhập.");
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng từ CSDL
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); 
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc(); 
$stmt->close();

// Kiểm tra nếu không tìm thấy người dùng
if (!$user) {
    die("Lỗi: Không tìm thấy thông tin người dùng.");
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra biến role có tồn tại không
    if (!isset($_POST['role'])) {
        die("Lỗi: Không xác định được vai trò người dùng.");
    }

    $updateSuccess = false; // Định nghĩa biến để tránh lỗi Undefined variable

    if ($_POST['role'] === 'donor') {
        // Cập nhật thông tin người quyên góp
        $fullname = htmlspecialchars($_POST['fullname']);
        $phone = htmlspecialchars($_POST['phone']);
        $email = htmlspecialchars($_POST['email']);
        $social_media = htmlspecialchars($_POST['social_media']);

        $query = "UPDATE users SET full_name = ?, phone = ?, email = ?, social_media = ? WHERE id = ?";
        $stmt = $conn->prepare($query); 
        $stmt->bind_param("sssss", $fullname, $phone, $email, $social_media, $user_id);
        $updateSuccess = $stmt->execute(); // Gán kết quả của execute()
        $stmt->close();
    } elseif ($_POST['role'] === 'organization') {
        // Cập nhật thông tin tổ chức
        $org_name = htmlspecialchars($_POST['org_name']);
        $org_description = htmlspecialchars($_POST['org_description']);
        $contact_phone = htmlspecialchars($_POST['contact_phone']);
        $email = htmlspecialchars($_POST['email']);
        $address = htmlspecialchars($_POST['address']);
        $website = htmlspecialchars($_POST['website']);
        $social_media = htmlspecialchars($_POST['social_media']);

        $query = "UPDATE users SET organization_name = ?, description = ?, phone = ?, email = ?, address = ?, website = ?, social_media = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", $org_name, $org_description, $contact_phone, $email, $address, $website, $social_media, $user_id);
        $updateSuccess = $stmt->execute();
        $stmt->close();
    }

    // Kiểm tra kết quả cập nhật
    if ($updateSuccess) {
        echo "<script>
            alert('Thông tin đã được cập nhật thành công!');
            window.location.href = '" . ($user['role'] === 'donor' ? 'donor.php' : 'organization.php') . "';
        </script>";
    } else {
        echo "<script>
            alert('Có lỗi xảy ra, vui lòng thử lại!');
            window.history.back();
        </script>";
    }
    exit();
}
?>
