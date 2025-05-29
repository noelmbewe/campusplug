<?php
  class BuyerManager {
      private $pdo;
      private $user_id;

      public function __construct($pdo, $user_id) {
          $this->pdo = $pdo;
          $this->user_id = $user_id;
      }

      public function getProducts($filters = []) {
          try {
              $query = "SELECT p.product_id, p.name, p.description, COALESCE(p.image, 'https://via.placeholder.com/150') AS image_url, p.price, p.listing_type, c.name AS category
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.vendor_id IN (SELECT vendor_id FROM vendors WHERE verified = true)";
              $params = [];

              if (!empty($filters['category_id'])) {
                  $query .= " AND p.category_id = ?";
                  $params[] = $filters['category_id'];
              }
              if (!empty($filters['listing_type'])) {
                  $query .= " AND p.listing_type = ?";
                  $params[] = $filters['listing_type'];
              }
              if (!empty($filters['price_min'])) {
                  $query .= " AND p.price >= ?";
                  $params[] = $filters['price_min'];
              }
              if (!empty($filters['price_max'])) {
                  $query .= " AND p.price <= ?";
                  $params[] = $filters['price_max'];
              }
              if (!empty($filters['search'])) {
                  $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                  $params[] = "%{$filters['search']}%";
                  $params[] = "%{$filters['search']}%";
              }

              $stmt = $this->pdo->prepare($query);
              $stmt->execute($params);
              $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
              error_log("getProducts returned: " . print_r($results, true));
              return $results;
          } catch (PDOException $e) {
              error_log("getProducts error: " . $e->getMessage());
              return [];
          }
      }

      public function getCategories() {
          try {
              $stmt = $this->pdo->query("SELECT category_id, name FROM categories");
              return $stmt->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              error_log("getCategories error: " . $e->getMessage());
              return [];
          }
      }

      public function addToCart($product_id, $quantity, $listing_type) {
          try {
              $this->pdo->beginTransaction();
              error_log("Adding to cart: user_id={$this->user_id}, product_id=$product_id, quantity=$quantity, listing_type=$listing_type");

              // Get or create cart
              $stmt = $this->pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
              $stmt->execute([$this->user_id]);
              $cart_id = $stmt->fetchColumn();

              if (!$cart_id) {
                  $stmt = $this->pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
                  $stmt->execute([$this->user_id]);
                  $cart_id = $this->pdo->lastInsertId();
                  error_log("Created new cart: cart_id=$cart_id");
              }

              // Check if item exists in cart
              $stmt = $this->pdo->prepare("SELECT cart_item_id FROM cart_items WHERE cart_id = ? AND product_id = ? AND listing_type = ?");
              $stmt->execute([$cart_id, $product_id, $listing_type]);
              $cart_item_id = $stmt->fetchColumn();

              if ($cart_item_id) {
                  $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_item_id = ?");
                  $stmt->execute([$quantity, $cart_item_id]);
                  error_log("Updated cart item: cart_item_id=$cart_item_id, new_quantity=" . ($quantity + $existing_quantity));
              } else {
                  $stmt = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, listing_type) VALUES (?, ?, ?, ?)");
                  $stmt->execute([$cart_id, $product_id, $quantity, $listing_type]);
                  error_log("Inserted new cart item: cart_id=$cart_id, product_id=$product_id");
              }

              $this->pdo->commit();
              return true;
          } catch (PDOException $e) {
              $this->pdo->rollBack();
              error_log("addToCart error: " . $e->getMessage() . " | product_id=$product_id, user_id={$this->user_id}");
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

      public function updateCartItem($cart_item_id, $quantity) {
          try {
              if ($quantity <= 0) {
                  return $this->removeCartItem($cart_item_id);
              }
              $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?)");
              return $stmt->execute([$quantity, $cart_item_id, $this->user_id]);
          } catch (PDOException $e) {
              error_log("updateCartItem error: " . $e->getMessage());
              return false;
          }
      }

      public function removeCartItem($cart_item_id) {
          try {
              $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?)");
              return $stmt->execute([$cart_item_id, $this->user_id]);
          } catch (PDOException $e) {
              error_log("removeCartItem error: " . $e->getMessage());
              return false;
          }
      }

      public function checkout($shipping_address, $payment_details) {
          try {
              $this->pdo->beginTransaction();
              $cart_items = $this->getCart();
              if (empty($cart_items)) {
                  return false;
              }

              $total_amount = 0;
              foreach ($cart_items as $item) {
                  $total_amount += $item['price'] * $item['quantity'];
              }
              $total_amount *= 1750; // Convert to MWK
              $tax = $total_amount * 0.1; // 10% tax
              $total_amount += $tax;

              // Simulate payment processing
              if (!$this->processPayment($payment_details, $total_amount)) {
                  $this->pdo->rollBack();
                  return false;
              }

              $stmt = $this->pdo->prepare("INSERT INTO orders (user_id, vendor_id, total_amount, status, created_at, shipping_address) 
                                           SELECT ?, p.vendor_id, ?, 'pending', NOW(), ?
                                           FROM products p 
                                           JOIN cart_items ci ON p.product_id = ci.product_id 
                                           JOIN cart c ON ci.cart_id = c.cart_id 
                                           WHERE c.user_id = ? LIMIT 1");
              $stmt->execute([$this->user_id, $total_amount, $shipping_address, $this->user_id]);
              $order_id = $this->pdo->lastInsertId();

              foreach ($cart_items as $item) {
                  $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, listing_type) 
                                               VALUES (?, ?, ?, ?, ?)");
                  $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'] * 1750, $item['listing_type']]);
              }

              $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?)");
              $stmt->execute([$this->user_id]);

              $this->pdo->commit();
              return $order_id;
          } catch (PDOException $e) {
              $this->pdo->rollBack();
              error_log("checkout error: " . $e->getMessage());
              return false;
          }
      }

      private function processPayment($payment_details, $amount) {
          if (empty($payment_details['method']) || empty($payment_details['phone']) || strlen($payment_details['phone']) < 8) {
              return false;
          }
          return true; // Simulate success
      }

      public function getOrders() {
          try {
              $stmt = $this->pdo->prepare("SELECT order_id, total_amount, status, created_at, shipping_address
                                           FROM orders
                                           WHERE user_id = ? ORDER BY created_at DESC");
              $stmt->execute([$this->user_id]);
              return $stmt->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              error_log("getOrders error: " . $e->getMessage());
              return [];
          }
      }

      public function getOrderItems($order_id) {
          try {
              $stmt = $this->pdo->prepare("SELECT oi.*, p.name, COALESCE(p.image, 'https://via.placeholder.com/150') AS image_url
                                           FROM order_items oi
                                           JOIN products p ON oi.product_id = p.product_id
                                           WHERE oi.order_id = ?");
              $stmt->execute([$order_id]);
              return $stmt->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              error_log("getOrderItems error: " . $e->getMessage());
              return [];
          }
      }
  }
  ?>