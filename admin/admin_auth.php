<?php
session_start();
require_once '../db_connect.php';

function isAdminLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        die(json_encode(['error' => 'Unauthorized access']));
    }
    return true;
}
?>