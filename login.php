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

    // Handle Login
    if (isset($_POST['login'])) {
        $password = $_POST['password'];

        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if ($password === $row['password']) { // Compare plain text passwords
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                header("Location: $redirect");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid username.";
        }
        $stmt->close();
    }

    // Handle Password Reset
    if (isset($_POST['reset'])) {
        $sql = "SELECT id, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Set default password based on username
            $new_password = ($username === 'admin') ? 'admin123' : 'user123';

            $update_sql = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_password, $username);
            if ($update_stmt->execute()) {
                $success = "Password reset successfully to '$new_password'.";
            } else {
                $error = "Error resetting password.";
            }
            $update_stmt->close();
        } else {
            $error = "Invalid username.";
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>

    <section class="py-10 px-4 flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold text-center text-purple-700 mb-6">Login</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                </div>
                <button type="submit" name="login" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition duration-300">Login</button>
                <button type="submit" name="reset" class="w-full mt-2 bg-yellow-500 text-white p-3 rounded-lg hover:bg-yellow-600 transition duration-300">Reset Password</button>
            </form>
            <p class="text-center mt-4">Don't have an account? <a href="signup.php?redirect=<?php echo urlencode($redirect); ?>" class="text-purple-600 hover:underline">Sign Up</a></p>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>