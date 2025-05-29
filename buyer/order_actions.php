<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../buyer_manager.php';
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$action = $_GET['action'] ?? '';

try {
    if ($action === 'details') {
        $order_id = $_GET['id'] ?? 0;
        $order = $pdo->prepare("SELECT order_id, shipping_address FROM orders WHERE order_id = ? AND user_id = ?");
        $order->execute([$order_id, $_SESSION['user_id']]);
        $order_data = $order->fetch(PDO::FETCH_ASSOC);
        $items = $buyer->getOrderItems($order_id);
        echo json_encode(['order' => $order_data, 'items' => $items]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>