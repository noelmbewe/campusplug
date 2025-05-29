<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$stats = $admin->getPlatformStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats - CampusPlug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen">
    <?php include '../include/sidebar.php'; ?>
    <div class="flex-1 ml-64">
        <?php include '../include/navbar.php'; ?>
        <main class="p-6">
            <div class="bg-white rounded shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Platform Statistics</h3>
                <?php if (empty($stats)): ?>
                    <p>No statistics available.</p>
                <?php else: ?>
                    <p><strong>Total Users:</strong> <?php echo htmlspecialchars($stats['total_users']); ?></p>
                    <p><strong>Total Vendors:</strong> <?php echo htmlspecialchars($stats['total_vendors']); ?></p>
                    <p><strong>Total Orders:</strong> <?php echo htmlspecialchars($stats['total_orders']); ?></p>
                    <p><strong>Total Products:</strong> <?php echo htmlspecialchars($stats['total_products']); ?></p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
?>