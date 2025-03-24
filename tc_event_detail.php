<?php
require 'db_connect.php';

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);
$sql = "SELECT e.*, u.name AS organizer, 
               (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE event_id = e.id) AS total_donated,
               (SELECT COUNT(*) FROM donations WHERE event_id = e.id) AS donation_count
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Sự kiện không tồn tại.");
}

$stmt->close();

// Lấy danh sách quyên góp
$sql_donations = "SELECT donor_name, amount, donated_at FROM donations WHERE event_id = ? ORDER BY donated_at DESC";
$stmt = $conn->prepare($sql_donations);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$donations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Sự kiện</title>
    <link rel="stylesheet" href="tc_styles.css">
    <script>
        function toggleDonations() {
            var list = document.getElementById("donationList");
            if (list.style.display === "none") {
                list.style.display = "block";
            } else {
                list.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($event["event_name"]); ?></h1>
        <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($event["description"])); ?></p>
        <p><strong>Tổ chức chịu trách nhiệm:</strong> <?php echo htmlspecialchars($event["organizer"]); ?></p>
        <p><strong>Tên người phụ trách:</strong> <?php echo htmlspecialchars($event["organizer_name"]); ?></p>
        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($event["phone"]); ?></p>
        <p><strong>Địa chỉ người phụ trách:</strong> <?php echo htmlspecialchars($event["address"]); ?></p>
        <p><strong>Địa chỉ hỗ trợ:</strong> <?php echo htmlspecialchars($event["location"]); ?></p>
        <p><strong>Mục tiêu quyên góp:</strong> <?php echo number_format($event["goal"])." VND"; ?></p>
        <p><strong>Số tiền đã quyên góp:</strong> <?php echo number_format($event["total_donated"])." VND"; ?></p>
        <button onclick="toggleDonations()">Danh sách quyên góp</button>
        <div id="donationList" style="display: none;">
            <h3>Danh sách quyên góp</h3>
            <table border="1">
                <tr>
                    <th>Tên</th>
                    <th>Số tiền</th>
                    <th>Thời gian</th>
                </tr>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($donation["donor_name"]); ?></td>
                        <td><?php echo number_format($donation["amount"])." VND"; ?></td>
                        <td><?php echo $donation["donated_at"]; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <button onclick="window.location.href='tc_index.php'">Quay lại</button>
        <?php if ($event["donation_count"] == 0): ?>
            <button onclick="window.location.href='tc_delete_event.php?id=<?php echo $event_id; ?>'">Xóa sự kiện</button>
        <?php endif; ?>
        <button onclick="window.location.href='tc_request_edit.php?id=<?php echo $event_id; ?>'">Yêu cầu sửa sự kiện</button>
    </div>
</body>
</html>
