<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../admin_functions.php';

isAdminLoggedIn();
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($user_id > 0 && in_array($status, ['active', 'suspended'])) {
    $success = suspendUser($GLOBALS['pdo'], $user_id, $status);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['error' => 'Invalid user ID or status']);
}
?>