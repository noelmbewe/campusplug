<?php
class VendorManager {
    private $pdo;
    private $vendor_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->vendor_id = $this->getVendorId($user_id);
    }

    private function getVendorId($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT vendor_id FROM vendors WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getVendorId error: " . $e->getMessage());
            return false;
        }
    }

    public function getVendorStats() {
        $stats = [];
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = ?");
            $stmt->execute([$this->vendor_id]);
            $stats['total_products'] = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE vendor_id = ?");
            $stmt->execute([$this->vendor_id]);
            $stats['total_orders'] = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE vendor_id = ? AND status = 'completed'");
            $stmt->execute([$this->vendor_id]);
            $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
        } catch (PDOException $e) {
            error_log("getVendorStats error: " . $e->getMessage());
        }
        return $stats;
    }

    public function getProducts() {
        try {
            // Check if listing_type column exists
            $stmt = $this->pdo->query("SHOW COLUMNS FROM products LIKE 'listing_type'");
            $hasListingType = $stmt->rowCount() > 0;
            $select = $hasListingType ? "p.listing_type" : "'sale' AS listing_type";

            $stmt = $this->pdo->prepare("SELECT p.product_id, p.name, p.description, p.price, $select, c.name AS category
                                         FROM products p
                                         LEFT JOIN categories c ON p.category_id = c.category_id
                                         WHERE p.vendor_id = ?");
            $stmt->execute([$this->vendor_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("getProducts error: " . $e->getMessage());
            return [];
        }
    }

    public function createProduct($name, $description, $price, $category_id, $listing_type) {
        try {
            // Validate listing_type
            $listing_type = in_array($listing_type, ['sale', 'rent']) ? $listing_type : 'sale';
            $stmt = $this->pdo->prepare("INSERT INTO products (vendor_id, category_id, name, description, price, listing_type)
                                         VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$this->vendor_id, $category_id, $name, $description, $price, $listing_type]);
        } catch (PDOException $e) {
            error_log("createProduct error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProduct($product_id, $name, $description, $price, $category_id, $listing_type) {
        try {
            // Validate listing_type
            $listing_type = in_array($listing_type, ['sale', 'rent']) ? $listing_type : 'sale';
            $stmt = $this->pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, listing_type = ?
                                         WHERE product_id = ? AND vendor_id = ?");
            return $stmt->execute([$name, $description, $price, $category_id, $listing_type, $product_id, $this->vendor_id]);
        } catch (PDOException $e) {
            error_log("updateProduct error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct($product_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE product_id = ? AND vendor_id = ?");
            return $stmt->execute([$product_id, $this->vendor_id]);
        } catch (PDOException $e) {
            error_log("deleteProduct error: " . $e->getMessage());
            return false;
        }
    }

    public function getOrders() {
        try {
            $stmt = $this->pdo->prepare("SELECT o.order_id, o.total_amount, o.status, o.created_at, u.email
                                         FROM orders o
                                         JOIN users u ON o.user_id = u.user_id
                                         WHERE o.vendor_id = ?");
            $stmt->execute([$this->vendor_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("getOrders error: " . $e->getMessage());
            return [];
        }
    }

    public function updateOrderStatus($order_id, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND vendor_id = ?");
            return $stmt->execute([$status, $order_id, $this->vendor_id]);
        } catch (PDOException $e) {
            error_log("updateOrderStatus error: " . $e->getMessage());
            return false;
        }
    }

    public function getProfile() {
        try {
            $stmt = $this->pdo->prepare("SELECT name, student_id, bio, contact_info FROM vendors WHERE vendor_id = ?");
            $stmt->execute([$this->vendor_id]);
            return $stmt->fetch() ?: ['name' => '', 'student_id' => '', 'bio' => '', 'contact_info' => ''];
        } catch (PDOException $e) {
            error_log("getProfile error: " . $e->getMessage());
            return ['name' => '', 'student_id' => '', 'bio' => '', 'contact_info' => ''];
        }
    }

    public function updateProfile($name, $student_id, $bio, $contact_info) {
        try {
            $stmt = $this->pdo->prepare("UPDATE vendors SET name = ?, student_id = ?, bio = ?, contact_info = ?
                                         WHERE vendor_id = ?");
            return $stmt->execute([$name, $student_id, $bio, $contact_info, $this->vendor_id]);
        } catch (PDOException $e) {
            error_log("updateProfile error: " . $e->getMessage());
            return false;
        }
    }

    public function getCategories() {
        try {
            $stmt = $this->pdo->query("SELECT category_id, name FROM categories");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("getCategories error: " . $e->getMessage());
            return [];
        }
    }
}
?>
</xai_validator>


<xaiArtifact artifact_id="5c0f45b0-9970-4c74-9809-3ed69a8168ad" artifact_version_id="2c421aea-d4ee-41cb-8198-045b2cbe7402" title="vendor_dashboard.php" contentType="text/php">
<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../vendor_manager.php';
checkVendor();
$vendor = new VendorManager($pdo, $_SESSION['user_id']);
$stats = $vendor->getVendorStats();
$products = $vendor->getProducts();
$orders = $vendor->getOrders();
$categories = $vendor->getCategories();
?>
<!-- Rest of the file remains unchanged -->