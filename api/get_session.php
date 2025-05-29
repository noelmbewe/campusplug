<?php
header('Content-Type: application/json');
session_start();

echo json_encode([
    'isLoggedIn' => isset($_SESSION['user_id']),
    'email' => $_SESSION['email'] ?? null,
    'role' => $_SESSION['role'] ?? null
]);
?> 