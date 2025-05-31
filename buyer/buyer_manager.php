
<?php
class BuyerManager {
    private $pdo;
    private $user_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    public function addToCart($product_id, $quantity, $listing_type) {
        try {
            $this->pdo->beginTransaction();
            error_log("addToCart: user_id={$this->user_id}, product_id=$product_id, quantity=$quantity, listing_type=$listing_type");

            // Get or create cart
            $stmt = $this->pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
            $stmt->execute([$this->user_id]);
            $cart_id = $stmt->fetchColumn();

            if ($cart_id === false) {
                $stmt = $this->pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
                $stmt->execute([$this->user_id]);
                $cart_id = $this->pdo->lastInsertId();
                error_log("Created cart: cart_id=$cart_id");
            }

            // Check existing cart item
            $stmt = $this->pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND listing_type = ?");
            $stmt->execute([$cart_id, $product_id, $listing_type]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                $new_quantity = $cart_item['quantity'] + $quantity;
                $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                $stmt->execute([$new_quantity, $cart_item['cart_item_id']]);
                error_log("Updated cart item: cart_item_id={$cart_item['cart_item_id']}, new_quantity=$new_quantity");
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, listing_type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cart_id, $product_id, $quantity, $listing_type]);
                error_log("Inserted cart item: cart_id=$cart_id, product_id=$product_id");
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("addToCart error: " . $e->getMessage() . " | Code: " . $e->getCode() . " | product_id=$product_id, user_id={$this->user_id}, listing_type=$listing_type");
            return false;
        }
    }

    public function getCart() {
        try {
            $stmt = $this->pdo->prepare("SELECT ci.cart_item_id, ci.product_id, ci.quantity, ci.listing_type, p.name, p.price, COALESCE(p.image, 'https://via.placeholder.com/150') AS image_url
                                         FROM cart_items ci
                                         JOIN cart c ON ci.cart_id = c.cart_id
                                         JOIN products p ON ci.product_id = p.product_id
                                         WHERE c.user_id = ?");
            $stmt->execute([$this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getCart error: " . $e->getMessage());
            return [];
        }
    }

    // Other methods unchanged...
}
?>
```