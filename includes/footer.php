</main>
    <footer class="bg-purple-700 text-white py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Edu Matrix Section -->
                <div>
                    <div class="flex items-center mb-4">
                        <img src="/edumatrix/images/logo1.png" alt="Edu Matrix Logo" class="w-10 h-10 mr-2">
                        <h3 class="text-xl font-bold">Edu Matrix</h3>
                    </div>
                    <p>Your trusted platform for academic excellence and growth.</p>
                </div>
                <!-- Quick Links Section -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul>
                        <li><a href="/edumatrix/index.php" class="hover:underline">Home</a></li>
                        <li><a href="/edumatrix/programs.php" class="hover:underline">Programs</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/edumatrix/logout.php" class="hover:underline">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/edumatrix/login.php" class="hover:underline">Login</a></li>
                            <li><a href="/edumatrix/signup.php" class="hover:underline">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- Contact Us Section -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <p>Phone: +880 123 456 789</p>
                    <p>Email: support@edumatrix.com</p>
                    <p>Address: 123 Education Lane, Dhaka, Bangladesh</p>
                </div>
            </div>
            <div class="border-t border-white mt-8 pt-4 text-center">
                <p>&copy; <?php echo date("Y"); ?> Edu Matrix. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>