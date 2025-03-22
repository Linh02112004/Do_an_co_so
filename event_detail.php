<?php
include 'db.php';

$event_id = intval($_GET['id']);

$sql = "SELECT events.title, events.description, 
               users.full_name, users.phone, users.email, users.address, 
               users.bank_account, users.beneficiary_bank 
        FROM events 
        JOIN users ON events.created_by = users.id 
        WHERE events.id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Không tìm thấy sự kiện!";
    exit;
}

$event = $result->fetch_assoc();

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

$bank_name = $event['beneficiary_bank'];
$bank_code = isset($bank_codes[$bank_name]) ? $bank_codes[$bank_name] : null;

if (!$bank_code) {
    echo "<script>alert('Ngân hàng không hỗ trợ VietQR!');</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Sự kiện</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            width: 60%;
            margin: auto;
            padding: 20px;
        }
        .section {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }
        .btn-cancel {
            background-color: #dc3545;
        }
        .btn-cancel:hover {
            background-color: #c82333;
        }
        .btn-donate {
            background-color: #28a745;
        }
        .btn-donate:hover {
            background-color: #218838;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 500px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        .close:hover {
            color: black;
        }
        .modal input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }
        .qr-container {
    display: flex;
    justify-content: center; /* Căn giữa theo chiều ngang */
    align-items: center; /* Căn giữa theo chiều dọc */
    margin-top: 15px;
    width: 100%; /* Đảm bảo container rộng bằng modal */
}

#qrcode {
    display: none;
    width: 180px;  /* Kích thước phù hợp với card */
    height: 180px;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 5px;
    background: #fff;
}

</style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>

        <div class="section">
            <h3>Thông tin người tạo sự kiện</h3>
            <p><strong>Tên:</strong> <?php echo htmlspecialchars($event['full_name']); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($event['phone']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($event['email']); ?></p>
            <p><strong>Ngân hàng:</strong> <?php echo htmlspecialchars($event['beneficiary_bank']); ?></p>
            <p><strong>Số tài khoản:</strong> <?php echo htmlspecialchars($event['bank_account']); ?></p>
        </div>

        <div class="button-container">
            <a href="index.html" class="btn btn-cancel">Hủy</a>
            <button class="btn btn-donate" onclick="showModal()">Quyên góp</button>
        </div>
    </div>

    <!-- Modal -->
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

    <script>
        function confirmDonation() {
    let amount = document.getElementById('donationAmount').value;
    if (amount === "" || amount < 1000) {
        alert("Vui lòng nhập số tiền hợp lệ (tối thiểu 1.000 VNĐ)!");
        return;
    }

    let eventId = "<?php echo $event_id; ?>";

    fetch('update_donation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `event_id=${eventId}&amount=${amount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Quyên góp thành công! Số tiền đã cập nhật.");
            // Clear the input field
            document.getElementById('donationAmount').value = '';
            // Hide the confirm button
            document.getElementById('confirmBtn').style.display = 'none';
            // Hide the QR code
            document.getElementById('qrcode').style.display = 'none';
            // Redirect to index page
            window.location.href = 'index.html';
        } else {
            alert("Lỗi: " + data.message);
        }
    })
    .catch(error => console.error('Lỗi khi gửi dữ liệu:', error));
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

</script>
</body>
</html>
