<?php
include 'db.php';

$sql = "SELECT title, description, image_path FROM events";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Sự Kiện</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .event-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; padding: 20px; }
        .event { width: 300px; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; box-shadow: 2px 2px 10px rgba(0,0,0,0.1); }
        .event img { width: 100%; height: 200px; object-fit: cover; }
        .event-content { padding: 10px; }
        .event-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .event-description { font-size: 14px; color: #666; }
    </style>
</head>
<body>

<h2>Danh sách Sự Kiện</h2>

<div class="event-container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="event">
            <?php
            if (!empty($row['image_path'])) {
                $imageData = base64_encode($row['image_path']); // Giải mã ảnh BLOB
                $imageSrc = "data:image/jpeg;base64,{$imageData}";
                echo "<img src='{$imageSrc}' alt='" . htmlspecialchars($row['title']) . "'>";
            } else {
                echo "<img src='default.jpg' alt='Không có ảnh'>";
            }
            ?>
            <div class="event-content">
                <div class="event-title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="event-description"><?= htmlspecialchars($row['description']) ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
