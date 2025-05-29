<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../buyer_manager.php';
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$cart_items = $buyer->getCart();
$cart_count = count($cart_items);
$subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart_items));
$tax = $subtotal * 0.1;
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug - Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="flex flex-1">
        <aside id="sidebar" class="bg-[#714315] text-white w-64 h-screen fixed top-0 left-0 flex flex-col transition-transform duration-300 z-50">
            <div class="p-4 flex items-center justify-between">
                <h1 class="text-xl font-bold">CampusPlug</h1>
                <button id="toggle-sidebar" class="text-white focus:outline-none">
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
                    <span class="absolute right-4 bg-red-500 text-white rounded-full px-2 py-1 text-xs"><?php echo $cart_count; ?></span>
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
                    <button id="mobile-menu" class="text-gray-700 focus:outline-none md:hidden mr-4">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-semibold">Cart</h2>
                </div>
                <div class="relative">
                    <button id="profile-toggle" class="flex items-center text-gray-700 focus:outline-none">
                        <i class="fas fa-user-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Buyer'); ?></span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg hidden">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="../logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </header>
            <main class="p-6">
                <h3 class="text-2xl font-semibold mb-6">Your Cart</h3>
                <?php if (empty($cart_items)): ?>
                    <p class="text-gray-600">Your cart is empty. <a href="dashboard.php" class="text-blue-500">Shop now!</a></p>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex items-center border-b py-4">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded mr-4">
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($item['listing_type']); ?></p>
                                    <p class="text-lg">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="flex items-center">
                                    <input type="number" min="1" value="<?php echo $item['quantity']; ?>" 
                                           class="update-quantity w-16 p-2 border rounded mr-2" 
                                           data-id="<?php echo $item['cart_item_id']; ?>">
                                    <button class="remove-item bg-red-500 text-white px-2 py-1 rounded" 
                                            data-id="<?php echo $item['cart_item_id']; ?>">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="text-lg font-semibold mb-4">Order Summary</h4>
                        <div class="flex justify-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Tax (10%)</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span>Total</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="block bg-blue-500 text-white px-4 py-2 rounded mt-4 text-center hover:bg-blue-600">Proceed to Checkout</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            $('#toggle-sidebar').click(function() {
                const sidebar = $('#sidebar');
                const mainContent = $('#main-content');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('span').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                mainContent.toggleClass('ml-64 ml-20');
            });

            // Mobile Sidebar Toggle
            $('#mobile-menu').click(function() {
                $('#sidebar').toggleClass('open');
            });

            // Profile Dropdown
            $('#profile-toggle').click(function() {
                $('#profile-dropdown').toggleClass('hidden');
            });

            // Update Quantity
            $('.update-quantity').change(function() {
                const cart_item_id = $(this).data('id');
                const quantity = $(this).val();
                $.post('cart_actions.php', { action: 'update', cart_item_id: cart_item_id, quantity: quantity }, function(response) {
                    if (response.success) {
                        Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                        location.reload();
                    } else {
                        Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                    }
                });
            });

            // Remove Item
            $('.remove-item').click(function() {
                if (confirm('Remove this item?')) {
                    const cart_item_id = $(this).data('id');
                    $.post('cart_actions.php', { action: 'remove', cart_item_id: cart_item_id }, function(response) {
                        if (response.success) {
                            Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                            location.reload();
                        } else {
                            Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
?>