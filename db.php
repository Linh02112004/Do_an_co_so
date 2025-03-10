<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "charity_events";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); // Đảm bảo đọc dữ liệu đúng

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
