<?php
require_once 'db_connect.php';

function registerUser($pdo, $email, $password, $role = 'buyer', $student_id = null) {
    if (empty($email) || empty($password) || !in_array($role, ['buyer', 'vendor', 'admin'])) {
        return ['success' => false, 'error' => 'Invalid input'];
    }
    if ($role === 'vendor' && empty($student_id)) {
        return ['success' => false, 'error' => 'Student ID required for vendors'];
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'error' => 'Email already registered'];
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, 'active')");
    $stmt->execute([$email, $hashed_password, $role]);
    $user_id = $pdo->lastInsertId();

    if ($role === 'vendor') {
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, student_id, name, verified) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $student_id, $email, false]);
    }

    return ['success' => true, 'user_id' => $user_id];
}

function loginUser($pdo, $email, $password) {
    error_log("Login attempt for email: $email");

    $stmt = $pdo->prepare("SELECT user_id, email, password, role FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        error_log("No user found for email: $email");
        return ['success' => false, 'error' => 'Invalid email or password'];
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        error_log("Login successful for user_id: {$user['user_id']}, role: {$user['role']}");
        return ['success' => true, 'role' => $user['role']];
    }

    error_log("Password verification failed for email: $email");
    return ['success' => false, 'error' => 'Invalid email or password'];
}

function logoutUser() {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

function checkAdmin() {
    error_log("checkAdmin: Session data: " . print_r($_SESSION, true));
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        error_log("checkAdmin failed: Redirecting to index.php");
        session_unset();
        header('Location: ../index.php?error=' . urlencode('Please log in as an admin'));
        exit;
    }
}

function checkVendor() {
    error_log("checkVendor: Session data: " . print_r($_SESSION, true));
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') {
        error_log("checkVendor failed: Redirecting to index.php");
        session_unset();
        header('Location: ../index.php?error=' . urlencode('Please log in as a vendor'));
        exit;
    }
}







function checkLogin($email, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT user_id, email, password, role FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}




function checkBuyer() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        header('Location: ../index.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

?>