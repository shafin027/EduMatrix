<?php
include 'includes/header.php';
include 'includes/db_connect.php';

// Fetch all categories from the categories table
$categories_sql = "SELECT * FROM categories";
$result = $conn->query($categories_sql);
if (!$result) {
    die("Error fetching categories: " . $conn->error);
}
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : 'uploads/class_6.jpeg';
        $categories[] = [
            'name' => $row['category'],
            'image' => $image
        ];
    }
}
?>

    <section class="py-10 px-4 bg-gray-100">
        <h2 class="text-3xl font-bold text-center text-purple-700 mb-8">Program Categories</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <?php
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_name = $category['name'];
                    $imagePath = $category['image'];
                    echo '<a href="category_courses.php?category=' . urlencode($category_name) . '" class="block bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-xl">
                            <div class="relative h-64">
                                <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($category_name) . '" class="w-full h-full object-cover">
                            </div>
                            <div class="p-4 text-center">
                                <p class="text-black font-semibold text-lg">' . htmlspecialchars($category_name) . '</p>
                            </div>
                          </a>';
                }
            } else {
                echo "<p>No categories available.</p>";
            }
            ?>
        </div>
    </section>

<?php
$conn->close();
include 'includes/footer.php';
?>