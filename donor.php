<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT full_name FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql_user)) {
    $stmt->bind_param("s", $user_id); // Kiểu dữ liệu "s" thay vì "i" nếu ID là dạng chuỗi
    $stmt->execute();
    $result_user = $stmt->get_result();
    $user = $result_user->fetch_assoc();
    $stmt->close();
}

$full_name = $user ? htmlspecialchars($user['full_name']) : "Người dùng";

// Cập nhật trạng thái sự kiện dựa trên tổng số tiền quyên góp
$conn->query("UPDATE events e
              LEFT JOIN (SELECT event_id, SUM(amount) AS total_donations 
                         FROM donations GROUP BY event_id) d
              ON e.id = d.event_id
              SET e.status = 'completed'
              WHERE COALESCE(d.total_donations, 0) >= e.goal");

// Truy vấn thông tin sự kiện cùng tổ chức và số tiền đã quyên góp
$sql = "SELECT e.id AS event_id, e.event_name AS name, e.description, e.status,
               u.organization_name AS organization, e.organizer_name, 
               e.location, e.goal, COALESCE(SUM(d.amount), 0) AS amount_raised
        FROM events e
        LEFT JOIN donations d ON e.id = d.event_id
        JOIN users u ON e.user_id = u.id
        GROUP BY e.id, u.organization_name";

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
    <title>🌱 HY VỌNG - Người quyên góp</title>
    <link rel="stylesheet" href="style/donor.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="donor.php">🌱 HY VỌNG</a></h1>
        <div class="header-right">
            <div id="userMenu">
                <span id="userName">Xin chào, <?php echo $full_name; ?></span>
                <span id="arrowDown" class="arrow">▼</span>
                <div id="dropdown" class="dropdown-content">
                    <a href="#">Cập nhật thông tin</a>
                    <a href="#">Thay đổi mật khẩu</a>
                    <a href="logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div id="searchBoxContainer">
            <input type="text" id="searchBox" placeholder="Tìm kiếm sự kiện">
            <button id="searchButton">Tìm kiếm</button>
        </div>

        <h2>Sự kiện đang diễn ra</h2>
        <div id="ongoing-events" class="events-list">
            <?php foreach ($events as $event): ?>
                <?php if ($event['status'] === 'ongoing' && $event['amount_raised'] < $event['goal']): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                        <div class="event-description"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
                        <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event['organization']); ?></p>
                        <p><strong>Người phụ trách:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                        <p><strong>Địa điểm hỗ trợ:</strong> <?= htmlspecialchars($event['location']) ?></p>
                        <?php
                        $goal = $event['goal'];
                        $raised = $event['amount_raised'];
                        $progress = ($goal > 0) ? min(100, ($raised / $goal) * 100) : 0;
                        ?>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%;">
                            <?php echo $progress; ?>%
                            </div>
                        </div>
                        <button onclick="window.location.href='dn_eventDetails.php?id=<?php echo $event['event_id']; ?>'">Quyên góp</button>
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
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%;">
                            <?php echo $progress; ?>%
                            </div>
                        </div>
                        <button onclick="window.location.href='dn_eventDetails.php?id=<?php echo $event['event_id']; ?>'">Quyên góp</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </main>  

    <footer>
        <div class="footer-container">
            <h1>🌱 HY VỌNG</h1>
            <ul class="footer-links">
                <li><a href="#">Điều khoản & Điều kiện</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Chính sách Cookie</a></li>
            </ul>
            <p class="footer-copyright">Copyright © 2025 Hope.</p>
        </div>
    </footer>

    <script src="script.js" defer></script>
</body>
</html>
