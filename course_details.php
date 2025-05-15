<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid course ID.");
}

$id = $_GET['id'];
$sql = "SELECT class, category, price, discount_price, image, description FROM programs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found.");
}

$row = $result->fetch_assoc();

// Handle Enrollment (only for non-admins)
$enroll_message = '';
if (isset($_POST['enroll'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=course_details.php?id=" . $id);
        exit();
    }

    // Check if user is admin
    if ($_SESSION['role'] === 'admin') {
        $enroll_message = "Admins cannot enroll in courses.";
    } else {
        $user_id = $_SESSION['user_id'];
        $program_id = $id;

        // Check if already enrolled
        $check_sql = "SELECT * FROM enrollments WHERE user_id = ? AND program_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $program_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            // Redirect to payment page with program ID
            header("Location: payment.php?program_id=" . $id);
            exit();
        } else {
            $enroll_message = "You are already enrolled in this course.";
        }
        $check_stmt->close();
    }
}
?>

    <section class="py-10 px-4 max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="relative h-64">
                <img src="<?php echo htmlspecialchars($row["image"] ? $row["image"] : 'uploads/class_6.jpeg'); ?>" alt="<?php echo htmlspecialchars($row["class"]); ?>" class="w-full h-full object-cover">
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold text-purple-700 mb-4"><?php echo htmlspecialchars($row["class"]); ?></h2>
                <p class="text-gray-700 mb-4"><strong>Category:</strong> <?php echo htmlspecialchars($row["category"]); ?></p>
                <p class="text-gray-700 mb-4"><strong>Original Price:</strong> ৳<?php echo htmlspecialchars($row["price"]); ?> টাকা</p>
                <?php if ($row["discount_price"] !== null): ?>
                    <p class="text-green-600 mb-4"><strong>Discount Price:</strong> ৳<?php echo htmlspecialchars($row["discount_price"]); ?> টাকা</p>
                <?php endif; ?>
                <p class="text-gray-700 mb-4"><strong>Description:</strong> <?php echo htmlspecialchars($row["description"] ? $row["description"] : 'No description available.'); ?></p>
                <?php if (isset($enroll_message)): ?>
                    <p class="text-center mb-4 <?php echo strpos($enroll_message, 'Success') !== false ? 'text-green-500' : 'text-red-500'; ?>"><?php echo htmlspecialchars($enroll_message); ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <p class="text-center text-red-500 mb-4">Admins cannot enroll in courses.</p>
                <?php else: ?>
                    <form method="POST" action="">
                        <button type="submit" name="enroll" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-800">Enroll Now</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>