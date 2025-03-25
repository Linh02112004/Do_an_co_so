CREATE DATABASE IF NOT EXISTS charity_management;
USE charity_management;

-- Bảng lưu số lượng bản ghi theo từng loại vai trò
CREATE TABLE IF NOT EXISTS id_counters (
    role ENUM('admin', 'organization', 'donor') PRIMARY KEY,
    count INT NOT NULL DEFAULT 0
);

-- Khởi tạo bộ đếm cho từng vai trò
INSERT INTO id_counters (role, count) VALUES ('admin', 0), ('organization', 0), ('donor', 0)
ON DUPLICATE KEY UPDATE count = count;

-- Bảng users với ID tự động tạo (VARCHAR(10) thay vì INT)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(10) PRIMARY KEY,
    full_name VARCHAR(255),
    organization_name VARCHAR(255),
    description TEXT,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    website VARCHAR(255),
    social_media VARCHAR(255),
    role ENUM('admin', 'organization', 'donor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DELIMITER //
CREATE TRIGGER before_insert_users1
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.role = 'organization' AND (NEW.organization_name IS NULL OR NEW.description IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Organization must have organization_name and description';
    END IF;
    
    IF NEW.role = 'donor' AND NEW.full_name IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor must have full_name';
    END IF;
    
    IF NEW.role = 'admin' THEN
        SET NEW.full_name = NULL;
        SET NEW.organization_name = NULL;
        SET NEW.description = NULL;
        SET NEW.address = NULL;
        SET NEW.website = NULL;
        SET NEW.social_media = NULL;
    END IF;
END //
DELIMITER ;

-- Trigger để tự động sinh ID
DELIMITER $$
CREATE TRIGGER before_insert_users
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    DECLARE new_count INT;
    DECLARE role_prefix VARCHAR(2);
    
    -- Xác định tiền tố ID theo vai trò
    IF NEW.role = 'admin' THEN
        SET role_prefix = 'AD';
    ELSEIF NEW.role = 'organization' THEN
        SET role_prefix = 'OR';
    ELSEIF NEW.role = 'donor' THEN
        SET role_prefix = 'DN';
    END IF;
    
    -- Tăng số đếm và tạo ID mới
    UPDATE id_counters SET count = count + 1 WHERE role = NEW.role;
    SELECT count INTO new_count FROM id_counters WHERE role = NEW.role;
    
    -- Gán ID cho bản ghi mới
    SET NEW.id = CONCAT(role_prefix, LPAD(new_count, 3, '0'));
END $$
DELIMITER ;

-- Tạo bảng events để lưu thông tin sự kiện
CREATE TABLE IF NOT EXISTS events (
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
    user_id VARCHAR(10) NOT NULL, -- Liên kết với users 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tạo bảng donations để lưu thông tin quyên góp
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL, -- Liên kết với events
    donor_id VARCHAR(10) NOT NULL, -- Liên kết với users 
    amount DECIMAL(15,2) NOT NULL CHECK (amount > 0), -- Số tiền quyên góp phải > 0
    donated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng event_edits để theo dõi chỉnh sửa sự kiện
CREATE TABLE IF NOT EXISTS event_edits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id VARCHAR(10) NOT NULL,  -- Ai sửa?
    event_name VARCHAR(255),
    description TEXT,
    organizer_name VARCHAR(255),
    goal DECIMAL(15,2),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,  -- Lý do từ chối (nếu có)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng notifications để lưu thông báo cho người dùng
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(10) NOT NULL,
    message TEXT NOT NULL,
    seen TINYINT(1) DEFAULT 0, -- 0 = chưa đọc, 1 = đã đọc
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng comments để lưu bình luận của người dùng trong sự kiện
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id VARCHAR(10) NOT NULL,
    parent_id INT DEFAULT NULL, -- Nếu NULL, là bình luận gốc. Nếu có ID, là trả lời.
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);
