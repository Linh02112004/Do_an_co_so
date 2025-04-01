<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

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
               e.location, e.goal, COALESCE(SUM(d.amount), 0) AS amount_raised,
               (SELECT COUNT(*) FROM event_edits ee WHERE ee.event_id = e.id AND ee.status = 'pending') AS pending
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
    <title>🌱 HY VỌNG - Quản trị viên</title>
    <link rel="stylesheet" href="style/admin.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="admin.php">🌱 HY VỌNG</a></h1>
        <div class="header-right">
            <div id="userMenu">
                <span id="userName">Xin chào, Quản Trị Viên</span>
                <span id="arrowDown" class="arrow">▼</span>
                <div id="dropdown" class="dropdown-content">
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
                        <h3>
                            <?php if ($event['pending'] > 0): ?>
                                <span class="warning-icon"><big>❗</big></span>
                            <?php endif; ?>

                            <?php echo htmlspecialchars($event['name']); ?>
                        </h3>
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
                        <button onclick="window.location.href='ad_eventDetails.php?id=<?php echo $event['event_id']; ?>'">Xem</button>
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
                        <button onclick="window.location.href='ad_eventDetails.php?id=<?php echo $event['event_id']; ?>'">Xem</button>
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

    <script>
        // Tm kiếm Sự kiện theo tên
        const searchBox = document.getElementById("searchBox");
            const eventCards = document.querySelectorAll(".event-card");

            searchBox.addEventListener("input", function () {
                const searchText = searchBox.value.trim().toLowerCase();

                eventCards.forEach(eventCard => {
                    const eventName = eventCard.querySelector("h3").textContent.toLowerCase();
                    if (eventName.includes(searchText)) {
                        eventCard.style.display = "block";
                    } else {
                        eventCard.style.display = "none";
                    }
                });
            });
    </script>
</body>
</html>
