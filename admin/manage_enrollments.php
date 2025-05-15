<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch enrollments with user and program details
$sql = "SELECT e.id, e.user_id, e.program_id, e.enrollment_date, e.status, u.username, p.class, p.category 
        FROM enrollments e 
        JOIN users u ON e.user_id = u.id 
        JOIN programs p ON e.program_id = p.id";
$result = $conn->query($sql);

// Handle marking as finished
if (isset($_GET['mark_finished']) && is_numeric($_GET['mark_finished'])) {
    $enrollment_id = intval($_GET['mark_finished']);
    $update_sql = "UPDATE enrollments SET status = 'finished' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $enrollment_id);
    if ($stmt->execute()) {
        header("Location: manage_enrollments.php");
        exit();
    }
    $stmt->close();
}
?>

<?php include '../includes/header.php'; ?>

    <section class="py-10 px-4 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-purple-700 mb-8 text-center">View Enrollments</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white rounded-lg shadow-lg">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">User</th>
                                <th class="p-3 text-left">Program</th>
                                <th class="p-3 text-left">Category</th>
                                <th class="p-3 text-left">Enrollment Date</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-100">
                                    <td class="p-3"><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['class']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['enrollment_date']))); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td class="p-3">
                                        <?php if ($row['status'] === 'enrolled'): ?>
                                            <a href="?mark_finished=<?php echo $row['id']; ?>" class="text-green-500 hover:underline" onclick="return confirm('Mark this enrollment as finished?')">Mark as Finished</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-700">No enrollments available.</p>
            <?php endif; ?>
        </div>
    </section>

<?php
$conn->close();
include '../includes/footer.php';
?>