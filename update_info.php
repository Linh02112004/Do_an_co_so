<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu người dùng đã đăng nhập
    if (!isset($_SESSION['user_id'])) {
        echo "Bạn cần đăng nhập để cập nhật thông tin.";
        exit;
    }

    // Lấy thông tin từ form
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $cccd = $_POST['cccd'];
    $address = $_POST['address'];
    $bank_account = $_POST['bank_account'];
    $beneficiary_bank = $_POST['beneficiary_bank'];
    $user_id = $_SESSION['user_id'];

    // Cập nhật thông tin người dùng trong cơ sở dữ liệu
    $sql = "UPDATE users SET full_name=?, dob=?, cccd=?, address=?, bank_account=?, beneficiary_bank=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $full_name, $dob, $cccd, $address, $bank_account, $beneficiary_bank, $user_id);

    if ($stmt->execute()) {
        echo "Cập nhật thông tin thành công!";
        header("Location: index.html");
        exit;
    } else {
        echo "Cập nhật thông tin thất bại: " . $conn->error;
    }
}
?>