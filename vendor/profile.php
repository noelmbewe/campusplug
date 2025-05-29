<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$profile = $vendor->getProfile();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Vendor Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <h3 class="text-2xl font-semibold mb-6">Vendor Profile</h3>
                <div class="bg-white rounded-lg shadow p-6 max-w-lg">
                    <form id="profile-form">
                        <div class="mb-4">
                            <label class="block text-sm font-medium">Name</label>
                            <input type="text" name="name" id="profile-name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" class="w-full p-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium">Student ID</label>
                            <input type="text" name="student_id" id="profile-student-id" value="<?php echo htmlspecialchars($profile['student_id'] ?? ''); ?>" class="w-full p-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium">Bio</label>
                            <textarea name="bio" id="profile-bio" class="w-full p-2 border rounded"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium">Contact Info</label>
                            <input type="text" name="contact_info" id="profile-contact" value="<?php echo htmlspecialchars($profile['contact_info'] ?? ''); ?>" class="w-full p-2 border rounded">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        $(document).ready(function() {
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

            // Profile Form Submission
            $('#profile-form').submit(function(e) {
                e.preventDefault();
                $.post('profile_actions.php?action=update', $(this).serialize(), function(response) {
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