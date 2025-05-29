<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';
require_once '../admin_functions.php';

isAdminLoggedIn();
echo json_encode(getAllListings($GLOBALS['pdo']));
?>