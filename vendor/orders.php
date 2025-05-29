<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$orders = $vendor->getOrders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Vendor Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="flex flex-1">
        <?php include '../include/vendor_sidebar.php'; ?>
        <div class="flex-1 main-content ml-64 transition-all duration-300" id="main-content">
            <?php include '../include/vendor_navbar.php'; ?>
            <main class="p-6">
                <h3 class="text-2xl font-semibold mb-6">Manage Orders</h3>
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-semibold mb-4">Your Orders</h4>
                    <table id="orders-table" class="display w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                    <td>
                                        <select class="update-order-status" data-id="<?php echo $order['order_id']; ?>">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#orders-table').DataTable({ order: [[0, 'desc']] });

            // Sidebar Toggle
            $('#toggle-sidebar').click(function() {
                const sidebar = $('#sidebar');
                const mainContent = $('#main-content');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('span').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                mainContent.toggleClass('ml-64 ml-20');
            });

            // Mobile Sidebar Toggle
            $('#mobile-menu').click(function() {
                $('#sidebar').toggleClass('open');
            });

            // Profile Dropdown
            $('#profile-toggle').click(function() {
                $('#profile-dropdown').toggleClass('hidden');
            });

            // Order Status Update
            $('.update-order-status').change(function() {
                const id = $(this).data('id');
                const status = $(this).val();
                $.post('order_actions.php', { action: 'update', id: id, status: status }, function(response) {
                    if (response.success) {
                        Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                    } else {
                        Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                    }
                });
            });
        });
    </script>
</body>
</html>
?>