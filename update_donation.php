<?php
include 'db.php';

$event_id = intval($_POST['event_id']);
$amount = floatval($_POST['amount']);

// Update the raised amount for the event
$sql = "UPDATE events SET raised_amount = raised_amount + ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $amount, $event_id);

if ($stmt->execute()) {
    // Success, optionally return a success message
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cập nhật không thành công!']);
}

$stmt->close();
$conn->close();
?>