<?php
header('Content-Type: application/json');
require_once '../db_connect.php'; // Ensure this points to 'db_connect.php'
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']); // Use $_pdo instead of $pdo
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get') {
        echo json_encode($vendor->getProfile());
    } elseif ($action === 'update') {
        $name = $_POST['name'] ?? '';
        $student_id = $_POST['student_id'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $contact_info = $_POST['contact_info'] ?? ''; // Corrected 'contact' to 'contact_info'
        if ($vendor->updateProfile($name, $student_id, $bio, $contact_info)) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>