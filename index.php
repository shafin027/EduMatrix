<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-cache, must-revalidate");
include 'includes/header.php';
include 'includes/db_connect.php';

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch categories and images from the categories table
$categories = [];
$categories_sql = "SELECT category, image FROM categories"; // Removed GROUP BY
$categories_result = $conn->query($categories_sql);

if ($categories_result === false) {
    echo "Categories Query Error: " . $conn->error . "<br>";
} elseif ($categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        // Ensure image path uses 'uploads/' (lowercase)
        $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
        // Debug: Log the image path being used
        error_log("Image path for category '" . $row['category'] . "': " . $image);
        $categories[$row['category']] = $image;
    }
} else {
    echo "Warning: No categories found in the 'categories' table.<br>";
}
?>

    <!-- Video Banner Section -->
    <section class="relative h-96">
        <video autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover">
            <source src="/edumatrix/videos/education_animation.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center">
            <h1 class="text-4xl font-bold text-white mb-4">Welcome to Edu Matrix</h1>
            <p class="text-lg text-white mb-6">Empowering Your Academic Journey</p>
            <a href="/edumatrix/programs.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-800">Explore Programs</a>
        </div>
    </section>

    <!-- Students Served Section -->
    <section class="py-8 bg-purple-100">
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-purple-700 text-white rounded-lg shadow-lg p-6 text-center transform transition duration-300 hover:scale-105">
                <h3 class="text-4xl font-bold mb-2">10,000+</h3>
                <p class="text-lg">Students Served by Edu Matrix</p>
            </div>
        </div>
    </section>

    <!-- Program Categories Section -->
    <section class="py-10 px-4 bg-gray-100">
        <h2 class="text-3xl font-bold text-center text-purple-700 mb-8">Program Categories</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <?php
            if (!empty($categories)) {
                foreach ($categories as $category => $image) {
                    $imagePath = $image ? htmlspecialchars($image) : "uploads/placeholder.jpg";
                    // Debug: Print the image path being used
                    echo "<!-- Debug: Attempting to load image at " . htmlspecialchars($imagePath) . " -->";
                    echo '<a href="category_courses.php?category=' . urlencode($category) . '" class="block bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-xl">
                            <div class="relative h-64">
                                <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($category) . '" class="w-full h-full object-cover" onerror="this.onerror=null;this.src=\'uploads/placeholder.jpg\';">
                            </div>
                            <div class="p-4 text-center">
                                <p class="text-black font-semibold text-lg">' . htmlspecialchars($category) . '</p>
                            </div>
                          </a>';
                }
            } else {
                echo "<p class='text-center text-gray-600'>No categories available.</p>";
            }
            ?>
        </div>
    </section>

<?php
$conn->close();
include 'includes/footer.php';
?>