-- Tạo cơ sở dữ liệu
CREATE DATABASE charity_events;

-- Sử dụng cơ sở dữ liệu
USE charity_events;

-- Tạo bảng users
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    cccd VARCHAR(12) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    bank_account VARCHAR(50),
    beneficiary_bank VARCHAR(100)
);

-- Tạo bảng events
CREATE TABLE events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL,
    raised_amount DECIMAL(10, 2) DEFAULT 0,
    created_by INT(11) NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);