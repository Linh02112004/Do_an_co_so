<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: tc_dang_nhap.php");
    exit();
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Lấy thông báo chưa đọc mới nhất
$sql_notification = "SELECT id, message FROM notifications WHERE user_id = ? AND seen = 0 ORDER BY created_at DESC LIMIT 1";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("i", $user_id);
$stmt_notification->execute();
$result_notification = $stmt_notification->get_result();
$notification = $result_notification->fetch_assoc();
$stmt_notification->close();

// Nếu có thông báo, đánh dấu là đã đọc ngay khi hiển thị
if ($notification) {
    $sql_mark_seen = "UPDATE notifications SET seen = 1 WHERE id = ?";
    $stmt_mark_seen = $conn->prepare($sql_mark_seen);
    $stmt_mark_seen->bind_param("i", $notification['id']);
    $stmt_mark_seen->execute();
    $stmt_mark_seen->close();
}

// Cập nhật trạng thái sự kiện khi đủ số tiền
$conn->query("UPDATE events e
              LEFT JOIN (SELECT event_id, SUM(amount) AS total_donations FROM donations GROUP BY event_id) d
              ON e.id = d.event_id
              SET e.status = 'completed'
              WHERE d.total_donations >= e.goal");

// Lấy danh sách sự kiện của tổ chức đang đăng nhập
$sql = "SELECT e.id, e.event_name AS name, e.description, e.status, 
               u.name AS organization, e.organizer_name, e.goal, 
               COALESCE(SUM(d.amount), 0) AS amount_raised
        FROM events e
        LEFT JOIN donations d ON e.id = d.event_id
        JOIN users u ON e.user_id = u.id
        WHERE e.user_id = ?
        GROUP BY e.id, u.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Quản lý Sự kiện</title>
    <link rel="stylesheet" href="tc_styles.css">
</head>
<body>
    <div class="container">
        <h1>Chào mừng, <?php echo $_SESSION['user_name']; ?>!</h1>
        <button onclick="window.location.href='tc_tao_su_kien.html'">Tạo Sự kiện</button>
        <button onclick="window.location.href='dang_xuat.php'">Đăng xuất</button>
        
        <?php if (!empty($notification)) : ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
            </div>
        <?php endif; ?>

        <h2>Đang diễn ra</h2>
        <div id="ongoing-events" class="events-list">
            <?php foreach ($events as $event): ?>
                <?php if ($event['status'] === 'ongoing' && $event['amount_raised'] < $event['goal']): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                        <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                        <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                        <?php
                        $goal = $event['goal'];
                        $raised = $event['amount_raised'];
                        $progress = ($goal > 0) ? min(100, ($raised / $goal) * 100) : 0;
                        ?>
                        <!-- Thanh tiến độ -->
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
                                <?php echo number_format($raised, 0, ',', '.'); ?> / <?php echo number_format($goal, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        <button onclick="window.location.href='tc_event_detail.php?id=<?php echo $event['id']; ?>'">Xem</button>
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
                        <?php
                        $goal = $event['goal'];
                        $raised = $event['amount_raised'];
                        $progress = ($goal > 0) ? min(100, ($raised / $goal) * 100) : 0;
                        ?>
                        <!-- Thanh tiến độ -->
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
                                <?php echo number_format($raised, 0, ',', '.'); ?> / <?php echo number_format($goal, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        <button onclick="window.location.href='tc_event_detail.php?id=<?php echo $event['id']; ?>'">Xem</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
