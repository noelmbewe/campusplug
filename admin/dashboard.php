<?php
require_once '../db_connect.php'; // Ensure session_start()
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($pdo);
$stats = $admin->getPlatformStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-100 flex h-screen">
    <?php include '../include/sidebar.php'; ?>
    <div class="flex-1 ml-64">
        <?php include '../include/navbar.php'; ?>
        <main class="p-6">
            <h3 class="text-2xl font-semibold mb-6">Welcome to CampusPlug Admin</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6 card">
                    <div class="flex items-center">
                        <i class="fas fa-users text-3xl text-blue-500 mr-4"></i>
                        <div>
                            <h4 class="text-lg font-semibold">Total Users</h4>
                            <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_users'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 card">
                    <div class="flex items-center">
                        <i class="fas fa-store text-3xl text-green-500 mr-4"></i>
                        <div>
                            <h4 class="text-lg font-semibold">Total Vendors</h4>
                            <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_vendors'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 card">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-3xl text-yellow-500 mr-4"></i>
                        <div>
                            <h4 class="text-lg font-semibold">Total Orders</h4>
                            <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_orders'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 card">
                    <div class="flex items-center">
                        <i class="fas fa-box text-3xl text-red-500 mr-4"></i>
                        <div>
                            <h4 class="text-lg font-semibold">Total Products</h4>
                            <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_products'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        $(document).ready(function() {
            $('#toggle-sidebar').click(function() {
                const sidebar = $('#sidebar');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('span').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                $.post('set_sidebar_state.php', { collapsed: collapsed ? 0 : 1 });
            });

            $('#profile-toggle').click(function() {
                $('#profile-dropdown').toggleClass('hidden');
            });
        });
    </script>
</body>
</html>
?>