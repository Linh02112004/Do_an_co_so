<?php
require 'db_connect.php';

$sql = "SELECT e.id, e.event_name AS name, e.description, e.status, 
               COALESCE(u.organization_name, u.full_name) AS organizer 
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        ORDER BY e.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);

$stmt->close();
$conn->close();
?>
