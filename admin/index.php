<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch stats for the dashboard
$total_users_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$total_users_result = $conn->query($total_users_sql);
$total_users = $total_users_result->fetch_assoc()['total'];

$total_programs_sql = "SELECT COUNT(*) as total FROM programs";
$total_programs_result = $conn->query($total_programs_sql);
$total_programs = $total_programs_result->fetch_assoc()['total'];

$total_enrollments_sql = "SELECT COUNT(*) as total FROM enrollments";
$total_enrollments_result = $conn->query($total_enrollments_sql);
$total_enrollments = $total_enrollments_result->fetch_assoc()['total'];
?>

<?php include '../includes/header.php'; ?>

    <section class="py-10 px-4 bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-purple-700 to-purple-900 text-white rounded-lg shadow-lg p-6 mb-8 flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Welcome, Admin!</h2>
                    <p class="text-lg">Manage Edu Matrix with ease. Here's an overview of your platform as of <?php echo date('F j, Y, g:i A T'); ?>.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-full transition-all duration-300">Logout</a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <h3 class="text-xl font-semibold text-purple-700 mb-2">Total Users</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($total_users); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <h3 class="text-xl font-semibold text-purple-700 mb-2">Total Programs</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($total_programs); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <h3 class="text-xl font-semibold text-purple-700 mb-2">Total Enrollments</h3>
                    <p class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($total_enrollments); ?></p>
                </div>
            </div>

            <!-- Quick Navigation Tiles -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="manage_users.php" class="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-xl font-semibold mb-2">View Users</h3>
                    <p>View and manage all registered users.</p>
                </a>
                <a href="manage_enrollments.php" class="bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-xl font-semibold mb-2">View Enrollments</h3>
                    <p>Monitor student enrollments.</p>
                </a>
                <a href="manage_programs.php" class="bg-gradient-to-r from-green-500 to-green-700 text-white rounded-lg shadow-lg p-6 text-center transform hover:scale-105 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-xl font-semibold mb-2">Manage Programs</h3>
                    <p>Create and manage programs and coupons.</p>
                </a>
            </div>

            <!-- Manage Programs Button -->
            <div class="text-center mb-8">
                <a href="manage_programs.php" class="inline-block bg-gradient-to-r from-purple-600 to-purple-800 text-white px-8 py-4 rounded-full text-lg font-semibold hover:from-purple-700 hover:to-purple-900 hover:shadow-lg transition-all duration-300">Manage Programs</a>
            </div>
        </div>
    </section>

<?php
$conn->close();
include '../includes/footer.php';
?>