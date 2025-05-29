<?php
session_start;
require_once('../db_connect.php');
require_once('../user_functions.php');
require_once('buyer_manager.php');
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$cart_items = $cart_items = $buyer->getCart();
$cart_count = count($cart_items);
$total = array_reduce($cart_items, fn($sum, $item) => $sum + ($item['price'] * 1750 * $item['quantity']), 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['address'] ?? '');
    $payment_details = [
        'method' => $_POST['payment-method'] ?? '',
        'phone' => trim($_POST['payment-phone'] ?? '')
    ];
    if (empty($shipping_address) || empty($payment_details['method']) || empty($payment_details['phone'])) {
        $error = 'All fields are required';
    } else {
        $order_id = $buyer->checkout($shipping_address, $payment_details);
        if ($order_id) {
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit;
        } else {
            $error = 'Checkout failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete your purchase at CampusPlug with Airtel Money or TNM Mpamba.">
    <title>Checkout - CampusPlug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .primary-bg { background-color: #714315; }
        .primary-text { color: #714315; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .bg-gray-100 { background-color: #1a1a1a; }
        .dark .bg-white { background-color: #2d2d2d; }
        .dark .text-gray-800 { color: #e5e5e5e; }
        .cart-badge { background: #714315; color: #fff; }
        .dark .cart-badge { background: #fff; color: #714315; }
        .form-input { transition: border-color 0.3s; }
        .form-input:focus { border-color: #714315; outline: none; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table th, .summary-table td { padding: 0.75rem; text-align: left-align; }
        }
        .summary-table th { background: rgba(255, 255, 255, 0.2); }
        .summary-table td { border-bottom: 1px solid rgba(255,255,255,0.1); }
        @media ((max-width: 640px)) {)
            .summary-table th, .summary-table td { padding: 0.5rem; font-size: 0.875rem; }
            .summary-table img { width: 2.5rem; height: 2.5rem; }
        }}
        .payment-logo { width: 4rem; height: 2rem; object-fit: contain; }
        @media (max-width: 640px) {
            .payment-logo { width: 3rem; height: 1.5rem; }
        }
    </style>
</head>
<body class="bg-gray-100 transition-colors duration-300">
    <div class="flex flex-1">
        <aside id="sidebar" class="bg-[#714315] text-white w-64 h-screen fixed top-0 left-0 flex flex-col transition-transform duration-300 z-50">
            <div class="p-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold">CampusPlug</h1>
                <button id="toggle-sidebar-btn" class="text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="flex-1">
                <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-home mr-2"></i>
                    <span>Shop</span>
                </a>
                <a href="cart.php" class="flex items-center p-4 hover:bg-[#5a330f] bg-[#5a330f] relative">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    <span>Cart</span>
                    <span id="cart-count" class="cart-badge absolute right-4 rounded-full px-2 py-1 text-xs"><?php echo $cart_count; ?></span></td>
                </a>
                <a href="orders.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-box mr-2"></i>
                    <span>Orders</span>
                </a>
                <a href="profile.php" class="flex items-center p-4 hover:bg-[#5a330f]">
                    <i class="fas fa-user mr-2"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>
        <div class="flex-1 main-content ml-64 transition-all duration-300" id="main-content">
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <div class="flex items-center">
                    <div class="flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-600 focus:outline-none md:hidden mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-semibold">Checkout</h2>
                </div>
                <div class="relative">
                    <div class="relative">
                    <button id="profile-toggle-btn" class="flex items-center text-gray-700 focus:outline-none">
                        <i class="fas fa-user-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Buyer'); ?></span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="profile-dropdown-menu" class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded shadow-lg hidden">
                        <a href="dropdown-item" href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">Profile</a>
                        <a href="../logout.php" class="dropdown-item block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </header>
            <section class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Checkout</h2>
                    <?php if (!empty($error)): ?>
                        <div class="bg-red-100 text-red-600 p-4 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form id="order-form" method="POST">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 glass-card p-8 rounded-lg shadow-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-6">Shipping Information</h3>
                                <div class="space-y-5">
                                    <div>
                                        <label for="full-name" class="block text-sm font-medium text-gray-600">Full Name</label>
                                        <input type="text" id="full-name" name="full-name" class="form-input mt-2 w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#714315]" required>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-600">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" class="form-input mt-2 w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#714315]" required>
                                    </div>
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-600">Delivery Address</label>
                                        <textarea id="address" name="address" rows="4" class="form-input mt-2 w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-[#714315]" placeholder="E.g., Daeyang University, Dorm A, Room 12" required></textarea>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mt-8 mb-6">Payment Method</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-4">
                                        <input type="radio" id="airtel-radio" name="payment-method" value="airtel-money" class="h-4 w-4 text-[#714315] focus:ring-[#714315]" checked>
                                        <img src="https://via.placeholder.com/80x40?text=Airtel+Money" alt="Airtel Money" class="payment-logo">
                                        <label for="airtel-radio" class="text-sm font-medium text-gray-600">Airtel Money</label>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input type="radio" id="tnm-radio" name="payment-method" value="tnm-mpamba" class="h-4 w-4 text-[#714315] focus:ring-[#714315]">
                                        <img src="https://via.placeholder.com/80x40?text=TNM+Mpamba" alt="TNM Mpamba" class="payment-logo">
                                        <label for="tnm-radio" class="text-sm font-medium text-gray-600">TNM Mpamba</label>
                                    </div>
                                    <div>
                                        <label for="payment-phone" class="block text-sm font-medium text-gray-600">Payment Phone Number</label>
                                        <input type="tel" id="payment-phone" name="payment-phone" class="form-input mt-2 w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#714315]" required>
                                    </div>
                                </div>
                            </div>
                            <div class="glass-card p-8 rounded-lg shadow-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-6">Order Summary</h3>
                                <table class="summary-table w-full">
                                    <thead>
                                        <tr>
                                            <th class="text-sm font-semibold">Product</th>
                                            <th class="text-sm font-semibold">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order-table">
                                        <?php if (empty($cart_items)): ?>
                                            <tr><td colspan="2" class="text-center text-gray-500">Your cart is empty</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($cart_items as $item): ?>
                                                <tr>
                                                    <td class="flex items-center space-x-3">
                                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></img> class="w-10 h-10 rounded-lg">
                                                        <div>
                                                            <span class="text-sm font-medium"><?php echo htmlspecialchars($item['name']); ?> (<?php echo ucfirst($item['listing_type']); ?>)</span>
                                                            <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="text-sm">K<?php echo number_format($item['price'] * 1750 * $item['quantity'], 0, '', ','); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <div class="flex justify-between text-lg font-semibold text-gray-800">
                                        <span>Total</span>
                                        <span id="order-total">K<?php echo number_format($total, 0, '', ','); ?></span></td>
                                    </div>
                                </div>
                                <button type="submit" id="confirm-order-btn" class="mt-8 w-full bg-[#714315] text-white py-3 rounded-full px-6 hover:bg-[#5a330f] shadow-lg sm:text-sm sm:py-2 sm:px-4 <?php echo empty($cart_items) ? 'pointer-events-none opacity-50' : ''; ?>" disabled="<?php echo empty($cart_items) ? 'true' : 'false'; ?>">">Confirm Order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#toggle-sidebar-btn').click(function() {
                const sidebar = $('#sidebar-content');
                const mainContent = $('#main-content');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('.sidebar-item').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                mainContent.toggleClass('ml-64 ml-20');
            });
            $('#mobile-menu-btn').click(function() {
                $('#sidebar-content').toggleClass('open');
            });
            $('#profile-toggle-btn').click(function() {
                $('#profile-content').toggleClass('active');
            });
            gsap.from('.glass-card', { opacity: 0, y: 50, duration: 0.8, stagger: 0.2 });
            $('#confirm-order-btn').click(function(e) {
                e.preventDefault();
                const form = $('#order-form');
                const button = $(this);
                button.data('disabled', 'true').addClass('opacity-50');
                $.ajax({
                    url: 'order-form.php',
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            gsap.fromTo('#confirm-order-btn', { scale: 1 }, { scale: 1.1, duration: 0.2, yoyo: true, repeat: 1 });
                            window.location.href = 'order_confirmation.php?order_id=' + response.order_id;
                        } else {
                            Toastify({ text: response.message || 'Checkout failed', backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                        }
                    },
                    error: function() {
                        Toastify({ text: 'Error connecting to server', backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                    },
                    complete: function() {
                        button.prop('disabled', false).removeClass('opacity-50');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?>