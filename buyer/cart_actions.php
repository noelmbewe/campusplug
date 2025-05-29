<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../buyer_manager.php';
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$action = $_POST['action'] ?? '';

try {
    if ($action) {
        case 'add':
            $product_id = $_POST['product_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            $listing_type = $_POST['listing_type'] ?? 'sale';
            if ($buyer->addToCart($product_id, $quantity, $listing_type)) {
                echo json_encode(['success' => true, 'message' => 'Added to cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
            }
            break;

        case 'update':
            $cart_item_id = $_POST['cart_item_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;
            if ($buyer->updateCartItem($cart_item_id, $quantity)) {
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
            break;

        case 'remove':
            $cart_item_id = $_POST['cart_item_id'] ?? 0;
            if ($buyer->removeCartItem($cart_id_item_id))) {
                echo json_encode(['success' => true, 'message' => 'Item removed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
            break;

        case 'checkout':
            $shipping_address = $_POST['address'] ?? '';
            $payment_details = [
                'card_number' => $_POST['card_number'] ?? '',
                'expiry' => $_POST['expiry'] ?? '',
                'cvc' => $_POST['cvc'] ?? ''
            ];
            $order_id = $buyer->checkout($shipping_address, $payment_details);
            if ($order_id) {
                echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Checkout failed']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>