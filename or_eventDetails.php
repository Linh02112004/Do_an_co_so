<?php
require 'db_connect.php';

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);

// Truy vấn thông tin sự kiện và tổng tiền quyên góp
$sql = "SELECT e.*, 
               u.organization_name AS organizer, 
               u.full_name AS organizer_full_name,
               (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE event_id = e.id) AS total_donated,
               (SELECT COUNT(*) FROM donations WHERE event_id = e.id) AS donation_count
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        WHERE e.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id); // Nếu `event_id` là INT
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Sự kiện không tồn tại.");
}

$stmt->close();

// Lấy danh sách quyên góp
$sql_donations = "SELECT u.full_name AS donor_name, d.amount, d.donated_at 
                  FROM donations d 
                  JOIN users u ON d.donor_id = u.id 
                  WHERE d.event_id = ? 
                  ORDER BY d.donated_at DESC";

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
    <title>Impact VN - <?php echo htmlspecialchars($event["event_name"]); ?></title>
    <link rel="stylesheet" href="style/organization.css">
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
    <header>
        <h1><a id="homeLink" href="organization.php">IMPACT VN</a></h1>
        <div class="header-right">
            <div id="userMenu">
                <span id="userName">Xin chào, Tổ chức <?php echo htmlspecialchars($event['organizer']); ?></span>
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
        <div class="container">
            <h1><?php echo htmlspecialchars($event["event_name"]); ?></h1>
            <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($event["description"])); ?></p>
            <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event["organizer"]); ?></p>
            <p><strong>Tên người phụ trách:</strong> <?php echo htmlspecialchars($event["organizer_name"]); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($event["phone"]); ?></p>
            <p><strong>Địa điểm sự kiện:</strong> <?php echo htmlspecialchars($event["location"]); ?></p>
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
            <button onclick="window.location.href='organization.php'">Quay lại</button>
            <?php if ($event["donation_count"] == 0): ?>
                <button onclick="window.location.href='or_deleteEvents.php?id=<?php echo $event_id; ?>'">Xóa sự kiện</button>
            <?php endif; ?>
            <button onclick="openEditModal()">Yêu cầu Sửa Sự kiện</button>
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

    <!-- Pop-up Yêu cầu chỉnh sửa sự kiện -->
    <div id="editEventModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editEventModal')">&times;</span>
            <h1><small>Yêu cầu sửa sự kiện</small></h1>
            <form action="or_requestEdit.php" method="POST">
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                <label>Tên sự kiện:</label>
                <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
                <label>Mô tả:</label>
                <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                <label>Địa chỉ hỗ trợ:</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                <label>Mục tiêu quyên góp:</label>
                <input type="number" name="goal" value="<?php echo htmlspecialchars($event['goal']); ?>" required>
                <label>Tên người phụ trách:</label>
                <input type="text" name="organizer_name" value="<?php echo htmlspecialchars($event['organizer_name']); ?>" required>
                <label>Số điện thoại:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($event['phone']); ?>" required>
                <button type="submit">Gửi Yêu cầu</button>
            </form>
        </div>
    </div>

    <!-- Script điều khiển pop-up -->
    <script>
    function openEditModal() {
        document.getElementById('editEventModal').style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Đóng pop-up khi click ra ngoài modal
    window.onclick = function(event) {
        let modal = document.getElementById('editEventModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
