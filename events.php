<?php
include 'db.php';
header("Content-Type: application/json");

$sql = "SELECT id, title, description, raised_amount, target_amount, image_path FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image_path'] = 'data:image/jpeg;base64,' . base64_encode($row['image_path']);
        $events[] = $row;
    }
}
echo json_encode($events);
?>
