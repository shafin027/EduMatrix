<?php
session_start();
include 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        header("Location: $redirect");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
}
?>

<?php include 'includes/header.php'; ?>

    <section class="py-10 px-4 flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold text-center text-purple-700 mb-6">Sign Up</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition duration-300">Sign Up</button>
            </form>
            <p class="text-center mt-4">Already have an account? <a href="login.php?redirect=<?php echo urlencode($redirect); ?>" class="text-purple-600 hover:underline">Login</a></p>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>