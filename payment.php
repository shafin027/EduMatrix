<?php
session_start();
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['program_id']) || !is_numeric($_GET['program_id'])) {
    header("Location: login.php?redirect=payment.php");
    exit();
}

// Redirect admins
if ($_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$program_id = intval($_GET['program_id']);

// Fetch program details
$sql = "SELECT class, category, price, discount_price FROM programs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found.");
}

$program = $result->fetch_assoc();
$base_price = $program['discount_price'] ?? $program['price'];
$final_price = $base_price;
$payment_message = '';
$coupon_applied = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    
    if (!empty($coupon_code)) {
        $coupon_sql = "SELECT discount_percent FROM coupons WHERE code = ? AND expiry_date >= CURDATE()";
        $coupon_stmt = $conn->prepare($coupon_sql);
        $coupon_stmt->bind_param("s", $coupon_code);
        $coupon_stmt->execute();
        $coupon_result = $coupon_stmt->get_result();

        if ($coupon_result->num_rows > 0) {
            $coupon = $coupon_result->fetch_assoc();
            $discount_percent = $coupon['discount_percent'];
            $discount = $base_price * ($discount_percent / 100);
            $final_price = max(0, $base_price - $discount);
            $coupon_applied = true;
            $payment_message = "Coupon '$coupon_code' applied! $discount_percent% discount. New amount: ৳" . number_format($final_price, 2) . " টাকা.";
        } else {
            $payment_message = "Invalid or expired coupon code.";
        }
        $coupon_stmt->close();
    } else {
        $payment_message = "Please enter a coupon code.";
    }
}
?>

    <section class="py-10 px-4 max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-purple-700 mb-4">Payment Confirmation</h2>
            <p class="text-gray-700 mb-4"><strong>Course:</strong> <?php echo htmlspecialchars($program['class']); ?> - <?php echo htmlspecialchars($program['category']); ?></p>
            <p class="text-gray-700 mb-4"><strong>Original Price:</strong> ৳<?php echo number_format($program['price'], 2); ?> টাকা</p>
            <?php if ($program['discount_price'] !== null): ?>
                <p class="text-green-600 mb-4"><strong>Discount Price:</strong> ৳<?php echo number_format($program['discount_price'], 2); ?> টাকা</p>
            <?php endif; ?>
            <p class="text-gray-700 mb-4"><strong>Final Amount to Pay:</strong> ৳<?php echo number_format($final_price, 2); ?> টাকা</p>
            <?php if (isset($payment_message)): ?>
                <p class="text-center mb-4 <?php echo strpos($payment_message, 'Success') !== false || strpos($payment_message, 'applied') !== false ? 'text-green-500' : 'text-red-500'; ?>">
                    <?php echo htmlspecialchars($payment_message); ?>
                </p>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="coupon_code" class="block text-gray-700 mb-2">Coupon Code (e.g., EDUMATRIX100)</label>
                    <input type="text" id="coupon_code" name="coupon_code" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" value="<?php echo isset($coupon_code) ? htmlspecialchars($coupon_code) : ''; ?>">
                </div>
                <button type="submit" name="apply_coupon" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700">Apply Coupon</button>
            </form>
            <form method="GET" action="payment_options.php" class="mt-4">
                <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $final_price; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">Pay Now</button>
            </form>
        </div>
    </section>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>