-- Create database
CREATE DATABASE IF NOT EXISTS office_supplies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE office_supplies;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table
CREATE TABLE items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    description TEXT,
    stock_qty INT NOT NULL DEFAULT 0,
    min_qty INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Requisitions table
CREATE TABLE requisitions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Requisition items table
CREATE TABLE requisition_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    requisition_id INT,
    item_id INT,
    qty INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisitions(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Insert sample data
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Regular User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO categories (name) VALUES
('เครื่องเขียน'),
('กระดาษ'),
('อุปกรณ์สำนักงาน');

INSERT INTO items (name, category_id, description, stock_qty, min_qty) VALUES
('ปากกาลูกลื่น', 1, 'ปากกาลูกลื่นสีน้ำเงิน', 100, 20),
('ดินสอ 2B', 1, 'ดินสอดำ 2B', 50, 10),
('กระดาษ A4', 2, 'กระดาษ A4 80 แกรม', 500, 100),
('แฟ้มเอกสาร', 3, 'แฟ้มเอกสารสีน้ำเงิน', 30, 5); 