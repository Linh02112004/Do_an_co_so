<?php
require 'db_connect.php';

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);
$sql = "SELECT e.*, 
               u.organization_name AS organizer, 
               u.full_name AS organizer_full_name,
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

// Lấy danh sách bình luận
$sql_comments = "SELECT c.*, 
                CASE 
                    WHEN u.role = 'organization' THEN u.organization_name 
                    ELSE u.full_name 
                END AS commenter_name 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.event_id = ? 
                ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql_comments);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact VN - <?php echo htmlspecialchars($event["event_name"]); ?></title>
    <link rel="stylesheet" href="style/admin.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="admin.php">IMPACT VN</a></h1>
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
        <div class="container">
        <h1><?php echo htmlspecialchars($event["event_name"]); ?></h1>
            <div class="content-wrapper">
                <!-- Container Left (70%) -->
                <div class="container-left">
                    <hr>
                    <h3>Thông tin chi tiết</h3>
                    <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($event["description"])); ?></p>
                    <p><strong>Tổ chức:</strong> <?php echo htmlspecialchars($event["organizer"]); ?></p>
                    <p><strong>Tên người phụ trách:</strong> <?php echo htmlspecialchars($event["organizer_name"]); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($event["phone"]); ?></p>
                    <p><strong>Địa điểm sự kiện:</strong> <?php echo htmlspecialchars($event["location"]); ?></p>
                    <p><strong>Mục tiêu quyên góp:</strong> <?php echo number_format($event["goal"])." VND"; ?></p>
                    <p><strong>Số tiền đã quyên góp:</strong> <?php echo number_format($event["total_donated"])." VND"; ?></p>
                    <?php if ($event["donation_count"] == 0): ?>
                        <button onclick="window.location.href='ad_deleteEvents.php?id=<?php echo $event_id; ?>'">Xóa sự kiện</button>
                    <?php endif; ?>
                    <button onclick="openModal()">So sánh thay đổi</button>

                    <hr>
                    <h3>Bình luận</h3>
                    <div class="comment-box">
                        <textarea id="commentText" placeholder="Nhập bình luận của bạn..." rows="2"></textarea>
                        <button onclick="submitComment()">🗨️</button>
                    </div>

                    <!-- Danh sách bình luận -->
                    <div id="commentSection">
                        <?php if (!empty($comments)): ?>
                            <ul>
                                <?php foreach ($comments as $comment): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($comment["commenter_name"]); ?>:</strong> 
                                        <?php echo htmlspecialchars($comment["comment"]); ?>
                                        <br><small><?php echo $comment["created_at"]; ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Chưa có bình luận nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Container Right (30%) -->
                <div class="container-right">
                    <!-- Right Top -->
                     <hr>
                    <div class="right-top">
                        <h2><?php echo number_format($event["total_donated"]); ?> VND</h2>
                        <p>trong tổng số tiền là <?php echo number_format($event["goal"]); ?> VND</p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo ($event["total_donated"] / $event["goal"]) * 100; ?>%;">
                                <?php echo round(($event["total_donated"] / $event["goal"]) * 100); ?>%
                            </div>
                        </div>
                        <p><?php echo count($donations); ?> người đã quyên góp</p>
                    </div>

                    <!-- Right Bottom -->
                    <div class="right-bottom">
                        <h3>Danh sách quyên góp</h3>
                        <table border="1">
                            <tr>
                                <th>STT</th>
                                <th>Họ và Tên</th>
                                <th>Số tiền</th>
                                <th>Thời gian</th>
                            </tr>
                            <?php $stt = 1; ?>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo htmlspecialchars($donation["donor_name"]); ?></td>
                                    <td><?php echo number_format($donation["amount"])." VND"; ?></td>
                                    <td><?php echo $donation["donated_at"]; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
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

    <!-- Pop-up So sánh sự thay đổi của sự kiện yêu cầu chỉnh sửa -->
    <div id="compareModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal(compareModal)">&times;</span>
            <h2>So sánh thay đổi</h2>
            <table border="1">
                <tr>
                    <th>Trường</th>
                    <th>Dữ liệu cũ</th>
                    <th>Dữ liệu mới</th>
                </tr>
                <tr>
                    <td>Tên sự kiện</td>
                    <td><?php echo htmlspecialchars($original_event['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($edited_event['event_name']); ?></td>
                </tr>
                <tr>
                    <td>Mô tả</td>
                    <td><?php echo nl2br(htmlspecialchars($original_event['description'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($edited_event['description'])); ?></td>
                </tr>
                <tr>
                    <td>Người phụ trách</td>
                    <td><?php echo htmlspecialchars($original_event['organizer_name']); ?></td>
                    <td><?php echo htmlspecialchars($edited_event['organizer_name']); ?></td>
                </tr>
                <tr>
                    <td>Mục tiêu</td>
                    <td><?php echo number_format($original_event['goal']); ?> VND</td>
                    <td><?php echo number_format($edited_event['goal']); ?> VND</td>
                </tr>
            </table>

            <form method="post" action="ad_process_edit.php">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="edit_id" value="<?php echo $edited_event['id']; ?>">
                <button type="submit" name="action" value="approve">Chấp nhận</button>
                <button type="button" onclick="document.getElementById('reject-reason').style.display='block'">Từ chối</button>
                <div id="reject-reason" style="display:none;">
                    <textarea name="reason" placeholder="Nhập lý do từ chối"></textarea>
                    <button type="submit" name="action" value="reject">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal() {
            document.getElementById('compareModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('compareModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function submitComment() {
            let commentText = document.getElementById("commentText").value.trim();
            if (commentText === "") {
                alert("Vui lòng nhập bình luận.");
                return;
            }

            let eventId = "<?php echo $event_id; ?>";

            fetch('add_comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `event_id=${eventId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Lỗi: " + data.message);
                }
            })
            .catch(error => console.error('Lỗi khi gửi bình luận:', error));
        }
    </script>
</body>
</html>
