<?php
require_once 'user_functions.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'buyer';
    $student_id = trim($_POST['student_id'] ?? '');
    $result = registerUser($GLOBALS['pdo'], $email, $password, $role, $student_id);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Register for CampusPlug</h2>
        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Role</label>
                <select name="role" id="role" class="w-full p-2 border rounded">
                    <option value="buyer">Buyer</option>
                    <option value="vendor">Vendor</option>
                </select>
            </div>
            <div id="vendor-fields" class="hidden">
                <label class="block text-sm font-medium">Student ID</label>
                <input type="text" name="student_id" id="student_id" class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Register</button>
        </form>
        <p class="mt-4 text-center">Already have an account? <a href="index.php" class="text-blue-500 hover:underline">Login</a></p>
    </div>
    <script>
        document.getElementById('role').addEventListener('change', function() {
            const vendorFields = document.getElementById('vendor-fields');
            const studentId = document.getElementById('student_id');
            if (this.value === 'vendor') {
                vendorFields.classList.remove('hidden');
                studentId.setAttribute('required', 'required');
            } else {
                vendorFields.classList.add('hidden');
                studentId.removeAttribute('required');
            }
        });
    </script>
</body>
</html>
?>