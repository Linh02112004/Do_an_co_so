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

// Truy vấn danh sách thông báo chưa đọc trước, sau đó là các thông báo đã đọc (giới hạn 5 thông báo)
$sql_notification = "SELECT id, message, seen, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY seen ASC, created_at DESC 
        LIMIT 5";

// Truy vấn danh sách thông báo chưa đọc trước, sau đó là các thông báo đã đọc (giới hạn 5 thông báo)
$sql_notification = "SELECT id, message, seen, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY seen ASC, created_at DESC 
        LIMIT 5";

$stmt_notification = $conn->prepare($sql_notification);
$stmt_notification->bind_param("s", $user_id);
$stmt_notification->execute();
$result_notification = $stmt_notification->get_result();
$notifications = $result_notification->fetch_all(MYSQLI_ASSOC);
$notifications = $result_notification->fetch_all(MYSQLI_ASSOC);
$stmt_notification->close();

// Đánh dấu tất cả thông báo chưa đọc là đã đọc khi dropdown được mở
if (isset($_POST['mark_seen'])) {
    $sql_update = "UPDATE notifications SET seen = 1 WHERE user_id = ? AND seen = 0";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("s", $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        echo 'success'; // Trả về tín hiệu thành công
    } else {
        echo 'error'; // Trả về lỗi nếu không thể thực hiện cập nhật
    }
    exit(); // Dừng việc thực thi thêm các mã PHP khi xử lý AJAX
}

// Cập nhật trạng thái sự kiện khi đủ số tiền
$conn->query("UPDATE events e
              LEFT JOIN (SELECT event_id, SUM(amount) AS total_donations FROM donations GROUP BY event_id) d
              ON e.id = d.event_id
              SET e.status = 'completed'
              WHERE d.total_donations >= e.goal");

// Lấy danh sách sự kiện của tổ chức đang đăng nhập
$sql = "SELECT e.id, e.event_name AS name, e.description, e.status, 
               u.organization_name AS organization, e.organizer_name, e.location, e.goal, 
               COALESCE(SUM(d.amount), 0) AS amount_raised
        FROM events e
        LEFT JOIN donations d ON e.id = d.event_id
        JOIN users u ON e.user_id = u.id
        WHERE e.user_id = ?
        GROUP BY e.id, u.organization_name, e.event_name, e.description, e.status, e.organizer_name, e.location, e.goal";
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
    <title>🌱 HY VỌNG - Tổ chức</title>
    <title>🌱 HY VỌNG - Tổ chức</title>
    <link rel="stylesheet" href="style/organization.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="organization.php">🌱 HY VỌNG</a></h1>
        <h1><a id="homeLink" href="organization.php">🌱 HY VỌNG</a></h1>
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
                    <div id="notifications-container">
                        <a id="notifications" href="#">Thông báo 
                            <?php
                            $unread_count = array_reduce($notifications, function ($count, $notif) {
                                return $notif['seen'] == 0 ? $count + 1 : $count;
                            }, 0);
                            ?>
                            <span id="notif-badge" <?php if ($unread_count == 0) echo 'style="display:none;"'; ?>>
                                <?php echo $unread_count; ?>
                            </span>
                        </a>
                        <div id="notificationDropdown" class="notification-dropdown" style="display:none;">
                            <ul id="notificationList">
                                <?php if (empty($notifications)) : ?>
                                    <li><p>Không có thông báo nào.</p></li>
                                <?php else : ?>
                                    <?php foreach ($notifications as $notif) : ?>
                                        <li class="<?php echo $notif['seen'] ? '' : 'unread'; ?>">
                                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small><?php echo date("d/m/Y H:i", strtotime($notif['created_at'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                    <div id="notifications-container">
                        <a id="notifications" href="#">Thông báo 
                            <?php
                            $unread_count = array_reduce($notifications, function ($count, $notif) {
                                return $notif['seen'] == 0 ? $count + 1 : $count;
                            }, 0);
                            ?>
                            <span id="notif-badge" <?php if ($unread_count == 0) echo 'style="display:none;"'; ?>>
                                <?php echo $unread_count; ?>
                            </span>
                        </a>
                        <div id="notificationDropdown" class="notification-dropdown" style="display:none;">
                            <ul id="notificationList">
                                <?php if (empty($notifications)) : ?>
                                    <li><p>Không có thông báo nào.</p></li>
                                <?php else : ?>
                                    <?php foreach ($notifications as $notif) : ?>
                                        <li class="<?php echo $notif['seen'] ? '' : 'unread'; ?>">
                                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small><?php echo date("d/m/Y H:i", strtotime($notif['created_at'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    </div>
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
                        <button onclick="window.location.href='or_eventDetails.php?id=<?php echo $event['id']; ?>'">Xem</button>
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
                        <button onclick="window.location.href='or_eventDetails.php?id=<?php echo $event['id']; ?>'">Xem</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <h1>🌱 HY VỌNG</h1>
            <h1>🌱 HY VỌNG</h1>
            <ul class="footer-links">
                <li><a href="#">Điều khoản & Điều kiện</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Chính sách Cookie</a></li>
            </ul>
            <p class="footer-copyright">Copyright © 2025 Hope.</p>
            <p class="footer-copyright">Copyright © 2025 Hope.</p>
        </div>
    </footer>

    <!-- Pop-up Tạo sự kiện -->
    <div id="create_eventModal" class="modal" style="display: none;">
        <div class="modal-content">
        <span class="close" onclick="closeModal('create_eventModal')">&times;</span>
        <span class="close" onclick="closeModal('create_eventModal')">&times;</span>
            <h1>Tạo sự kiện</h1>
            <form action="or_saveEvents.php" method="POST">
                <div class="form-container">
                    <!-- Thông tin Sự kiện -->
                    <div class="form-section">
                        <h2>Thông tin Sự kiện</h2>
                        <label for="event_name">Tên sự kiện:</label>
                        <input type="text" id="event_name" name="event_name" required>

                        <label for="location">Địa điểm hỗ trợ:</label>
                        <input type="text" id="location" name="location" required>

                        <label for="goal">Mục tiêu quyên góp:</label>
                        <input type="number" id="goal" name="goal" required>
                        
                        <label for="description">Mô tả:</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <!-- Thông tin Người phụ trách -->
                    <div class="form-section">
                        <h2>Thông tin Người phụ trách</h2>
                        <label for="organizer_name">Họ tên:</label>
                        <input type="text" id="organizer_name" name="organizer_name" required>

                        <label for="phone">Số điện thoại:</label>
                        <input type="tel" id="phone" name="phone" required>

                        <label for="bank_account">Số tài khoản:</label>
                        <input type="text" id="bank_account" name="bank_account" required>

                        <label for="bank_name">Ngân hàng thụ hưởng:</label>
                        <select id="bank_name" name="bank_name" required>
                            <?php 
                            $bank_codes = [
                                "BIDV" => "BIDV",
                                "Vietcombank" => "VCB",
                                "Techcombank" => "TCB",
                                "Agribank" => "VBA",
                                "ACB" => "ACB",
                                "MB Bank" => "MB",
                                "VPBank" => "VPB"
                            ];
                            $selected_bank = $user['bank_name'] ?? '';
                            foreach ($bank_codes as $name => $code): 
                            ?>
                                <option value="<?= htmlspecialchars($name) ?>" <?= ($selected_bank == $name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit">Tạo Sự kiện</button>
            </form>
        </div>
    </div>

    <script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Xử lý modal tạo sự kiện
            // Xử lý modal tạo sự kiện
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

            // Xử lý thông báo
            const notificationsBtn = document.getElementById("notifications");
            const notificationDropdown = document.getElementById("notificationDropdown");
            const notifBadge = document.getElementById("notif-badge");

            notificationsBtn.addEventListener("click", function (event) {
                event.preventDefault();
                console.log("Notifications button clicked");

                // Thay đổi trạng thái của dropdown
                if (notificationDropdown.style.display === "none" || notificationDropdown.style.display === "") {
                    notificationDropdown.style.display = "block"; // Hiển thị dropdown
                } else {
                    notificationDropdown.style.display = "none"; // Ẩn dropdown
                }

                // Gửi yêu cầu AJAX để đánh dấu thông báo là đã đọc
                if (notifBadge && notifBadge.style.display !== "none") {
                    console.log("Sending AJAX to mark notifications as seen");

                    // Gửi yêu cầu AJAX tới chính file organization.php
                    fetch("organization.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "mark_seen=1" // Gửi yêu cầu để đánh dấu tất cả thông báo là đã đọc
                    }).then(response => response.text())
                    .then(responseText => {
                        if (responseText === 'success') {
                            notifBadge.style.display = "none"; // Ẩn badge khi tất cả thông báo đã được đánh dấu là đã đọc
                            document.querySelectorAll(".notification-dropdown ul li.unread").forEach(li => {
                                li.classList.remove("unread"); // Xóa class "unread" từ các thông báo
                            });
                        }
                    });
                }
            });

            // Đóng dropdown khi click ra ngoài
            document.addEventListener("click", function (event) {
                if (!notificationsBtn.contains(event.target) && !notificationDropdown.contains(event.target)) {
                    notificationDropdown.style.display = "none"; // Ẩn dropdown khi click ngoài
                }
            });

            const searchBox = document.getElementById('searchBox');
            const searchButton = document.getElementById('searchButton');

            searchButton.addEventListener('click', filterEvents);
            searchBox.addEventListener('input', resetSearch); // Tự động hiển thị toàn bộ khi xóa nội dung tìm kiếm

            function filterEvents() {
                const searchValue = searchBox.value.toLowerCase();
                document.querySelectorAll('.event-card').forEach(card => {
                    if (card.getAttribute('data-title').includes(searchValue)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            function resetSearch() {
                if (searchBox.value.trim() === '') {
                    document.querySelectorAll('.event-card').forEach(card => {
                        card.style.display = 'block';
                    });
                }
            }
        });
    </script>

</body>
</html>
