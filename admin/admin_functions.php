<?php
require_once '../db_connect.php';
require_once 'admin_auth.php';

function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT user_id, email, role, status, created_at FROM users");
    $users = $stmt->fetchAll();
    return $users ?: [];
}

function getVendorDetails($pdo, $vendor_id) {
    $stmt = $pdo->prepare("
        SELECT v.vendor_id, v.name, v.student_id, v.bio, v.profile_picture, v.contact_number, v.verified, u.email
        FROM vendors v
        JOIN users u ON v.user_id = u.user_id
        WHERE v.vendor_id = ?
    ");
    $stmt->execute([$vendor_id]);
    $vendor = $stmt->fetch();
    return $vendor ?: null;
}

function getAllVendors($pdo) {
    $stmt = $pdo->query("
        SELECT v.vendor_id, v.name, v.student_id, v.verified, u.email
        FROM vendors v
        JOIN users u ON v.user_id = u.user_id
    ");
    $vendors = $stmt->fetchAll();
    return $vendors ?: [];
}

function verifyVendor($pdo, $vendor_id, $verified) {
    $stmt = $pdo->prepare("UPDATE vendors SET verified = ? WHERE vendor_id = ?");
    $stmt->execute([$verified, $vendor_id]);
    logAdminAction($pdo, $_SESSION['user_id'], 'verify_vendor', $vendor_id, $verified ? 'Verified vendor' : 'Unverified vendor');
    return $stmt->rowCount() > 0;
}

function removeListing($pdo, $product_id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    logAdminAction($pdo, $_SESSION['user_id'], 'remove_listing', $product_id, 'Removed product listing');
    return $stmt->rowCount() > 0;
}

function getReports($pdo) {
    $stmt = $pdo->query("
        SELECT r.report_id, r.reporter_id, r.target_id, r.target_type, r.reason, r.status, r.created_at, u.email AS reporter_email
        FROM reports r
        JOIN users u ON r.reporter_id = u.user_id
    ");
    $reports = $stmt->fetchAll();
    return $reports ?: [];
}

function resolveReport($pdo, $report_id, $status) {
    $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $stmt->execute([$status, $report_id]);
    logAdminAction($pdo, $_SESSION['user_id'], 'resolve_report', $report_id, "Report $status");
    return $stmt->rowCount() > 0;
}

function getPlatformStats($pdo) {
    $stats = [];
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_vendors'] = $pdo->query("SELECT COUNT(*) FROM vendors WHERE verified = 1")->fetchColumn();
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    return $stats;
}

function logAdminAction($pdo, $admin_id, $action_type, $target_id, $description) {
    $stmt = $pdo->prepare("
        INSERT INTO admin_actions (admin_id, action_type, target_id, description, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$admin_id, $action_type, $target_id, $description]);
}

function addCategory($pdo, $name, $description) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    logAdminAction($pdo, $_SESSION['user_id'], 'update_system', null, "Added category: $name");
    return $stmt->rowCount() > 0;
}

function suspendUser($pdo, $user_id, $status) {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->execute([$status, $user_id]);
    logAdminAction($pdo, $_SESSION['user_id'], 'suspend_user', $user_id, $status === 'suspended' ? 'Suspended user' : 'Reactivated user');
    return $stmt->rowCount() > 0;
}
?>