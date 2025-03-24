<?php
require 'db_connect.php';

$sql = "SELECT e.id, e.event_name AS name, e.description, e.status, u.name AS organizer 
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        ORDER BY e.id DESC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
