<?php
require 'db_connect.php';

session_start();
if (!isset($_SESSION["user_id"])) {
    $logged_in = false;
} else {
    $logged_in = true;
    $user_name = $_SESSION["user_name"]; // Lấy tên từ session
    $user_id = $_SESSION["user_id"]; // Lấy ID từ session
}


if (!isset($_GET["id"])) {
    die("Sự kiện không tồn tại.");
}

$event_id = intval($_GET["id"]);
$sql = "SELECT e.*, u.name AS organizer, 
               (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE event_id = e.id) AS total_donated,
               (SELECT COUNT(*) FROM donations WHERE event_id = e.id) AS donation_count,
               e.bank_account, e.bank_name
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
$sql_donations = "SELECT donor_name, amount, donated_at FROM donations WHERE event_id = ? ORDER BY donated_at DESC";
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
$sql_comments = "SELECT c.*, u.name AS user_name FROM comments c 
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
    <title>Chi tiết Sự kiện</title>
    <link rel="stylesheet" href="ntg_styles.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($event["event_name"]); ?></h1>
        <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($event["description"])); ?></p>
        <p><strong>Tổ chức chịu trách nhiệm:</strong> <?php echo htmlspecialchars($event["organizer"]); ?></p>
        <p><strong>Tên người phụ trách:</strong> <?php echo htmlspecialchars($event["organizer_name"]); ?></p>
        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($event["phone"]); ?></p>
        <p><strong>Địa chỉ người phụ trách:</strong> <?php echo htmlspecialchars($event["address"]); ?></p>
        <p><strong>Địa chỉ hỗ trợ:</strong> <?php echo htmlspecialchars($event["location"]); ?></p>
        <p><strong>Mục tiêu quyên góp:</strong> <?php echo number_format($event["goal"])." VND"; ?></p>
        <p><strong>Số tiền đã quyên góp:</strong> <?php echo number_format($event["total_donated"])." VND"; ?></p>
        
        <!-- Nút danh sách quyên góp -->
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

        <button class="btn btn-donate" onclick="showModal()">Quyên góp</button>
        <!-- Card nhập số tiền -->
        <div id="donationModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Nhập số tiền muốn quyên góp</h3>
                <input type="number" id="donationAmount" placeholder="Nhập số tiền (VNĐ)" min="1000">
                <button class="btn btn-donate" onclick="generateVietQR()">Tạo QR</button>
                <button class="btn btn-cancel" onclick="closeModal()">Hủy</button>
                <br>
                <div class="qr-container">
                    <img id="qrcode" />
                </div>
                <!-- Moved the confirm button inside the modal -->
                <button id="confirmBtn" class="btn btn-donate" onclick="confirmDonation()" style="display: none;">Xác nhận</button>
            </div>
        </div>

        <!-- Danh sách bình luận -->
        <div id="commentSection">
    <?php if (!empty($comments)): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?php echo htmlspecialchars($comment["user_name"]); ?>:</strong> 
                    <?php echo htmlspecialchars($comment["comment"]); ?>
                    <br><small><?php echo $comment["created_at"]; ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Chưa có bình luận nào.</p>
    <?php endif; ?>
</div>


<div>
    <textarea id="commentText" placeholder="Nhập bình luận của bạn..." rows="3" style="width: 100%;"></textarea>
    <button onclick="submitComment()">Gửi bình luận</button>
</div>



        <!-- Nút quay lại -->
        <button onclick="window.location.href='ntg_index.php'">Quay lại</button>
    </div>

    <script>
        function confirmDonation() {
    let eventId = "<?php echo $event_id; ?>";

    fetch('update_donation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `event_id=${eventId}&amount=${amount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Quyên góp thành công! Cảm ơn bạn!");
            // Hide the confirm button
            document.getElementById('confirmBtn').style.display = 'none';
            // Hide the QR code
            document.getElementById('qrcode').style.display = 'none';
            // Redirect to index page
            window.location.href = 'ntg_index.php';
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(error => console.error('Lỗi khi gửi dữ liệu:', error));
}

        function toggleDonations() {
            var list = document.getElementById("donationList");
            if (list.style.display === "none") {
                list.style.display = "block";
            } else {
                list.style.display = "none";
            }
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
            alert("Bình luận đã được thêm!");
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
