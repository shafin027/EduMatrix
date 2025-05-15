<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_GET['category']) || empty($_GET['category'])) {
    die("Invalid category.");
}

$category = $_GET['category'];
$sql = "SELECT id, class, category, price, discount_price, image FROM programs WHERE category = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $category);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}
$result = $stmt->get_result();
?>

    <section class="py-10 px-4 bg-gray-100">
        <h2 class="text-3xl font-bold text-center text-purple-700 mb-8">Courses in <?php echo htmlspecialchars($category); ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $imagePath = $row["image"] ? str_replace('Uploads/', 'uploads/', $row["image"]) : 'uploads/class_6.jpeg';
                    $price = htmlspecialchars($row["price"]);
                    $discount_price = $row["discount_price"] ? htmlspecialchars($row["discount_price"]) : null;
                    echo '<a href="course_details.php?id=' . $row["id"] . '" class="block bg-white rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-xl">
                            <div class="relative h-64">
                                <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["class"]) . '" class="w-full h-full object-cover">
                            </div>
                            <div class="p-4 text-center">
                                <p class="text-black font-semibold text-lg">' . htmlspecialchars($row["class"]) . ' - ' . htmlspecialchars($row["category"]) . '</p>
                                <p class="text-pink-500 font-semibold text-lg">';
                    if ($discount_price && $discount_price > 0) {
                        echo 'মূল্য <span class="line-through text-gray-500">৳' . $price . ' টাকা</span>';
                        echo '</p><p class="text-green-600 font-semibold text-lg">৳' . $discount_price . ' টাকা</p>';
                    } else {
                        echo 'মূল্য ৳' . $price . ' টাকা';
                    }
                    echo '</p>
                            </div>
                          </a>';
                }
            } else {
                echo "<p>No courses available in this category.</p>";
            }
            ?>
        </div>
    </section>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>