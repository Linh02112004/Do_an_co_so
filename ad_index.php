<?php
session_start();
require 'db_connect.php';

// Kiểm tra nếu quản trị viên đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ad_login.php");
    exit();
}

// Cập nhật trạng thái sự kiện khi đủ số tiền
$conn->query("UPDATE events e
              LEFT JOIN (SELECT event_id, SUM(amount) AS total_donations FROM donations GROUP BY event_id) d
              ON e.id = d.event_id
              SET e.status = 'completed'
              WHERE d.total_donations >= e.goal");

// Lấy danh sách sự kiện của tất cả tổ chức
$sql = "SELECT e.id, e.event_name AS name, e.description, e.status, 
               u.name AS organization, e.organizer_name, e.goal, 
               COALESCE(SUM(d.amount), 0) AS amount_raised,
               (SELECT COUNT(*) FROM event_edits WHERE event_id = e.id AND status = 'pending') AS pending_edits
        FROM events e
        LEFT JOIN donations d ON e.id = d.event_id
        JOIN users u ON e.user_id = u.id
        GROUP BY e.id, u.name";
$result = $conn->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Quản lý Sự kiện</title>
    <link rel="stylesheet" href="ad_styles.css">
</head>
<body>
    <div class="container">
        <h1>Trang Quản trị</h1>
        <button onclick="window.location.href='ad_logout.php'">Đăng xuất</button>

        <h2>Đang diễn ra</h2>
        <div id="ongoing-events" class="events-list">
            <?php foreach ($events as $event): ?>
                <?php if ($event['status'] === 'ongoing' && $event['amount_raised'] < $event['goal']): ?>
                    <div class="event-card">
                        <?php if ($event['pending_edits'] > 0): ?>
                            <span class="warning-icon">❗</span>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                        <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                        <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                        <p><strong>Mục tiêu:</strong> <?php echo number_format($event['goal']); ?> VND</p>
                        <p><strong>Đã quyên góp:</strong> <?php echo number_format($event['amount_raised']); ?> VND</p>
                        <button onclick="window.location.href='ad_event_detail.php?id=<?php echo $event['id']; ?>'">Xem</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <h2>Đã hoàn thành</h2>
        <div id="completed-events" class="events-list">
            <?php foreach ($events as $event): ?>
                <?php if ($event['status'] === 'completed' || $event['amount_raised'] >= $event['goal']): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                        <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                        <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                        <p><strong>Mục tiêu:</strong> <?php echo number_format($event['goal']); ?> VND</p>
                        <p><strong>Đã quyên góp:</strong> <?php echo number_format($event['amount_raised']); ?> VND</p>
                        <button onclick="window.location.href='ad_event_detail.php?id=<?php echo $event['id']; ?>'">Xem</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
