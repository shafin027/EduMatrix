<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch users (exclude admins)
$sql = "SELECT id, username, email, name, phone, address, role, created_at FROM users WHERE role != 'admin'";
$result = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>

    <section class="py-10 px-4 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-purple-700 mb-8 text-center">Manage Users</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white rounded-lg shadow-lg">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Username</th>
                                <th class="p-3 text-left">Email</th>
                                <th class="p-3 text-left">Name</th>
                                <th class="p-3 text-left">Phone</th>
                                <th class="p-3 text-left">Address</th>
                                <th class="p-3 text-left">Role</th>
                                <th class="p-3 text-left">Joined</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-100">
                                    <td class="p-3"><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['address'] ?? 'N/A'); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                                    <td class="p-3">
                                        <a href="user_details.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:underline">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-700">No users available.</p>
            <?php endif; ?>
        </div>
    </section>

<?php
$conn->close();
include '../includes/footer.php';
?>