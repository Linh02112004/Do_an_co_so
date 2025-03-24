<?php
$servername = "localhost"; // Hoặc địa chỉ máy chủ MySQL
$username = "root"; // Thay bằng username của MySQL
$password = ""; // Thay bằng password của MySQL
$database = "charity_management";

// Kết nối tới MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
