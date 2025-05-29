<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$vendors = $admin->getAllVendors();
$users = $admin->getAllUsers();
$toast_message = '';
$vendor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $result = $admin->createVendor((int)$_POST['user_id'], $_POST['name'], $_POST['student_id']);
            $toast_message = $result ? 'Vendor created successfully.' : 'Error creating vendor.';
        } elseif ($_POST['action'] === 'edit') {
            $result = $admin->updateVendor((int)$_POST['vendor_id'], $_POST['name'], $_POST['student_id'], $_POST['bio'], $_POST['contact_number']);
            $toast_message = $result ? 'Vendor updated successfully.' : 'Error updating vendor.';
        } elseif ($_POST['action'] === 'verify') {
            $result = $admin->verifyVendor((int)$_POST['vendor_id'], (int)$_POST['verified']);
            $toast_message = $result ? 'Vendor verification updated.' : 'Error updating verification.';
        }
    }
    header('Location: vendors.php?toast=' . urlencode($toast_message));
    exit;
}

if (isset($_GET['vendor_id'])) {
    $vendor = $admin->getVendorDetails((int)$_GET['vendor_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - CampusPlug</title>
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
                <?php if ($vendor): ?>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Vendor Details</h3>
                        <a href="vendors.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                    </div>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($vendor['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($vendor['email']); ?></p>
                    <p><strong>Student ID:</strong> <?php echo htmlspecialchars($vendor['student_id']); ?></p>
                    <p><strong>Bio:</strong> <?php echo htmlspecialchars($vendor['bio'] ?? 'N/A'); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($vendor['contact_number'] ?? 'N/A'); ?></p>
                    <p><strong>Verified:</strong> <?php echo $vendor['verified'] ? 'Yes' : 'No'; ?></p>
                    <div class="mt-4 flex space-x-2">
                        <button class="bg-yellow-500 text-white px-4 py-2 rounded edit-vendor" data-id="<?php echo $vendor['vendor_id']; ?>" data-name="<?php echo htmlspecialchars($vendor['name']); ?>" data-student-id="<?php echo htmlspecialchars($vendor['student_id']); ?>" data-bio="<?php echo htmlspecialchars($vendor['bio'] ?? ''); ?>" data-contact="<?php echo htmlspecialchars($vendor['contact_number'] ?? ''); ?>"><i class="fas fa-edit mr-2"></i>Edit</button>
                        <form method="POST">
                            <input type="hidden" name="action" value="verify">
                            <input type="hidden" name="vendor_id" value="<?php echo $vendor['vendor_id']; ?>">
                            <input type="hidden" name="verified" value="<?php echo $vendor['verified'] ? 0 : 1; ?>">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded"><i class="fas fa-check mr-2"></i><?php echo $vendor['verified'] ? 'Unverify' : 'Verify'; ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Vendors</h3>
                        <button id="create-vendor-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"><i class="fas fa-plus mr-2"></i>Add Vendor</button>
                    </div>
                    <?php if (empty($vendors)): ?>
                        <p class="text-gray-500">No vendors found.</p>
                    <?php else: ?>
                        <table id="vendors-table" class="w-full">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="p-2">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Student ID</th>
                                    <th>Verified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendors as $vendor): ?>
                                    <tr>
                                        <td class="p-2"><?php echo htmlspecialchars($vendor['vendor_id']); ?></td>
                                        <td><?php echo htmlspecialchars($vendor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($vendor['student_id']); ?></td>
                                        <td><?php echo $vendor['verified'] ? 'Yes' : 'No'; ?></td>
                                        <td class="p-2 flex space-x-2">
                                            <a href="?vendor_id=<?php echo $vendor['vendor_id']; ?>" class="bg-blue-500 text-white px-2 py-1 rounded"><i class="fas fa-eye"></i></a>
                                            <button class="bg-yellow-500 text-white px-2 py-1 rounded edit-vendor" data-id="<?php echo $vendor['vendor_id']; ?>" data-name="<?php echo htmlspecialchars($vendor['name']); ?>" data-student-id="<?php echo htmlspecialchars($vendor['student_id']); ?>" data-bio="" data-contact=""><i class="fas fa-edit"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create Vendor Modal -->
    <div id="create-vendor-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Vendor</h2>
            <form id="create-vendor-form" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="mb-4">
                    <label class="block text-sm font-medium">User</label>
                    <select name="user_id" class="w-full p-2 border rounded" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['email']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Student ID</label>
                    <input type="text" name="student_id" class="w-full p-2 border rounded" required>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
            </form>
        </div>
    </div>

    <!-- Edit Vendor Modal -->
    <div id="edit-vendor-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Vendor</h2>
            <form id="edit-vendor-form" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="vendor_id" id="vendor-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" id="vendor-name" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Student ID</label>
                    <input type="text" name="student_id" id="vendor-student-id" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Bio</label>
                    <textarea name="bio" id="vendor-bio" class="w-full p-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Contact Number</label>
                    <input type="text" name="contact_number" id="vendor-contact" class="w-full p-2 border rounded">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // DataTables
            $('#vendors-table').DataTable({
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

            $('#create-vendor-btn').click(function() {
                openModal('#create-vendor-modal');
            });

            $('.edit-vendor').click(function() {
                $('#vendor-id').val($(this).data('id'));
                $('#vendor-name').val($(this).data('name'));
                $('#vendor-student-id').val($(this).data('student-id'));
                $('#vendor-bio').val($(this).data('bio'));
                $('#vendor-contact').val($(this).data('contact'));
                openModal('#edit-vendor-modal');
            });

            $('.close').click(function() {
                closeModal('#create-vendor-modal');
                closeModal('#edit-vendor-modal');
            });

            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModal('#create-vendor-modal');
                    closeModal('#edit-vendor-modal');
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