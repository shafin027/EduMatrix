<?php
session_start();
include 'db_connect.php';

$categories_sql = "SELECT DISTINCT category FROM programs";
$categories_result = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edu Matrix</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/edumatrix/css/style.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/edumatrix/favicon.ico?v=1">
    <!-- Additional sizes for modern browsers (optional) -->
    <link rel="apple-touch-icon" sizes="180x180" href="/edumatrix/favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/edumatrix/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/edumatrix/favicon-16x16.png">
</head>
<body class="min-h-screen flex flex-col font-sans">
    <header class="bg-white shadow-lg p-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <a href="/edumatrix/index.php" class="flex items-center">
                    <img src="/edumatrix/images/logo.png" alt="Edu Matrix Logo" class="w-12 h-12 mr-3">
                    <h1 class="text-3xl font-extrabold text-purple-700 tracking-tight">Edu Matrix</h1>
                </a>
            </div>
            <nav class="space-x-3">
                <a href="/edumatrix/index.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white font-semibold text-lg hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Home</a>
                <a href="/edumatrix/programs.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white font-semibold text-lg hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Programs</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] !== 'admin'): ?>
                        <a href="/edumatrix/dashboard.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white font-semibold text-lg hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Dashboard</a>
                    <?php endif; ?>
                    <a href="/edumatrix/logout.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-red-500 to-red-700 text-white font-semibold text-lg hover:from-red-600 hover:to-red-800 hover:shadow-lg transition-all duration-300">Logout</a>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="/edumatrix/admin/index.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white font-semibold text-lg hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Admin Panel</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/edumatrix/login.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold text-lg hover:from-green-600 hover:to-green-800 hover:shadow-lg transition-all duration-300">Login</a>
                    <a href="/edumatrix/signup.php" class="inline-block px-5 py-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 text-white font-semibold text-lg hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="mt-4 flex justify-center space-x-4">
            <?php
            if ($categories_result->num_rows > 0) {
                while($cat_row = $categories_result->fetch_assoc()) {
                    $category = $cat_row['category'];
                    echo '<a href="/edumatrix/category_courses.php?category=' . urlencode($category) . '" class="bg-purple-700 text-white px-4 py-2 rounded-lg hover:bg-purple-800">' . htmlspecialchars($category) . '</a>';
                }
            }
            ?>
        </div>
    </header>
    <main class="flex-grow">