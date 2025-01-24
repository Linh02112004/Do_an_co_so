<?php
include 'db.php';

$sql = "SELECT * FROM events";
$result = $conn->query($sql);

$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
echo json_encode($events);
?>