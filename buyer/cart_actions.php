```php
<?php
session_start();
ob_start();
require_once '../db_connect.php';
require_once 'buyer_manager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit;
}

try {
    $buyer = new BuyerManager($pdo, $_SESSION['user_id']);
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $listing_type = $_POST['listing_type'] ?? '';

        error_log("Add to cart: user_id={$_SESSION['user_id']}, product_id=$product_id, quantity=$quantity, listing_type=$listing_type");

        if ($product_id <= 0 || $quantity <= 0 || !in_array($listing_type, ['sale', 'rent'])) {
            error_log("Invalid input: product_id=$product_id, quantity=$quantity, listing_type=$listing_type");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        // Verify product
        $stmt = $pdo->prepare("SELECT product_id, listing_type FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product || $product['listing_type'] !== $listing_type) {
            error_log("Product not found or invalid listing_type: product_id=$product_id, listing_type=$listing_type");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        // Verify user
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            error_log("User not found: user_id={$_SESSION['user_id']}");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        if ($buyer->addToCart($product_id, $quantity, $listing_type)) {
            $cart_count = count($buyer->getCart());
            error_log("Success: cart_count=$cart_count");
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => $cart_count]);
        } else {
            error_log("Failed to add to cart: product_id=$product_id");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Cart action error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
ob_end_flush();
?>
```