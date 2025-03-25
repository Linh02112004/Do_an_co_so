<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Lấy tên tổ chức của người dùng
$sql_user = "SELECT organization_name FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql_user)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result_user = $stmt->get_result();
    $user = $result_user->fetch_assoc();
    $stmt->close();
}
$organization_name = $user ? htmlspecialchars($user['organization_name']) : "Tổ chức";

// Lấy thông báo chưa đọc mới nhất
$sql_notification = "SELECT id, message FROM notifications WHERE user_id = ? AND seen = 0 ORDER BY created_at DESC LIMIT 1";
$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("s", $user_id);
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
               u.organization_name AS organization, e.organizer_name, e.goal, 
               COALESCE(SUM(d.amount), 0) AS amount_raised
        FROM events e
        LEFT JOIN donations d ON e.id = d.event_id
        JOIN users u ON e.user_id = u.id
        WHERE e.user_id = ?
        GROUP BY e.id, u.organization_name, e.event_name, e.description, e.status, e.organizer_name, e.goal";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
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
    <title>Impact VN - Tổ chức</title>
    <link rel="stylesheet" href="style/organization.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="organization.php">IMPACT VN</a></h1>
        <div class="header-right">
            <div id="userMenu">
                <span id="userName">Xin chào, Tổ chức <?php echo $organization_name; ?></span>
                <span id="arrowDown" class="arrow">▼</span>
                <div id="dropdown" class="dropdown-content">
                    <a href="#">Cập nhật thông tin</a>
                    <a href="#">Thay đổi mật khẩu</a>
                    <a href="logout.php">Đăng xuất</a>
                </div>
            </div>

            <div id="authLinks" style="margin-left: auto;">
                <div class="auth-buttons">
                    <a id="createEventButton" href="#">Tạo sự kiện</a>
                    <a id="notifications" href="#">Thông báo</a>
                    <?php if (!empty($notification)) : ?>
                        <div class="notification">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        </div>
                    <?php endif; ?>
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
        
        <h2>Sự kiện đã hoàn thành</h2>
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
    </main>

    <footer>
        <div class="footer-container">
            <h1>IMPACT VN</h1>
            <ul class="footer-links">
                <li><a href="#">Điều khoản & Điều kiện</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Chính sách Cookie</a></li>
            </ul>
            <p class="footer-copyright">Copyright © 2025 Community Impact.</p>
        </div>
    </footer>

    <!-- Pop-up Tạo sự kiện -->
    <div id="create_eventModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h1>Tạo sự kiện</h1>
            <form action="tc_luu_su_kien.php" method="POST">
                <div class="form-container">
                    <!-- Thông tin Sự kiện -->
                    <div class="form-section">
                        <h2>Thông tin Sự kiện</h2>
                        <label for="event_name">Tên sự kiện:</label>
                        <input type="text" id="event_name" name="event_name" required>

                        <label for="description">Mô tả:</label>
                        <textarea id="description" name="description" required></textarea>

                        <label for="location">Địa điểm hỗ trợ:</label>
                        <input type="text" id="location" name="location" required>

                        <label for="goal">Mục tiêu quyên góp:</label>
                        <input type="number" id="goal" name="goal" required>
                    </div>

                    <!-- Thông tin Người phụ trách -->
                    <div class="form-section">
                        <h2>Thông tin Người phụ trách</h2>
                        <label for="organizer_name">Họ tên:</label>
                        <input type="text" id="organizer_name" name="organizer_name" required>

                        <label for="phone">Số điện thoại:</label>
                        <input type="tel" id="phone" name="phone" required>

                        <label for="address">Địa chỉ:</label>
                        <input type="text" id="address" name="address" required>

                        <label for="bank_account">Số tài khoản:</label>
                        <input type="text" id="bank_account" name="bank_account" required>

                        <label for="bank_name">Ngân hàng:</label>
                        <input type="text" id="bank_name" name="bank_name" required>
                    </div>
                </div>
                <button type="submit">Tạo Sự kiện</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const create_eventModal = document.getElementById("create_eventModal");
            const createEventButton = document.getElementById("createEventButton");
            const closeButton = create_eventModal.querySelector(".close");

            createEventButton.addEventListener("click", function (event) {
                event.preventDefault(); 
                create_eventModal.style.display = "block";
            });

            closeButton.addEventListener("click", function () {
                create_eventModal.style.display = "none";
            });

            window.addEventListener("click", function (event) {
                if (event.target === create_eventModal) {
                    create_eventModal.style.display = "none";
                }
            });
        });
    </script>

</body>
</html>
