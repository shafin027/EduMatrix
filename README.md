# Edu-Matrix
EduMatrix - Educational Program Management Platform

Overview
EduMatrix is a PHP-based web application designed to manage and display educational programs/courses organized by categories. It provides a user-friendly interface for browsing academic programs, with features like dynamic category listings, course details with pricing (including discounts), and responsive design using Tailwind CSS.
Key Features

Category Listings: Displays all available program categories with images (programs.php).
Course Listings: Shows courses within a selected category, including original and discounted prices (category_courses.php).
Responsive Design: Uses Tailwind CSS for a mobile-friendly layout.
Admin Management: Includes an admin interface for managing programs, categories, and coupons (not included in this upload but referenced in the code).
Database Integration: Uses MySQL to store categories, programs, and related data.

Prerequisites

PHP: Version 7.4 or higher.
MySQL: For database management.
Web Server: Apache (e.g., via XAMPP or WAMP).
Tailwind CSS: Included via CDN in header.php (assumed).

Setup Instructions

Clone the Repository:
git clone https://github.com/your-username/edumatrix.git
cd edumatrix


Set Up the Database:

Create a MySQL database named edumatrix.
Import the following schema:CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    image VARCHAR(255),
    description TEXT
);


Add sample data:INSERT INTO categories (category, image) VALUES
('School Program - SSC 30', 'uploads/class_6.jpeg'),
('School Program - SSC 29', 'uploads/class_7.jpeg');

INSERT INTO programs (class, category, price, discount_price, image, description) VALUES
('Math 101', 'School Program - SSC 30', 1000.00, 800.00, 'uploads/math101.jpg', 'Introduction to Algebra'),
('Math 102', 'School Program - SSC 30', 1200.00, NULL, NULL, 'Advanced Algebra');




Configure Database Connection:

Update includes/db_connect.php with your database credentials:<?php
$host = 'localhost';
$db = 'edumatrix';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>




Set Up File Structure:

Place the project files in your web server’s root directory (e.g., /Applications/XAMPP/xamppfiles/htdocs/edumatrix/).
Ensure the uploads/ directory exists and contains placeholder images (e.g., class_6.jpeg).


Start the Server:

Start your web server (e.g., XAMPP).
Access the project at http://localhost/edumatrix/programs.php.



File Structure

programs.php: Displays all categories with links to their courses.
category_courses.php: Shows courses for a specific category with pricing and discounts.
includes/
header.php: Contains the HTML head and navigation (assumed to include Tailwind CSS).
db_connect.php: Database connection configuration.
footer.php: HTML footer.


uploads/: Directory for storing images (e.g., course and category images).

Usage

Browse Categories:

Navigate to http://localhost/edumatrix/programs.php to view all program categories.
Each category card links to category_courses.php?category=[category_name].


View Courses:

On category_courses.php, courses are displayed with their original price (strikethrough if discounted) and discounted price (if applicable) below it.
Example: "মূল্য ৳1000 টাকা" followed by "৳800 টাকা" in green.
Click a course to view details .


Fork the repository.
Create a new branch (git checkout -b feature/your-feature).
Make your changes and commit (git commit -m "Add your feature").
Push to your branch (git push origin feature/your-feature).
Open a pull request.

Notes

Missing Files: Files like course_details.php, header.php, and footer.php are referenced but not included in this upload. Ensure they exist in your project.
Styling: Assumes Tailwind CSS is included in header.php. If not, add it:<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">


Security: Use prepared statements (already implemented) to prevent SQL injection. Consider adding input validation for production use.
Future Improvements:
Add pagination for large category or course lists.
Implement user authentication for restricted access.



License
This project is licensed under the MIT License - see the LICENSE file for details.
Contact
For questions or suggestions, feel free to open an issue or contact the repository owner.
