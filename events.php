<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include 'db.php';

$sql = "SELECT id, title, description, raised_amount, target_amount, image_path FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image_path'] = 'data:image/jpeg;base64,' . $row['image_path']; 
        $events[] = $row;
    }
}

echo json_encode($events);
?>
