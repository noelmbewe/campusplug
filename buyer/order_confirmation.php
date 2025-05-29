<?php
session_start();
require_once '../db_connect.php';
require_once '../user_functions.php';
checkBuyer();
$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - CampusPlug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .primary-bg { background-color: #714315; }
        .primary-text { color: #714315; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex flex-1">
        <aside id="sidebar" class="bg-[#714315] text-white w-64 h-screen fixed top-0 left-0 flex flex-col transition-transform duration-300 z-50">
            <div class="p-4 flex items-center justify-between">
                <h1 class="text-xl font-bold">CampusPlug</h1>
                <button id="toggle-sidebar" class="text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="flex-1">
                <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-home mr-2"></i>
                    <span>Shop</span>
                </a>
                <a href="cart.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    <span>Cart</span>
                </a>
                <a href="orders.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-box mr-2"></i>
                    <span>Orders</span>
                </a>
                <a href="profile.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-user mr-2"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>
        <div class="flex-1 main-content ml-64 transition-all duration-300">
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <div class="flex items-center">
                    <button id="mobile-menu" class="text-gray-700 focus:outline-none md:hidden mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-semibold">Order Confirmation</h2>
                </div>
                <div class="relative">
                    <div class="relative">
                    <button id="profile-toggle" class="flex items-center text-gray-700 focus:outline-none">
                        <i class="fas fa-user-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Buyer'); ?></span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg hidden">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="../logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </header>
            <section class="py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-white p-8 rounded-lg shadow-lg text-center">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Thank You for Your Order!</h2>
                        <p class="text-gray-600 mb-4">Your order #<?php echo htmlspecialchars($order_id); ?> has been placed successfully.</p>
                        <a href="orders.php" class="bg-[#714315] text-white py-2 px-6 rounded-full hover:bg-[#5a330f] inline-block">View Orders</a>
                        <a href="dashboard.php" class="ml-4 text-[#714315] hover:underline">Continue Shopping</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const collapsed = sidebar.classList.contains('w-20');
            sidebar.classList.toggle('w-20');
            sidebar.classList.toggle('w-64');
            sidebar.querySelectorAll('span').forEach(span => span.classList.toggle('hidden', !collapsed));
            sidebar.querySelector('h1').classList.toggle('hidden', !collapsed);
            mainContent.classList.toggle('ml-64');
            mainContent.classList.toggle('ml-20');
        });
        document.getElementById('mobile-menu').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
        document.getElementById('profile-toggle').addEventListener('click', () => {
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        });
    </script>
</body>
</html>
<?>