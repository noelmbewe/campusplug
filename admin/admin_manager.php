<?php
require_once '../db_connect.php';

class AdminManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT user_id, email, role, status, created_at FROM users");
        return $stmt->fetchAll() ?: [];
    }

    public function createUser($email, $password, $role) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$email, $hashed_password, $role]);
        $user_id = $this->pdo->lastInsertId();
        $this->logAdminAction($_SESSION['user_id'], 'create_user', $user_id, "Created user: $email");
        return $user_id > 0;
    }

    public function updateUser($user_id, $email, $role, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET email = ?, role = ?, status = ? WHERE user_id = ?");
        $stmt->execute([$email, $role, $status, $user_id]);
        $this->logAdminAction($_SESSION['user_id'], 'update_user', $user_id, "Updated user: $email");
        return $stmt->rowCount() > 0;
    }

    public function suspendUser($user_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$status, $user_id]);
        $this->logAdminAction($_SESSION['user_id'], 'suspend_user', $user_id, $status === 'suspended' ? 'Suspended user' : 'Reactivated user');
        return $stmt->rowCount() > 0;
    }

    public function getAllVendors() {
        $stmt = $this->pdo->query("
            SELECT v.vendor_id, v.name, v.student_id, v.verified, u.email
            FROM vendors v
            JOIN users u ON v.user_id = u.user_id
        ");
        return $stmt->fetchAll() ?: [];
    }

    public function getVendorDetails($vendor_id) {
        $stmt = $this->pdo->prepare("
            SELECT v.vendor_id, v.name, v.student_id, v.bio, v.profile_picture, v.contact_number, v.verified, u.email
            FROM vendors v
            JOIN users u ON v.user_id = u.user_id
            WHERE v.vendor_id = ?
        ");
        $stmt->execute([$vendor_id]);
        return $stmt->fetch() ?: null;
    }

    public function createVendor($user_id, $name, $student_id) {
        $stmt = $this->pdo->prepare("INSERT INTO vendors (user_id, name, student_id, verified) VALUES (?, ?, ?, 0)");
        $stmt->execute([$user_id, $name, $student_id]);
        $vendor_id = $this->pdo->lastInsertId();
        $this->logAdminAction($_SESSION['user_id'], 'create_vendor', $vendor_id, "Created vendor: $name");
        return $vendor_id > 0;
    }

    public function updateVendor($vendor_id, $name, $student_id, $bio, $contact_number) {
        $stmt = $this->pdo->prepare("UPDATE vendors SET name = ?, student_id = ?, bio = ?, contact_number = ? WHERE vendor_id = ?");
        $stmt->execute([$name, $student_id, $bio, $contact_number, $vendor_id]);
        $this->logAdminAction($_SESSION['user_id'], 'update_vendor', $vendor_id, "Updated vendor: $name");
        return $stmt->rowCount() > 0;
    }

    public function verifyVendor($vendor_id, $verified) {
        $stmt = $this->pdo->prepare("UPDATE vendors SET verified = ? WHERE vendor_id = ?");
        $stmt->execute([$verified, $vendor_id]);
        $this->logAdminAction($_SESSION['user_id'], 'verify_vendor', $vendor_id, $verified ? 'Verified vendor' : 'Unverified vendor');
        return $stmt->rowCount() > 0;
    }

    public function getAllListings() {
        $stmt = $this->pdo->query("
            SELECT p.product_id, p.name, p.type, p.price, p.description, v.name AS vendor_name
            FROM products p
            JOIN vendors v ON p.vendor_id = v.vendor_id
        ");
        return $stmt->fetchAll() ?: [];
    }

    public function createListing($vendor_id, $category_id, $name, $description, $price, $type) {
        $stmt = $this->pdo->prepare("INSERT INTO products (vendor_id, category_id, name, description, price, type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$vendor_id, $category_id, $name, $description, $price, $type]);
        $product_id = $this->pdo->lastInsertId();
        $this->logAdminAction($_SESSION['user_id'], 'create_listing', $product_id, "Created listing: $name");
        return $product_id > 0;
    }

    public function updateListing($product_id, $name, $description, $price, $type) {
        $stmt = $this->pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, type = ? WHERE product_id = ?");
        $stmt->execute([$name, $description, $price, $type, $product_id]);
        $this->logAdminAction($_SESSION['user_id'], 'update_listing', $product_id, "Updated listing: $name");
        return $stmt->rowCount() > 0;
    }

    public function removeListing($product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $this->logAdminAction($_SESSION['user_id'], 'remove_listing', $product_id, 'Removed product listing');
        return $stmt->rowCount() > 0;
    }

    public function getReports() {
        $stmt = $this->pdo->query("
            SELECT r.report_id, r.reporter_id, r.target_id, r.target_type, r.reason, r.status, r.created_at, u.email AS reporter_email
            FROM reports r
            JOIN users u ON r.reporter_id = u.user_id
        ");
        return $stmt->fetchAll() ?: [];
    }

    public function resolveReport($report_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
        $stmt->execute([$status, $report_id]);
        $this->logAdminAction($_SESSION['user_id'], 'resolve_report', $report_id, "Report $status");
        return $stmt->rowCount() > 0;
    }

    public function getPlatformStats() {
        $stats = [];
        $stats['total_users'] = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_vendors'] = $this->pdo->query("SELECT COUNT(*) FROM vendors WHERE verified = 1")->fetchColumn();
        $stats['total_orders'] = $this->pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['total_products'] = $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        return $stats;
    }

    public function getCategories() {
        $stmt = $this->pdo->query("SELECT category_id, name, description FROM categories");
        return $stmt->fetchAll() ?: [];
    }

    public function addCategory($name, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $this->logAdminAction($_SESSION['user_id'], 'update_system', null, "Added category: $name");
        return $stmt->rowCount() > 0;
    }

    public function updateCategory($category_id, $name, $description) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
        $stmt->execute([$name, $description, $category_id]);
        $this->logAdminAction($_SESSION['user_id'], 'update_category', $category_id, "Updated category: $name");
        return $stmt->rowCount() > 0;
    }

    public function deleteCategory($category_id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $this->logAdminAction($_SESSION['user_id'], 'delete_category', $category_id, 'Deleted category');
        return $stmt->rowCount() > 0;
    }

    private function logAdminAction($admin_id, $action_type, $target_id, $description) {
        $stmt = $this->pdo->prepare("
            INSERT INTO admin_actions (admin_id, action_type, target_id, description, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$admin_id, $action_type, $target_id, $description]);
    }
}
?>