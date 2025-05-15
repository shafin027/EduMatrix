CREATE DATABASE IF NOT EXISTS edumatrix;
USE edumatrix;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'finished') DEFAULT 'enrolled',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (program_id) REFERENCES programs(id)
);
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    image VARCHAR(255)
);
ALTER TABLE programs ADD COLUMN category_id INT;
ALTER TABLE programs ADD FOREIGN KEY (category_id) REFERENCES categories(id);

CREATE TABLE coupons (
    code VARCHAR(50) PRIMARY KEY,
    discount_percent DECIMAL(5, 2) NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS coupons (
    code VARCHAR(50) PRIMARY KEY,
    discount_percent DECIMAL(5, 2) NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clean up image paths (remove '../Uploads/' prefix if present)
UPDATE programs
SET image = REPLACE(image, '../Uploads/', 'Uploads/')
WHERE image LIKE '../Uploads/%';
-- Insert users with plain text passwords
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@edumatrix.com', 'admin'),
('user1', 'user123', 'user1@edumatrix.com', 'user');

INSERT INTO programs (class, category, price, discount_price, image, description) VALUES
('Class 6', 'School Program - SSC 30', 18000, 15000, 'uploads/class_6.jpeg', 'A comprehensive program for Class 6 students preparing for SSC.'),
('Class 7', 'School Program - SSC 29', 18000, 14000, 'uploads/class_7.jpeg', 'An advanced curriculum for Class 7 students aiming for SSC success.'),
('Class 8', 'School Program - SSC 28', 18000, 13000, 'uploads/class_8.jpeg', 'Tailored courses for Class 8 students to excel in SSC exams.'),
('SSC 27 Science', 'Academic Program', 18000, 12000, 'uploads/ssc_27_science.jpeg', 'In-depth science courses for SSC 27 students.'),
('SSC 27 Humanities', 'Academic Program', 18000, 11000, 'uploads/ssc_27_humanities.jpeg', 'Humanities-focused program for SSC 27 learners.'),
('SSC 27 Business Studies', 'Academic Program', 18000, 10000, 'uploads/ssc_27_business_studies.jpeg', 'Business studies curriculum for SSC 27 students.');

INSERT INTO coupons (code, discount_percent, expiry_date) VALUES
('EDUMATRIX100', 100.00, '2025-12-31');