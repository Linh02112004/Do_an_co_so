-- Tạo cơ sở dữ liệu nếu chưa có
CREATE DATABASE IF NOT EXISTS charity_management;
USE charity_management;

-- Tạo bảng users để lưu thông tin tổ chức
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Mật khẩu sẽ được mã hóa
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng events để lưu thông tin sự kiện
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    goal DECIMAL(15,2) NOT NULL,
    organizer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    bank_account VARCHAR(50) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    status ENUM('ongoing', 'completed') DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL, -- Liên kết với users
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tạo bảng donations để lưu thông tin quyên góp
CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL, -- Liên kết với events
    donor_name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL CHECK (amount > 0), -- Số tiền quyên góp phải > 0
    donated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE event_edits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,  -- Ai sửa?
    event_name VARCHAR(255),
    description TEXT,
    organizer_name VARCHAR(255),
    goal DECIMAL(15,2),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,  -- Lý do từ chối (nếu có)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE notifications ADD COLUMN seen TINYINT(1) DEFAULT 0;

-- Tạo một tài khoản tổ chức mẫu (Mật khẩu là "password" nhưng cần mã hóa khi dùng thực tế)
-- INSERT INTO users (name, email, password) 
-- VALUES ('Tổ chức ABC', 'abc@example.com', 'password123');

-- Tạo một sự kiện mẫu cho tổ chức có id=1
-- INSERT INTO events (event_name, description, location, goal, organizer_name, phone, address, bank_account, bank_name, user_id)
-- VALUES 
-- ('Quyên góp sách cho trẻ em', 'Một chiến dịch quyên góp sách để giúp trẻ em vùng cao.', 'Hà Nội', 50000000, 'Nguyễn Văn A', '0987654321', 'Hà Nội', '123456789', 'Vietcombank', 1);

-- INSERT INTO donations (event_id, donor_name, amount) 
-- VALUES 
-- (1, 'Trần Thị B', 10000000), -- 10 triệu
-- (1, 'Lê Văn C', 5000000);    -- 5 triệu