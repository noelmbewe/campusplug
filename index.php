<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
require_once 'user_functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $result = loginUser($pdo, $email, $password);
        if ($result['success']) {
            // Debug: Log session data
            error_log("Post-login session: " . print_r($_SESSION, true));
            switch ($result['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'vendor':
                    $redirect = file_exists('vendor/dashboard.php') ? 'vendor/dashboard.php' : 'index.php?error=Vendor+dashboard+not+implemented';
                    header("Location: $redirect");
                    break;
                default: // buyer
                    $redirect = file_exists('buyer/dashboard.php') ? 'buyer/dashboard.php' : 'index.php?error=Buyer+dashboard+not+implemented';
                    header("Location: $redirect");
                    break;
            }
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login to CampusPlug</h2>
        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Login</button>
        </form>
        <p class="mt-4 text-center">Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Register</a></p>
    </div>
</body>
</html>
?>