<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT product_id, name, description, price, category_id, listing_type
                               FROM products WHERE product_id = ? AND vendor_id = ?");
        $stmt->execute([$id, $vendor->getVendorId($_SESSION['user_id'])]);
        echo json_encode($stmt->fetch());
    } elseif ($action === 'create') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $category_id = $_POST['category_id'] ?? 0;
        $listing_type = $_POST['listing_type'] ?? 'sale';
        if ($vendor->createProduct($name, $description, $price, $category_id, $listing_type)) {
            echo json_encode(['success' => true, 'message' => 'Product created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create product']);
        }
    } elseif ($action === 'update') {
        $id = $_POST['product_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $category_id = $_POST['category_id'] ?? 0;
        $listing_type = $_POST['listing_type'] ?? 'sale';
        if ($vendor->updateProduct($id, $name, $description, $price, $category_id, $listing_type)) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($vendor->deleteProduct($id)) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>