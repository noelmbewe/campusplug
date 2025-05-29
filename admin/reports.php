<?php
require_once '../user_functions.php';
require_once 'admin_manager.php';
checkAdmin();
$admin = new AdminManager($GLOBALS['pdo']);
$reports = $admin->getReports();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['status'])) {
    $admin->resolveReport((int)$_POST['report_id'], $_POST['status']);
    header('Location: reports.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CampusPlug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen">
    <?php include '../include/sidebar.php'; ?>
    <div class="flex-1 ml-64">
        <?php include '../include/navbar.php'; ?>
        <main class="p-6">
            <div class="bg-white rounded shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Reports</h3>
                <?php if (empty($reports)): ?>
                    <p>No reports found.</p>
                <?php else: ?>
                    <table class="w-full border">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2">ID</th>
                                <th>Reporter</th>
                                <th>Target</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td class="p-2"><?php echo htmlspecialchars($report['report_id']); ?></td>
                                    <td><?php echo htmlspecialchars($report['reporter_email']); ?></td>
                                    <td><?php echo htmlspecialchars($report['target_type'] . ' ' . $report['target_id']); ?></td>
                                    <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($report['status']); ?></td>
                                    <td>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Resolve</button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <input type="hidden" name="status" value="dismissed">
                                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Dismiss</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
?>