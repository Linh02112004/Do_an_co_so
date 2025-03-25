<?php
session_start();
require 'db_connect.php'; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['registerRole'];
    $email = trim($_POST['registerEmail']);
    $password = password_hash($_POST['registerPassword'], PASSWORD_BCRYPT);
    $id = uniqid(); // Tạo ID duy nhất
    
    if ($role === 'organization') {
        $org_name = trim($_POST['orgName']);
        $query = "INSERT INTO users (id, organization_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $id, $org_name, $email, $password, $role);
    } else {
        $full_name = trim($_POST['fullName']);
        $phone = trim($_POST['phone']);
        $query = "INSERT INTO users (id, full_name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $id, $full_name, $phone, $email, $password, $role);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đăng ký thành công!";
        header("Location: Home.html");
        exit();
    } else {
        $_SESSION['error'] = "Lỗi đăng ký!";
    }
    
    $stmt->close();
    $conn->close();
} 
?>
