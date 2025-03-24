<?php
session_start();
if (!isset($_SESSION['participant_id'])) {
    header("Location: ntg_dang_nhap.php");
    exit();
}
require 'db_connect.php';

$conn->query("UPDATE events e
              LEFT JOIN (SELECT event_id, SUM(amount) AS total_donations FROM donations GROUP BY event_id) d
              ON e.id = d.event_id
              SET e.status = 'completed'
              WHERE d.total_donations >= e.goal");

// Truy vấn sự kiện với thông tin tổ chức, người phụ trách và địa điểm hỗ trợ
$sql = "SELECT e.id, e.event_name AS name, e.description, e.status,
               u.name AS organization, e.organizer_name, 
               e.location, e.goal, COALESCE(SUM(d.amount), 0) AS amount_raised
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
    <title>Danh sách sự kiện</title>
    <link rel="stylesheet" href="ntg_styles.css">
</head>
<body>
    <div class="container">
    <h1>Chào mừng, <?php echo htmlspecialchars($_SESSION['participant_name']); ?>!</h1>
        <button onclick="window.location.href='dang_xuat.php'">Đăng xuất</button>
        <h2>Sự kiện đang diễn ra</h2>
            <div id="ongoing-events" class="events-list">
                <?php foreach ($events as $event): ?>
                    <?php if ($event['status'] === 'ongoing' && $event['amount_raised'] < $event['goal']): ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                            <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                            <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                            <p><strong>Địa điểm hỗ trợ:</strong> <?= htmlspecialchars($event['location']) ?></p>
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
                            <button onclick="window.location.href='ntg_event_detail.php?id=<?php echo $event['id']; ?>'">Quyên góp</button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <h2>Sự kiện đã hoàn thành</h2>
            <div id="completed-events" class="events-list">
                <?php foreach ($events as $event): ?>
                    <?php if ($event['status'] === 'completed' || $event['amount_raised'] >= $event['goal']): ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                            <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                            <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                            <p><strong>Địa điểm hỗ trợ:</strong> <?= htmlspecialchars($event['location']) ?></p>
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
                            <button onclick="window.location.href='ntg_event_detail.php?id=<?php echo $event['id']; ?>'">Quyên góp</button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
