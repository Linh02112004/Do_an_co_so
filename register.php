<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $account_number = $_POST['account_number'];

    $sql = "INSERT INTO users (username, password, account_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $account_number);
    
    if ($stmt->execute()) {
        echo "Đăng ký thành công!";
        header("Location: login.html");
    } else {
        echo "Đăng ký thất bại: " . $conn->error;
    }
}
?>