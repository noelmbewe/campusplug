<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../admin_functions.php';

isAdminLoggedIn();
echo json_encode(getAllVendors($GLOBALS['pdo']));

function getAllVendors($pdo) {
    $stmt = $pdo->query("
        SELECT v.vendor_id, v.name, v.student_id, v.verified, u.email
        FROM vendors v
        JOIN users u ON v.user_id = u.user_id
    ");
    $vendors = $stmt->fetchAll();
    return $vendors ?: [];
}
?>