<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$listings = $admin->getAllListings();
$vendors = $admin->getAllVendors();
$categories = $admin->getCategories();
$toast_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $result = $admin->createListing((int)$_POST['vendor_id'], (int)$_POST['category_id'], $_POST['name'], $_POST['description'], (float)$_POST['price'], $_POST['type']);
            $toast_message = $result ? 'Listing created successfully.' : 'Error creating listing.';
        } elseif ($_POST['action'] === 'edit') {
            $result = $admin->updateListing((int)$_POST['product_id'], $_POST['name'], $_POST['description'], (float)$_POST['price'], $_POST['type']);
            $toast_message = $result ? 'Listing updated successfully.' : 'Error updating listing.';
        } elseif ($_POST['action'] === 'delete') {
            $result = $admin->removeListing((int)$_POST['product_id']);
            $toast_message = $result ? 'Listing deleted.' : 'Error deleting listing.';
        }
    }
    header('Location: listings.php?toast=' . urlencode($toast_message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings - CampusPlug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 8px; }
        .modal-content h2 { margin-bottom: 20px; }
        .modal-content .close { float: right; font-size: 24px; cursor: pointer; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen">
    <?php include '../include/sidebar.php'; ?>
    <div class="flex-1 ml-64">
        <?php include '../include/navbar.php'; ?>
        <main class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Listings</h3>
                    <button id="create-listing-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"><i class="fas fa-plus mr-2"></i>Add Listing</button>
                </div>
                <?php if (empty($listings)): ?>
                    <p class="text-gray-500">No listings found.</p>
                <?php else: ?>
                    <table id="listings-table" class="w-full">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2">ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Vendor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <tr>
                                    <td class="p-2"><?php echo htmlspecialchars($listing['product_id']); ?></td>
                                    <td><?php echo htmlspecialchars($listing['name']); ?></td>
                                    <td><?php echo htmlspecialchars($listing['type']); ?></td>
                                    <td><?php echo htmlspecialchars($listing['price']); ?></td>
                                    <td><?php echo htmlspecialchars($listing['vendor_name']); ?></td>
                                    <td class="p-2 flex space-x-2">
                                        <button class="bg-yellow-500 text-white px-2 py-1 rounded edit-listing" data-id="<?php echo $listing['product_id']; ?>" data-name="<?php echo htmlspecialchars($listing['name']); ?>" data-description="<?php echo htmlspecialchars($listing['description']); ?>" data-price="<?php echo $listing['price']; ?>" data-type="<?php echo $listing['type']; ?>"><i class="fas fa-edit"></i></button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo $listing['product_id']; ?>">
                                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create Listing Modal -->
    <div id="create-listing-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Listing</h2>
            <form id="create-listing-form" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Vendor</label>
                    <select name="vendor_id" class="w-full p-2 border rounded" required>
                        <option value="">Select Vendor</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo $vendor['vendor_id']; ?>"><?php echo htmlspecialchars($vendor['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Category</label>
                    <select name="category_id" class="w-full p-2 border rounded" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" name="price" step="0.01" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Type</label>
                    <select name="type" class="w-full p-2 border rounded" required>
                        <option value="product">Product</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
            </form>
        </div>
    </div>

    <!-- Edit Listing Modal -->
    <div id="edit-listing-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Listing</h2>
            <form id="edit-listing-form" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" id="listing-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" id="listing-name" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" id="listing-description" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Price</label>
                    <input type="number" name="price" id="listing-price" step="0.01" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Type</label>
                    <select name="type" id="listing-type" class="w-full p-2 border rounded">
                        <option value="product">Product</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTables
            $('#listings-table').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
            });

            // Sidebar Toggle
            $('#toggle-sidebar').click(function() {
                const sidebar = $('#sidebar');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('span').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                $.post('set_sidebar_state.php', { collapsed: collapsed ? 0 : 1 });
            });

            // Profile Dropdown
            $('#profile-toggle').click(function() {
                $('#profile-dropdown').toggleClass('hidden');
            });

            // Modals
            function openModal(modalId) {
                $(modalId).show();
            }
            function closeModal(modalId) {
                $(modalId).hide();
            }

            $('#create-listing-btn').click(function() {
                openModal('#create-listing-modal');
            });

            $('.edit-listing').click(function() {
                $('#listing-id').val($(this).data('id'));
                $('#listing-name').val($(this).data('name'));
                $('#listing-description').val($(this).data('description'));
                $('#listing-price').val($(this).data('price'));
                $('#listing-type').val($(this).data('type'));
                openModal('#edit-listing-modal');
            });

            $('.close').click(function() {
                closeModal('#create-listing-modal');
                closeModal('#edit-listing-modal');
            });

            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModal('#create-listing-modal');
                    closeModal('#edit-listing-modal');
                }
            });

            // Toast Notification
            const urlParams = new URLSearchParams(window.location.search);
            const toast = urlParams.get('toast');
            if (toast) {
                Toastify({
                    text: toast,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: toast.includes('Error') ? '#e74c3c' : '#2ecc71',
                }).showToast();
            }
        });
    </script>
</body>
</html>
?>