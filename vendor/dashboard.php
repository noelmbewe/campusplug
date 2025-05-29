<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$stats = $vendor->getVendorStats();
$products = $vendor->getProducts();
$orders = $vendor->getOrders();
$categories = $vendor->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Vendor Dashboard</title>
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
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 80%; max-width: 500px; border-radius: 8px; }
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
                <h3 class="text-2xl font-semibold mb-6">Vendor Dashboard</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 card">
                        <div class="flex items-center">
                            <i class="fas fa-box text-3xl text-blue-500 mr-4"></i>
                            <div>
                                <h4 class="text-lg font-semibold">Total Products</h4>
                                <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_products'] ?? 0); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 card">
                        <div class="flex items-center">
                            <i class="fas fa-shopping-cart text-3xl text-green-500 mr-4"></i>
                            <div>
                                <h4 class="text-lg font-semibold">Total Orders</h4>
                                <p class="text-2xl font-bold"><?php echo htmlspecialchars($stats['total_orders'] ?? 0); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 card">
                        <div class="flex items-center">
                            <i class="fas fa-dollar-sign text-3xl text-yellow-500 mr-4"></i>
                            <div>
                                <h4 class="text-lg font-semibold">Total Revenue</h4>
                                <p class="text-2xl font-bold">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold">Your Products</h4>
                        <button id="create-product" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Product</button>
                    </div>
                    <table id="products-table" class="display w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category'] ?: 'N/A'); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['listing_type']); ?></td>
                                    <td>
                                        <button class="edit-product bg-yellow-500 text-white px-2 py-1 rounded" data-id="<?php echo $product['product_id']; ?>">Edit</button>
                                        <button class="delete-product bg-red-500 text-white px-2 py-1 rounded" data-id="<?php echo $product['product_id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Orders Table -->
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

    <!-- Product Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <h3 id="modal-title" class="text-xl font-semibold mb-4"></h3>
            <form id="product-form">
                <input type="hidden" name="product_id" id="product-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" id="product-name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" id="product-description" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" name="price" id="product-price" step="0.01" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category_id" id="product-category" class="w-full p-2 border rounded" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Listing Type</label>
                    <select name="listing_type" id="product-type" class="w-full p-2 border rounded" required>
                        <option value="sale">Sale</option>
                        <option value="rent">Rent</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="close-modal" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-semibold mb-4">Edit Profile</h3>
            <form id="profile-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" id="profile-name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Student ID</label>
                    <input type="text" name="student_id" id="profile-student-id" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Bio</label>
                    <textarea name="bio" id="profile-bio" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Contact Info</label>
                    <input type="text" name="contact_info" id="profile-contact" class="w-full p-2 border rounded">
                </div>
                <div class="flex justify-end">
                    <button type="button" id="close-profile-modal" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#products-table').DataTable({ order: [[0, 'desc']] });
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

            // Product Modal
            $('#create-product').click(function() {
                $('#modal-title').text('Add Product');
                $('#product-form')[0].reset();
                $('#product-id').val('');
                $('#product-modal').show();
            });

            $('.edit-product').click(function() {
                const id = $(this).data('id');
                $.get('product_actions.php?action=get&id=' + id, function(data) {
                    $('#modal-title').text('Edit Product');
                    $('#product-id').val(data.product_id);
                    $('#product-name').val(data.name);
                    $('#product-description').val(data.description);
                    $('#product-price').val(data.price);
                    $('#product-category').val(data.category_id);
                    $('#product-type').val(data.listing_type);
                    $('#product-modal').show();
                });
            });

            $('#close-modal').click(function() {
                $('#product-modal').hide();
            });

            $('#product-form').submit(function(e) {
                e.preventDefault();
                const data = $(this).serialize() + '&action=' + ($('#product-id').val() ? 'update' : 'create');
                $.post('product_actions.php', data, function(response) {
                    if (response.success) {
                        Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                    }
                });
            });

            $('.delete-product').click(function() {
                if (confirm('Are you sure?')) {
                    const id = $(this).data('id');
                    $.post('product_actions.php', { action: 'delete', id: id }, function(response) {
                        if (response.success) {
                            Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                        }
                    });
                }
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

            // Profile Modal
            $('#edit-profile').click(function() {
                $.get('profile_actions.php?action=get', function(data) {
                    $('#profile-name').val(data.name);
                    $('#profile-student-id').val(data.student_id);
                    $('#profile-bio').val(data.bio);
                    $('#profile-contact').val(data.contact_info);
                    $('#profile-modal').show();
                });
            });

            $('#close-profile-modal').click(function() {
                $('#profile-modal').hide();
            });

            $('#profile-form').submit(function(e) {
                e.preventDefault();
                $.post('profile_actions.php?action=update', $(this).serialize(), function(response) {
                    if (response.success) {
                        Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                        setTimeout(() => location.reload(), 1000);
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