<?php
require 'db_connect.php';

session_start();
$full_name = "Người dùng"; // Giá trị mặc định

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    
    $sql_user = "SELECT full_name FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql_user)) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result_user = $stmt->get_result();
        $user = $result_user->fetch_assoc();
        $stmt->close();
        
        if ($user) {
            $full_name = htmlspecialchars($user['full_name']);
        }
    }
}

if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

// Truy vấn thông tin sự kiện và tổng tiền quyên góp
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

// Mảng chứa mã ngân hàng VietQR
$bank_codes = [
    "BIDV" => "BIDV",
    "Vietcombank" => "VCB",
    "Techcombank" => "TCB",
    "Agribank" => "VBA",
    "ACB" => "ACB",
    "MB Bank" => "MB",
    "VPBank" => "VPB"
];

$bank_name = $event['bank_name'];
$bank_code = isset($bank_codes[$bank_name]) ? $bank_codes[$bank_name] : null;

if (!$bank_code) {
    echo "<script>alert('Ngân hàng không hỗ trợ VietQR!');</script>";
}

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
    <link rel="stylesheet" href="style/donor.css">
</head>
<body>
    <header>
        <h1><a id="homeLink" href="donor.php">IMPACT VN</a></h1>
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

            <div id="authLinks" style="margin-left: auto;">
                <div class="auth-buttons">
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
                        <button class="btn btn-donate" onclick="showModal()">Quyên góp</button>
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

            <!-- Card nhập số tiền -->
            <div id="donationModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3>Nhập số tiền muốn quyên góp</h3>
                    <input type="number" id="donationAmount" placeholder="Nhập số tiền (VNĐ)" min="1000">
                    <button class="btn btn-donate" onclick="generateVietQR()">Tạo QR</button>
                    <br>
                    <div class="qr-container">
                        <img id="qrcode" />
                    </div>
                    <!-- Moved the confirm button inside the modal -->
                    <button id="confirmBtn" class="btn btn-donate" onclick="confirmDonation()" style="display: none;">Xác nhận</button>
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

    <script>
        function confirmDonation() {
            const eventId = new URLSearchParams(window.location.search).get('id');
            const amount = document.getElementById('donationAmount').value;

            if (!amount || amount <= 0) {
                alert('Vui lòng nhập số tiền hợp lệ.');
                return;
            }

            fetch('update_donation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `event_id=${eventId}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Quyên góp thành công! Cảm ơn bạn đã đóng góp.');
                    document.getElementById('confirmBtn').style.display = 'none';
                    location.reload();
                } else {
                    alert(`Lỗi: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi, vui lòng thử lại.');
            });
        }

        document.getElementById("donateBtn").addEventListener("click", function() {
        document.getElementById("donationCard").style.display = "block";
        });

        function closeDonationCard() {
            document.getElementById("donationCard").style.display = "none";
        }

        function showModal() {
            document.getElementById('donationModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('donationModal').style.display = 'none';
            document.getElementById('qrcode').style.display = 'none';
        }

        function generateVietQR() {
            let amount = document.getElementById('donationAmount').value;
            if (amount === "" || amount < 1000) {
                alert("Vui lòng nhập số tiền hợp lệ (tối thiểu 1.000 VNĐ)!");
            return;
            }

            let account = "<?php echo $event['bank_account']; ?>";
            let bankCode = "<?php echo $bank_code; ?>";

            if (!bankCode) {
                alert("Ngân hàng không hỗ trợ tạo QR VietQR.");
                return;
            }

            let qrURL = `https://img.vietqr.io/image/${bankCode}-${account}-compact.png?amount=${amount}&addInfo=QuyenGop`;

            document.getElementById('qrcode').src = qrURL;
            document.getElementById('qrcode').style.display = 'block';

            // Hide the confirm button initially
            let confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.style.display = 'none';

            // Show the confirm button after 5 seconds
            setTimeout(() => {
                confirmBtn.style.display = 'inline-block';
            }, 5000);
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
