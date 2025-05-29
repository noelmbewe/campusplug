<?php
header('Content-Type: application/json');
require_once 'user_functions.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$result = loginUser($GLOBALS['pdo'], $email, $password);
echo json_encode($result);
?>