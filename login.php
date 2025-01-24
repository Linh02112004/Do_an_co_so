<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Số điện thoại hoặc CCCD
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE phone = ? OR cccd = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $row['id'];
            echo "<script>
                sessionStorage.setItem('loggedIn', true);
                window.location.href = 'index.html';
            </script>";
        } else {
            echo "Mật khẩu không đúng.";
        }
    } else {
        echo "Số điện thoại hoặc CCCD không tồn tại.";
    }
}
?>