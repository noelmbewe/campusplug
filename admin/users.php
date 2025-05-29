<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$users = $admin->getAllUsers();
$toast_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $result = $admin->createUser($_POST['email'], $_POST['password'], $_POST['role']);
            $toast_message = $result ? 'User created successfully.' : 'Error creating user.';
        } elseif ($_POST['action'] === 'edit') {
            $result = $admin->updateUser((int)$_POST['user_id'], $_POST['email'], $_POST['role'], $_POST['status']);
            $toast_message = $result ? 'User updated successfully.' : 'Error updating user.';
        } elseif ($_POST['action'] === 'suspend') {
            $result = $admin->suspendUser((int)$_POST['user_id'], $_POST['status']);
            $toast_message = $result ? 'User status updated.' : 'Error updating user status.';
        }
    }
    header('Location: users.php?toast=' . urlencode($toast_message));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - CampusPlug</title>
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
                    <h3 class="text-lg font-semibold">Users</h3>
                    <button id="create-user-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"><i class="fas fa-plus mr-2"></i>Add User</button>
                </div>
                <?php if (empty($users)): ?>
                    <p class="text-gray-500">No users found.</p>
                <?php else: ?>
                    <table id="users-table" class="w-full">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2">ID</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="p-2"><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                    <td class="p-2 flex space-x-2">
                                        <button class="bg-yellow-500 text-white px-2 py-1 rounded edit-user" data-id="<?php echo $user['user_id']; ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>"><i class="fas fa-edit"></i></button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="suspend">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $user['status'] === 'active' ? 'suspended' : 'active'; ?>">
                                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"><i class="fas fa-ban"></i></button>
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

    <!-- Create User Modal -->
    <div id="create-user-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add User</h2>
            <form id="create-user-form" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Password</label>
                    <input type="password" name="password" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Role</label>
                    <select name="role" class="w-full p-2 border rounded">
                        <option value="buyer">Buyer</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form id="edit-user-form" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" id="edit-email" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Role</label>
                    <select name="role" id="edit-role" class="w-full p-2 border rounded">
                        <option value="buyer">Buyer</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Status</label>
                    <select name="status" id="edit-status" class="w-full p-2 border rounded">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTables
            $('#users-table').DataTable({
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

            $('#create-user-btn').click(function() {
                openModal('#create-user-modal');
            });

            $('.edit-user').click(function() {
                $('#edit-user-id').val($(this).data('id'));
                $('#edit-email').val($(this).data('email'));
                $('#edit-role').val($(this).data('role'));
                $('#edit-status').val($(this).data('status'));
                openModal('#edit-user-modal');
            });

            $('.close').click(function() {
                closeModal('#create-user-modal');
                closeModal('#edit-user-modal');
            });

            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModal('#create-user-modal');
                    closeModal('#edit-user-modal');
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