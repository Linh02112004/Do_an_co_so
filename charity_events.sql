CREATE DATABASE charity_events;

USE charity_events;

CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    account_number VARCHAR(50) NOT NULL
);

CREATE TABLE events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL,
    raised_amount DECIMAL(10, 2) DEFAULT 0,
    created_by INT(11) NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);