<?php
session_start();
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['program_id']) || !isset($_GET['amount']) || !isset($_GET['user_id']) || $_GET['user_id'] != $_SESSION['user_id']) {
    header("Location: login.php?redirect=payment.php");
    exit();
}

$user_id = intval($_GET['user_id']);
$program_id = intval($_GET['program_id']);
$amount = floatval($_GET['amount']);

// Fetch program details for display
$sql = "SELECT class, category FROM programs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found.");
}

$program = $result->fetch_assoc();
$payment_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $payment_message = "Please select a payment method.";
    } else {
        // Simulate payment processing (replace with actual gateway integration)
        // For demo, assume payment is successful
        $enroll_sql = "INSERT INTO enrollments (user_id, program_id, status) VALUES (?, ?, 'enrolled')";
        $enroll_stmt = $conn->prepare($enroll_sql);
        $enroll_stmt->bind_param("ii", $user_id, $program_id);
        if ($enroll_stmt->execute()) {
            $payment_message = "Payment successful via $payment_method! Enrollment completed.";
            header("Location: dashboard.php");
            exit();
        } else {
            $payment_message = "Error enrolling in the course.";
        }
        $enroll_stmt->close();
    }
}
?>

    <section class="py-12 px-4 min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-2xl p-8 transform hover:shadow-3xl transition-shadow duration-300">
                <!-- Decorative Header -->
                <h2 class="text-3xl font-bold text-purple-700 mb-6 text-center relative">
                    Select Payment Method
                    <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full"></span>
                </h2>
                <p class="text-gray-700 mb-4"><strong>Course:</strong> <?php echo htmlspecialchars($program['class']); ?> - <?php echo htmlspecialchars($program['category']); ?></p>
                <p class="text-gray-700 mb-6"><strong>Amount to Pay:</strong> ৳<?php echo number_format($amount, 2); ?> টাকা</p>
                <?php if (isset($payment_message)): ?>
                    <p class="text-center mb-6 <?php echo strpos($payment_message, 'Success') !== false ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                        <?php echo htmlspecialchars($payment_message); ?>
                    </p>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Choose Payment Method</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 bg-gradient-to-r from-pink-50 to-purple-50 border border-gray-200 rounded-lg hover:bg-gradient-to-r hover:from-pink-100 hover:to-purple-100 cursor-pointer transform transition-all duration-300 hover:shadow-lg">
                                <input type="radio" name="payment_method" value="bKash" class="mr-3 focus:ring-purple-500" required>
                                <div class="flex items-center">
                                    <img src="images/bkash.png" alt="bKash" class="w-10 h-10 rounded-full mr-3 transform hover:scale-110 transition-transform duration-300">
                                    <span class="text-gray-800 font-medium">bKash</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 bg-gradient-to-r from-orange-50 to-red-50 border border-gray-200 rounded-lg hover:bg-gradient-to-r hover:from-orange-100 hover:to-red-100 cursor-pointer transform transition-all duration-300 hover:shadow-lg">
                                <input type="radio" name="payment_method" value="Nagad" class="mr-3 focus:ring-red-500" required>
                                <div class="flex items-center">
                                    <img src="images/nagad.png" alt="Nagad" class="w-10 h-10 rounded-full mr-3 transform hover:scale-110 transition-transform duration-300">
                                    <span class="text-gray-800 font-medium">Nagad</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 bg-gradient-to-r from-green-50 to-teal-50 border border-gray-200 rounded-lg hover:bg-gradient-to-r hover:from-green-100 hover:to-teal-100 cursor-pointer transform transition-all duration-300 hover:shadow-lg">
                                <input type="radio" name="payment_method" value="Rocket" class="mr-3 focus:ring-teal-500" required>
                                <div class="flex items-center">
                                    <img src="images/rocket.png" alt="Rocket" class="w-10 h-10 rounded-full mr-3 transform hover:scale-110 transition-transform duration-300">
                                    <span class="text-gray-800 font-medium">Rocket</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-gray-200 rounded-lg hover:bg-gradient-to-r hover:from-blue-100 hover:to-indigo-100 cursor-pointer transform transition-all duration-300 hover:shadow-lg">
                                <input type="radio" name="payment_method" value="Card" class="mr-3 focus:ring-indigo-500" required>
                                <div class="flex items-center">
                                    <img src="images/visa-mastercard.png" alt="Card" class="w-10 h-10 rounded-full mr-3 transform hover:scale-110 transition-transform duration-300">
                                    <span class="text-gray-800 font-medium">Credit/Debit Card</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-200 border border-gray-200 rounded-lg hover:bg-gradient-to-r hover:from-gray-100 hover:to-gray-300 cursor-pointer transform transition-all duration-300 hover:shadow-lg">
                                <input type="radio" name="payment_method" value="Bank Transfer" class="mr-3 focus:ring-gray-500" required>
                                <div class="flex items-center">
                                    <img src="images/bank.png" alt="Bank" class="w-10 h-10 rounded-full mr-3 transform hover:scale-110 transition-transform duration-300">
                                    <span class="text-gray-800 font-medium">Bank Transfer</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="confirm_payment" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transform hover:scale-105 transition-all duration-300 shadow-md">Confirm Payment</button>
                    </div>
                </form>
                <p class="text-gray-600 mt-6 text-center italic">Note: After selecting a payment method, you will be redirected to the payment gateway or provided with payment instructions.</p>
            </div>
        </div>
    </section>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>