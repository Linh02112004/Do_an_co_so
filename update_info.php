<?php
include 'db.php';
session_start();

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập.");
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng hiện tại
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Nếu form được gửi đi, xử lý cập nhật dữ liệu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $full_name = $_POST['full_name'] ?? $user['full_name'];
    $dob = $_POST['dob'] ?? $user['dob'];
    $cccd = $_POST['cccd'] ?? $user['cccd'];
    $phone = $_POST['phone'] ?? $user['phone'];
    $email = $_POST['email'] ?? $user['email'];
    $address = $_POST['address'] ?? $user['address'];
    $bank_account = $_POST['bank_account'] ?? $user['bank_account'];
    $beneficiary_bank = $_POST['beneficiary_bank'] ?? $user['beneficiary_bank'];

    // Cập nhật thông tin vào cơ sở dữ liệu
    $sql_update = "UPDATE users SET full_name=?, dob=?, cccd=?, phone=?, email=?, address=?, bank_account=?, beneficiary_bank=? WHERE id=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssssssi", $full_name, $dob, $cccd, $phone, $email, $address, $bank_account, $beneficiary_bank, $user_id);

    if ($stmt->execute()) {
        // Hiển thị thông báo và reload trang
        echo "<script>
                alert('Cập nhật thông tin thành công!');
                window.location.href = window.location.href;
              </script>";
        exit;
    } else {
        echo "<script>alert('Lỗi khi cập nhật! Vui lòng thử lại.');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Cập nhật thông tin</title>
</head>
<body>
    <header>
        <h1>Cập nhật thông tin</h1>
        <nav>
            <div class="home-link-container">
                <a id="homeLink" href="index.html" class="home-link">Trang chính</a>
            </div>
        </nav>
    </header>
    <main>
        <form action="" method="POST">
            <label for="full_name">Họ và tên:</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
        
            <label for="dob">Ngày sinh:</label>
            <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>" required>
        
            <label for="cccd">Số CCCD:</label>
            <input type="text" id="cccd" name="cccd" value="<?= htmlspecialchars($user['cccd'] ?? '') ?>" required>
        
            <label for="phone">Số điện thoại:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
        
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        
            <label for="address">Địa chỉ:</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
        
            <label for="bank_account">Số tài khoản:</label>
            <input type="text" id="bank_account" name="bank_account" value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>" required>
        
            <label for="beneficiary_bank">Ngân hàng thụ hưởng:</label>
            <input type="text" id="beneficiary_bank" name="beneficiary_bank" value="<?= htmlspecialchars($user['beneficiary_bank'] ?? '') ?>" required>
            
            <button type="submit">Cập nhật</button>
        </form>
    </main>
</body>
</html>
