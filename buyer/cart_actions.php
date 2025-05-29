<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db_connect.php';
require_once 'buyer_manager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session");
    echo json_encode(['success' => false, 'message' => 'Please log in to manage cart']);
    exit;
}

try {
    $buyer = new BuyerManager($pdo, $_SESSION['user_id']);
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $listing_type = $_POST['listing_type'] ?? 'sale';

            error_log("Received add action: product_id=$product_id, quantity=$quantity, listing_type=$listing_type");

            if ($product_id <= 0 || $quantity <= 0 || !in_array($listing_type, ['sale', 'rent'])) {
                error_log("Invalid input: product_id=$product_id, quantity=$quantity, listing_type=$listing_type");
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            if ($buyer->addToCart($product_id, $quantity, $listing_type)) {
                $cart_count = count($buyer->getCart());
                error_log("Added to cart: cart_count=$cart_count");
                echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => $cart_count]);
            } else {
                error_log("Failed to add to cart");
                echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
            }
            break;

        case 'update':
            $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($cart_item_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            if ($buyer->updateCartItem($cart_item_id, $quantity)) {
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
            break;

        case 'remove':
            $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
            if ($cart_item_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            if ($buyer->removeCartItem($cart_item_id)) {
                echo json_encode(['success' => true, 'message' => 'Item removed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Cart action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>