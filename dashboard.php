<?php
include 'includes/header.php';
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT username, email, name, phone, address, profile_picture FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Update Profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Handle Profile Picture Upload
    $profile_picture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = 'uploads/profiles/';
        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Validate file type (only allow images)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['profile_picture']['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $profile_message = "Error: Only JPEG, PNG, or GIF files are allowed.";
        } else {
            $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $file_name = $user_id . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                $profile_picture = $file_path;
            } else {
                $profile_message = "Error: Failed to upload the profile picture.";
            }
        }
    }

    // Update the user in the database
    $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $profile_picture, $user_id);
    if ($update_stmt->execute()) {
        $profile_message = "Profile updated successfully!";
        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['address'] = $address;
        $user['profile_picture'] = $profile_picture;
    } else {
        $profile_message = "Error updating profile.";
    }
    $update_stmt->close();
}

// Mark Course as Finished
if (isset($_GET['finish'])) {
    $enrollment_id = $_GET['finish'];
    $finish_sql = "UPDATE enrollments SET status = 'finished' WHERE id = ? AND user_id = ?";
    $finish_stmt = $conn->prepare($finish_sql);
    $finish_stmt->bind_param("ii", $enrollment_id, $user_id);
    $finish_stmt->execute();
    $finish_stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Fetch Enrolled Courses
$enrolled_sql = "SELECT e.id, e.status, p.class, p.category, p.image 
                 FROM enrollments e 
                 JOIN programs p ON e.program_id = p.id 
                 WHERE e.user_id = ? AND e.status = 'enrolled'";
$enrolled_stmt = $conn->prepare($enrolled_sql);
$enrolled_stmt->bind_param("i", $user_id);
$enrolled_stmt->execute();
$enrolled_result = $enrolled_stmt->get_result();

// Fetch Finished Courses
$finished_sql = "SELECT e.id, e.status, p.class, p.category, p.image 
                 FROM enrollments e 
                 JOIN programs p ON e.program_id = p.id 
                 WHERE e.user_id = ? AND e.status = 'finished'";
$finished_stmt = $conn->prepare($finished_sql);
$finished_stmt->bind_param("i", $user_id);
$finished_stmt->execute();
$finished_result = $finished_stmt->get_result();

// Define the default profile picture path
$default_profile_picture = '/edumatrix/images/default_profile.jpg';
?>

    <section class="py-10 px-4 bg-gray-100">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-purple-700 mb-8">Customer Dashboard</h2>

            <!-- Profile Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-2xl font-bold text-purple-700 mb-4">Profile</h3>
                <?php if (isset($profile_message)): ?>
                    <p class="text-center mb-4 <?php echo strpos($profile_message, 'Success') !== false ? 'text-green-500' : 'text-red-500'; ?>"><?php echo htmlspecialchars($profile_message); ?></p>
                <?php endif; ?>
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/3 flex justify-center">
                        <?php
                        // Check if profile picture exists and is readable
                        $profile_picture_path = $user['profile_picture'] && file_exists($user['profile_picture']) 
                            ? $user['profile_picture'] 
                            : $default_profile_picture;
                        ?>
                        <img src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Profile Picture" class="w-32 h-32 rounded-full object-cover mb-4 md:mb-0">
                    </div>
                    <div class="md:w-2/3 md:pl-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="user_id" class="block text-gray-700 mb-2">User ID (Cannot be changed)</label>
                                <input type="text" id="user_id" value="<?php echo htmlspecialchars($user_id); ?>" class="w-full p-3 border rounded-lg bg-gray-200" disabled>
                            </div>
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 mb-2">Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full p-3 border rounded-lg">
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 mb-2">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-3 border rounded-lg" required>
                            </div>
                            <div class="mb-4">
                                <label for="phone" class="block text-gray-700 mb-2">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full p-3 border rounded-lg">
                            </div>
                            <div class="mb-4">
                                <label for="address" class="block text-gray-700 mb-2">Address</label>
                                <textarea id="address" name="address" class="w-full p-3 border rounded-lg"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="profile_picture" class="block text-gray-700 mb-2">Profile Picture</label>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="w-full p-3 border rounded-lg">
                            </div>
                            <button type="submit" name="update_profile" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-800">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Currently Enrolled Courses -->
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-purple-700 mb-4">Currently Enrolled Courses</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    if ($enrolled_result->num_rows > 0) {
                        while($row = $enrolled_result->fetch_assoc()) {
                            $imagePath = $row["image"] ? $row["image"] : 'uploads/class_6.jpeg';
                            echo '<div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                    <div class="relative h-64">
                                        <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["class"]) . '" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-4 text-center">
                                        <p class="text-black font-semibold text-lg">' . htmlspecialchars($row["class"]) . ' - ' . htmlspecialchars($row["category"]) . '</p>
                                        <a href="dashboard.php?finish=' . $row["id"] . '" class="mt-2 inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Mark as Finished</a>
                                    </div>
                                  </div>';
                        }
                    } else {
                        echo "<p>You are not currently enrolled in any courses.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Finished Courses -->
            <div>
                <h3 class="text-2xl font-bold text-purple-700 mb-4">Finished Courses</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    if ($finished_result->num_rows > 0) {
                        while($row = $finished_result->fetch_assoc()) {
                            $imagePath = $row["image"] ? $row["image"] : 'uploads/class_6.jpeg';
                            echo '<div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                    <div class="relative h-64">
                                        <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["class"]) . '" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-4 text-center">
                                        <p class="text-black font-semibold text-lg">' . htmlspecialchars($row["class"]) . ' - ' . htmlspecialchars($row["category"]) . '</p>
                                    </div>
                                  </div>';
                        }
                    } else {
                        echo "<p>You have not finished any courses yet.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

<?php
$enrolled_stmt->close();
$finished_stmt->close();
$conn->close();
include 'includes/footer.php';
?>