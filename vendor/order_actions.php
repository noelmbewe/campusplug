<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$action = $_POST['action'] ?? '';

try {
    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'processing', 'completed', 'cancelled']) && $vendor->updateOrderStatus($id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Order status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>