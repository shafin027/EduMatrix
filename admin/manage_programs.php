<?php
include '../includes/header.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Fetch unique categories for dropdown
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
$categories = [];
if ($categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
        $categories[$row['id']] = ['name' => $row['category'], 'image' => $image];
    }
}

// Handle category selection or new category input
$category = '';
$category_image = null;
if (isset($_POST['add_program']) || isset($_POST['edit_program']) || isset($_POST['add_category']) || isset($_POST['edit_category'])) {
    $selected_category = trim($_POST['category'] ?? '');
    $new_category = trim($_POST['new_category'] ?? '');
    if ($selected_category === 'new_category' && !empty($new_category)) {
        $category = $new_category;
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
            $fileName = basename($_FILES["category_image"]["name"]);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ["jpg", "jpeg", "png", "gif"];
            if (in_array($fileType, $allowedTypes)) {
                $newFileName = time() . "_cat_" . $fileName;
                $targetFilePath = $uploadDir . $newFileName;
                if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $targetFilePath)) {
                    $category_image = "uploads/" . $newFileName;
                } else {
                    $error = "Error uploading category image.";
                }
            } else {
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed for category image.";
            }
        }
    } else {
        $category = $selected_category;
    }
}

// Add Program
if (isset($_POST['add_program'])) {
    $class = trim($_POST['class']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $description = trim($_POST['description']);
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = basename($_FILES["image"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (in_array($fileType, $allowedTypes)) {
            $newFileName = time() . "_" . $fileName;
            $targetFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $imagePath = "uploads/" . $newFileName;
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (!isset($error) && !empty($class) && !empty($category) && !empty($description)) {
        // Check if the category needs to be created
        if (!in_array($category, array_column($categories, 'name'))) {
            $sql = "INSERT INTO categories (category, image) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $category, $category_image);
            if ($stmt->execute()) {
                // Re-fetch categories to update the dropdown
                $categories_result = $conn->query($categories_sql);
                $categories = [];
                if ($categories_result->num_rows > 0) {
                    while ($row = $categories_result->fetch_assoc()) {
                        $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
                        $categories[$row['id']] = ['name' => $row['category'], 'image' => $image];
                    }
                }
            } else {
                $error = "Error creating new category: " . $conn->error;
                $stmt->close();
                exit;
            }
            $stmt->close();
        }
        // Add the program
        $sql = "INSERT INTO programs (class, category, price, discount_price, image, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsss", $class, $category, $price, $discount_price, $imagePath, $description);
        if ($stmt->execute()) {
            $success = "Program added successfully.";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields (Class, Category, Description) are required.";
    }
}

// Edit Program
if (isset($_POST['edit_program'])) {
    $id = intval($_POST['id']);
    $class = trim($_POST['class']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $description = trim($_POST['description']);
    $imagePath = str_replace('Uploads/', 'uploads/', $_POST['existing_image']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = basename($_FILES["image"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (in_array($fileType, $allowedTypes)) {
            $newFileName = time() . "_" . $fileName;
            $targetFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                if ($imagePath && file_exists("../" . $imagePath)) {
                    unlink("../" . $imagePath);
                }
                $imagePath = "uploads/" . $newFileName;
            } else {
                $error = "Error uploading new image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (!isset($error) && !empty($class) && !empty($category) && !empty($description)) {
        // If category doesn't exist, create it (though this is rare for edits)
        if (!in_array($category, array_column($categories, 'name'))) {
            $sql = "INSERT INTO categories (category, image) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $category, $category_image);
            if ($stmt->execute()) {
                $categories_result = $conn->query($categories_sql);
                $categories = [];
                if ($categories_result->num_rows > 0) {
                    while ($row = $categories_result->fetch_assoc()) {
                        $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
                        $categories[$row['id']] = ['name' => $row['category'], 'image' => $image];
                    }
                }
            } else {
                $error = "Error creating new category: " . $conn->error;
                $stmt->close();
                exit;
            }
            $stmt->close();
        }
        $sql = "UPDATE programs SET class = ?, category = ?, price = ?, discount_price = ?, image = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssi", $class, $category, $price, $discount_price, $imagePath, $description, $id);
        if ($stmt->execute()) {
            $success = "Program updated successfully.";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields (Class, Category, Description) are required.";
    }
}

// Delete Program
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "SELECT image FROM programs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $imagePath = str_replace('Uploads/', 'uploads/', $row['image']);
        if ($imagePath && file_exists("../" . $imagePath)) {
            unlink("../" . $imagePath);
        }
    }
    $stmt->close();

    $sql = "DELETE FROM programs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $success = "Program deleted successfully.";
    header("Location: manage_programs.php");
    exit();
}

// Add Category
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $category_image = null;

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $fileName = basename($_FILES["category_image"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (in_array($fileType, $allowedTypes)) {
            $newFileName = time() . "_cat_" . $fileName;
            $targetFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $targetFilePath)) {
                $category_image = "uploads/" . $newFileName;
            } else {
                $error = "Error uploading category image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed for category image.";
        }
    }

    if (!isset($error) && !empty($category_name)) {
        $sql = "INSERT INTO categories (category, image) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $category_name, $category_image);
        if ($stmt->execute()) {
            $success = "Category added successfully.";
            // Re-fetch categories to update the dropdown and list
            $categories_result = $conn->query($categories_sql);
            $categories = [];
            if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
                    $categories[$row['id']] = ['name' => $row['category'], 'image' => $image];
                }
            }
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Category name is required.";
    }
    if (isset($success)) {
        header("Location: manage_programs.php");
        exit();
    }
}

// Edit Category
if (isset($_POST['edit_category'])) {
    $category_id = intval($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    $existing_image = str_replace('Uploads/', 'uploads/', $_POST['existing_category_image']);
    $category_image = $existing_image;

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $fileName = basename($_FILES["category_image"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (in_array($fileType, $allowedTypes)) {
            $newFileName = time() . "_cat_" . $fileName;
            $targetFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES["category_image"]["tmp_name"], $targetFilePath)) {
                if ($existing_image && file_exists("../" . $existing_image)) {
                    unlink("../" . $existing_image);
                }
                $category_image = "uploads/" . $newFileName;
            } else {
                $error = "Error uploading new category image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed for category image.";
        }
    }

    if (!isset($error) && !empty($category_name)) {
        $sql = "UPDATE categories SET category = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $category_name, $category_image, $category_id);
        if ($stmt->execute()) {
            $success = "Category updated successfully.";
            // Re-fetch categories to update the dropdown and list
            $categories_result = $conn->query($categories_sql);
            $categories = [];
            if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    $image = $row['image'] ? str_replace('Uploads/', 'uploads/', $row['image']) : null;
                    $categories[$row['id']] = ['name' => $row['category'], 'image' => $image];
                }
            }
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Category name is required.";
    }
    if (isset($success)) {
        header("Location: manage_programs.php");
        exit();
    }
}

// Delete Category
if (isset($_GET['delete_category'])) {
    $category_id = intval($_GET['delete_category']);
    $sql = "SELECT image FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $imagePath = str_replace('Uploads/', 'uploads/', $row['image']);
        if ($imagePath && file_exists("../" . $imagePath)) {
            unlink("../" . $imagePath);
        }
    }
    $stmt->close();

    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();
    $success = "Category deleted successfully.";
    header("Location: manage_programs.php");
    exit();
}

// Add Coupon
if (isset($_POST['add_coupon'])) {
    $code = trim($_POST['code']);
    $discount_percent = floatval($_POST['discount_percent']);
    $expiry_date = $_POST['expiry_date'];

    if (empty($code) || $discount_percent < 0 || $discount_percent > 100 || empty($expiry_date)) {
        $error = "Invalid coupon details. Ensure code is unique, discount is 0-100, and expiry date is set.";
    } elseif (strtotime($expiry_date) < time()) {
        $error = "Expiry date must be in the future.";
    } else {
        $check_sql = "SELECT code FROM coupons WHERE code = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Coupon code already exists.";
        } else {
            $sql = "INSERT INTO coupons (code, discount_percent, expiry_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sds", $code, $discount_percent, $expiry_date);
            if ($stmt->execute()) {
                $success = "Coupon added successfully.";
            } else {
                $error = "Error: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Delete Coupon
if (isset($_GET['delete_coupon'])) {
    $code = $_GET['delete_coupon'];
    $sql = "DELETE FROM coupons WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->close();
    $success = "Coupon deleted successfully.";
    header("Location: manage_programs.php");
    exit();
}

// Fetch Programs
$sql = "SELECT * FROM programs";
$program_result = $conn->query($sql);

// Fetch Coupons
$coupon_sql = "SELECT * FROM coupons";
$coupon_result = $conn->query($coupon_sql);

// Fetch Program for Editing
$edit_program = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $sql = "SELECT * FROM programs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_program = $result->fetch_assoc();
    if ($edit_program && $edit_program['image']) {
        $edit_program['image'] = str_replace('Uploads/', 'uploads/', $edit_program['image']);
    }
    $stmt->close();
}

// Fetch Category for Editing
$edit_category = null;
if (isset($_GET['edit_category'])) {
    $edit_category_id = intval($_GET['edit_category']);
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_category = $result->fetch_assoc();
    if ($edit_category && $edit_category['image']) {
        $edit_category['image'] = str_replace('Uploads/', 'uploads/', $edit_category['image']);
    }
    $stmt->close();
}
?>

    <section class="py-10 px-4">
        <h2 class="text-3xl font-bold text-center text-purple-700 mb-8">Manage Programs, Categories, and Coupons</h2>

        <!-- Messages -->
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="text-green-500 text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <!-- Add/Edit Program Form -->
        <div class="max-w-md mx-auto mb-8">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-purple-700 mb-4"><?php echo $edit_program ? 'Edit Program' : 'Add New Program'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_program): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_program['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_program['image']); ?>">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label for="class" class="block text-gray-700">Class</label>
                        <input type="text" id="class" name="class" value="<?php echo $edit_program ? htmlspecialchars($edit_program['class']) : ''; ?>" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="category" class="block text-gray-700">Category</label>
                        <select id="category" name="category" class="w-full p-2 border rounded" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $id => $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" data-image="<?php echo htmlspecialchars($cat['image'] ?? ''); ?>" <?php echo $edit_program && $edit_program['category'] == $cat['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new_category">+ Add New Category</option>
                        </select>
                        <input type="text" id="new_category_input" name="new_category" class="w-full p-2 border rounded mt-2 hidden" placeholder="Enter new category name">
                        <input type="file" id="category_image" name="category_image" class="w-full p-2 border rounded mt-2 hidden" accept="image/*" placeholder="Upload category image">
                        <script>
                            document.getElementById('category').addEventListener('change', function() {
                                const newCategoryOption = this.value === 'new_category';
                                document.getElementById('new_category_input').classList.toggle('hidden', !newCategoryOption);
                                document.getElementById('category_image').classList.toggle('hidden', !newCategoryOption);
                            });
                        </script>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-gray-700">Price (BDT)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo $edit_program ? htmlspecialchars($edit_program['price']) : ''; ?>" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="discount_price" class="block text-gray-700">Discount Price (BDT, optional)</label>
                        <input type="number" id="discount_price" name="discount_price" step="0.01" value="<?php echo $edit_program ? htmlspecialchars($edit_program['discount_price']) : ''; ?>" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700">Description</label>
                        <textarea id="description" name="description" class="w-full p-2 border rounded"><?php echo $edit_program ? htmlspecialchars($edit_program['description']) : ''; ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="image" class="block text-gray-700">Program Image <?php echo $edit_program ? '(leave blank to keep current)' : ''; ?></label>
                        <input type="file" id="image" name="image" class="w-full p-2 border rounded" accept="image/*" <?php echo $edit_program ? '' : 'required'; ?>>
                        <?php if ($edit_program && $edit_program['image']): ?>
                            <p class="text-gray-600">Current: <img src="../<?php echo htmlspecialchars($edit_program['image']); ?>" alt="Current Image" class="w-20 h-20 object-cover mt-2"></p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="<?php echo $edit_program ? 'edit_program' : 'add_program'; ?>" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-800"><?php echo $edit_program ? 'Update Program' : 'Add Program'; ?></button>
                </form>
            </div>
        </div>


        <!-- Program List -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-purple-700 mb-4">Programs</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                if ($program_result->num_rows > 0) {
                    while($row = $program_result->fetch_assoc()) {
                        $imagePath = $row["image"] ? str_replace('Uploads/', 'uploads/', "../" . $row["image"]) : "../uploads/placeholder.jpg";
                        echo '<div class="bg-white rounded-lg shadow-lg p-4">
                                <div class="relative h-40 mb-4">
                                    <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["class"]) . '" class="w-full h-full object-cover rounded" onerror="this.src=\'../uploads/placeholder.jpg\'">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600 to-indigo-600 opacity-75 flex items-center justify-center rounded">
                                        <div class="text-white text-xl font-bold text-center">' . strtoupper(htmlspecialchars($row["class"])) . '</div>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-purple-700">' . htmlspecialchars($row["class"]) . '</h3>
                                <p class="text-gray-700">' . htmlspecialchars($row["category"]) . '</p>
                                <p class="text-purple-600 font-semibold">Price: ' . htmlspecialchars($row["price"]) . ' BDT</p>
                                <p class="text-gray-600">' . ($row["discount_price"] ? 'Discount: ' . htmlspecialchars($row["discount_price"]) . ' BDT' : '') . '</p>
                                <p class="text-gray-600">' . (strlen($row["description"]) > 100 ? substr(htmlspecialchars($row["description"]), 0, 100) . '...' : htmlspecialchars($row["description"])) . '</p>
                                <a href="?edit=' . $row["id"] . '" class="text-blue-500 hover:underline">Edit</a><br>
                                <a href="?delete=' . $row["id"] . '" class="text-red-500 hover:underline" onclick="return confirm(\'Are you sure you want to delete this program?\')">Delete</a>
                              </div>';
                    }
                } else {
                    echo "<p>No programs available.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Category List -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-purple-700 mb-4">Categories</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $category_result = $conn->query("SELECT * FROM categories");
                if ($category_result->num_rows > 0) {
                    while($row = $category_result->fetch_assoc()) {
                        $imagePath = $row["image"] ? str_replace('Uploads/', 'uploads/', "../" . $row["image"]) : "../uploads/placeholder.jpg";
                        echo '<div class="bg-white rounded-lg shadow-lg p-4">
                                <div class="relative h-40 mb-4">
                                    <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row["category"]) . '" class="w-full h-full object-cover rounded" onerror="this.src=\'../uploads/placeholder.jpg\'">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600 to-indigo-600 opacity-75 flex items-center justify-center rounded">
                                        <div class="text-white text-xl font-bold text-center">' . strtoupper(htmlspecialchars($row["category"])) . '</div>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-purple-700">' . htmlspecialchars($row["category"]) . '</h3>
                                <a href="?edit_category=' . $row["id"] . '" class="text-blue-500 hover:underline">Edit</a><br>
                                <a href="?delete_category=' . $row["id"] . '" class="text-red-500 hover:underline" onclick="return confirm(\'Are you sure you want to delete this category?\')">Delete</a>
                              </div>';
                    }
                } else {
                    echo "<p>No categories available.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Add Coupon Form -->
        <div class="max-w-md mx-auto mb-8">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-purple-700 mb-4">Add New Coupon</h3>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="code" class="block text-gray-700">Coupon Code</label>
                        <input type="text" id="code" name="code" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="discount_percent" class="block text-gray-700">Discount Percent (0-100)</label>
                        <input type="number" id="discount_percent" name="discount_percent" step="0.01" min="0" max="100" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="expiry_date" class="block text-gray-700">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="w-full p-2 border rounded" required>
                    </div>
                    <button type="submit" name="add_coupon" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-800">Add Coupon</button>
                </form>
            </div>
        </div>

        <!-- Coupon List -->
        <div>
            <h3 class="text-2xl font-bold text-purple-700 mb-4">Coupons</h3>
            <?php if ($coupon_result->num_rows > 0): ?>
                <table class="w-full bg-white rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-purple-600 text-white">
                            <th class="p-3 text-left">Code</th>
                            <th class="p-3 text-left">Discount (%)</th>
                            <th class="p-3 text-left">Expiry Date</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $coupon_result->fetch_assoc()): ?>
                            <tr>
                                <td class="p-3"><?php echo htmlspecialchars($row["code"]); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row["discount_percent"]); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row["expiry_date"]); ?></td>
                                <td class="p-3">
                                    <a href="?delete_coupon=<?php echo urlencode($row["code"]); ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this coupon?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No coupons available.</p>
            <?php endif; ?>
        </div>
    </section>

<?php
$conn->close();
include '../includes/footer.php';
?>