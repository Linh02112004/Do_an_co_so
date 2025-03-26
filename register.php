<?php
session_start();
require 'db_connect.php'; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['registerRole'];
    $email = trim($_POST['registerEmail']);
    $password = password_hash($_POST['registerPassword'], PASSWORD_BCRYPT);
    $id = uniqid(); // Tạo ID duy nhất

    // Kiểm tra email đã tồn tại hay chưa
    $check_email_query = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_email_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Email đã tồn tại!'); window.history.back();</script>";
        exit();
    }
    $check_stmt->close();

    if ($role === 'organization') {
        $org_name = trim($_POST['orgName']);
        $query = "INSERT INTO users (id, organization_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $id, $org_name, $email, $password, $role);
    } else {
        $full_name = trim($_POST['fullName']);
        $phone = trim($_POST['phone']);
        
        // Kiểm tra số điện thoại đã tồn tại hay chưa
        $check_phone_query = "SELECT id FROM users WHERE phone = ?";
        $check_stmt = $conn->prepare($check_phone_query);
        $check_stmt->bind_param("s", $phone);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Số điện thoại đã tồn tại!'); window.history.back();</script>";
            exit();
        }
        $check_stmt->close();
        
        $query = "INSERT INTO users (id, full_name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $id, $full_name, $phone, $email, $password, $role);
    }
    
    if ($stmt->execute()) {
        echo "<script>alert('Đăng ký thành công!'); window.location.href='Home.html';</script>";
    } else {
        echo "<script>alert('Lỗi đăng ký! Vui lòng thử lại.'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
} 
?>
