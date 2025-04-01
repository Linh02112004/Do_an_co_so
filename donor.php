<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = ?";
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
                    <a id="updateInfoLink" href="#">Cập nhật thông tin</a>
                    <a id="changePasswordLink" href="#">Thay đổi mật khẩu</a>
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

    <!-- Pop-up Cập nhật thông tin -->
    <div id="updateInfoModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('updateInfoModal')">&times;</span>
            <h1>Cập nhật thông tin</h1>
            <form action="updateInfor.php" method="POST">
                <div class="form-container">
                    <!-- Thông tin Người quyên góp -->
                    <div class="form-section">
                        <h2>Thông tin cá nhân</h2>
                        <input type="hidden" name="role" value="donor">
                        <label for="fullname">Họ và Tên:</label>
                        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['full_name']); ?>" required>

                        <label for="phone">Số điện thoại:</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

                        <label for="social_media">Mạng xã hội:</label>
                        <input type="url" id="social_media" name="social_media" value="<?= htmlspecialchars($user['social_media']); ?>">
                    </div>
                </div>
                <button type="submit">Cập nhật thông tin</button>
            </form>
        </div>
    </div>

    <!-- Pop-up Thay đổi mật khẩu -->
    <div id="changePasswordModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('changePasswordModal')">&times;</span>
            <h1>Thay đổi mật khẩu</h1>
            <form action="changePassword.php" method="POST">
                <div class="form-container">
                    <!-- Mật khẩu -->
                    <div class="form-section">
                        <label for="current_password">Mật khẩu hiện tại:</label>
                        <input type="password" id="current_password" name="current_password" required>

                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" required>

                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit">Thay đổi mật khẩu</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Modal Cập nhật thông tin
            const updateInfoModal = document.getElementById("updateInfoModal");
            const updateInfoLink = document.getElementById("updateInfoLink"); // Liên kết mở modal
            const closeUpdateInfo = updateInfoModal.querySelector(".close"); // Nút đóng modal

            updateInfoLink.addEventListener("click", function (event) {
                event.preventDefault();
                updateInfoModal.style.display = "block"; // Hiển thị modal
            });

            closeUpdateInfo.addEventListener("click", function () {
                updateInfoModal.style.display = "none"; // Đóng modal
            });

            window.addEventListener("click", function (event) {
                if (event.target === updateInfoModal) {
                    updateInfoModal.style.display = "none"; // Đóng modal khi click ra ngoài
                }
            });

            // Modal Thay đổi mật khẩu
            const changePasswordModal = document.getElementById("changePasswordModal");
            const changePasswordLink = document.getElementById("changePasswordLink"); // Liên kết mở modal
            const closeChangePassword = changePasswordModal.querySelector(".close"); // Nút đóng modal

            changePasswordLink.addEventListener("click", function (event) {
                event.preventDefault();
                changePasswordModal.style.display = "block"; // Hiển thị modal
            });

            closeChangePassword.addEventListener("click", function () {
                changePasswordModal.style.display = "none"; // Đóng modal
            });

            window.addEventListener("click", function (event) {
                if (event.target === changePasswordModal) {
                    changePasswordModal.style.display = "none"; // Đóng modal khi click ra ngoài
                }
            });
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
        });
    </script>
</body>
</html>
