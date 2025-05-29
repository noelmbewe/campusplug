<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$categories = $admin->getCategories();
$toast_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $result = $admin->addCategory($_POST['name'], $_POST['description']);
            $toast_message = $result ? 'Category added successfully.' : 'Error adding category.';
        } elseif ($_POST['action'] === 'edit') {
            $result = $admin->updateCategory((int)$_POST['category_id'], $_POST['name'], $_POST['description']);
            $toast_message = $result ? 'Category updated successfully.' : 'Error updating category.';
        } elseif ($_POST['action'] === 'delete') {
            $result = $admin->deleteCategory((int)$_POST['category_id']);
            $toast_message = $result ? 'Category deleted.' : 'Error deleting category.';
        }
    }
    header('Location: settings.php?toast=' . urlencode($toast_message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CampusPlug</title>
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
                    <h3 class="text-lg font-semibold">Categories</h3>
                    <button id="create-category-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"><i class="fas fa-plus mr-2"></i>Add Category</button>
                </div>
                <?php if (empty($categories)): ?>
                    <p class="text-gray-500">No categories found.</p>
                <?php else: ?>
                    <table id="categories-table" class="w-full">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2">ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="p-2"><?php echo htmlspecialchars($category['category_id']); ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td class="p-2 flex space-x-2">
                                        <button class="bg-yellow-500 text-white px-2 py-1 rounded edit-category" data-id="<?php echo $category['category_id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>" data-description="<?php echo htmlspecialchars($category['description']); ?>"><i class="fas fa-edit"></i></button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
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

    <!-- Create Category Modal -->
    <div id="create-category-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Category</h2>
            <form id="create-category-form" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" class="w-full p-2 border rounded"></textarea>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Category</h2>
            <form id="edit-category-form" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" id="category-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" id="category-name" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Description</label>
                    <textarea name="description" id="category-description" class="w-full p-2 border rounded"></textarea>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTables
            $('#categories-table').DataTable({
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

            $('#create-category-btn').click(function() {
                openModal('#create-category-modal');
            });

            $('.edit-category').click(function() {
                $('#category-id').val($(this).data('id'));
                $('#category-name').val($(this).data('name'));
                $('#category-description').val($(this).data('description'));
                openModal('#edit-category-modal');
            });

            $('.close').click(function() {
                closeModal('#create-category-modal');
                closeModal('#edit-category-modal');
            });

            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModal('#create-category-modal');
                    closeModal('#edit-category-modal');
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