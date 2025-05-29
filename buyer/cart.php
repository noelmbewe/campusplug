<?php

require_once '../db_connect.php';
require_once '../user_functions.php';
require_once 'buyer_manager.php';
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$cart_items = $buyer->getCart();
$cart_count = count($cart_items);
$total = array_reduce($cart_items, fn($sum, $item) => $sum + ($item['price'] * 1750 * $item['quantity']), 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View and manage your shopping cart at CampusPlug.">
    <title>Shopping Cart - CampusPlug</title>
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
        .dark .text-gray-800 { color: #e5e5e5; }
        .cart-badge { background: #714315; color: #fff; }
        .dark .cart-badge { background: #fff; color: #714315; }
        .quantity-btn {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 640px) {
            .quantity-btn { width: 2rem; height: 2rem; }
            .quantity-input { width: 3rem; }
        }
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th, .cart-table td { padding: 1rem; text-align: left; }
        .cart-table th { background: rgba(255, 255, 255, 0.2); }
        .cart-table td { border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        @media (max-width: 640px) {
            .cart-table th, .cart-table td { padding: 0.5rem; font-size: 0.875rem; }
            .cart-table img { width: 3rem; height: 3rem; }
        }
        .cart-footer {
            position: sticky;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-100 transition-colors duration-300">
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
                    <span id="cart-count" class="cart-badge absolute right-4 rounded-full px-2 py-1 text-xs"><?php echo $cart_count; ?></span>
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
            <section class="py-8 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Shopping Cart</h2>
                    <div class="glass-card p-6 rounded-lg">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <?php if (empty($cart_items)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-700">Your cart is empty</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cart_items as $index => $item): ?>
                                        <tr>
                                            <td class="flex items-center space-x-4">
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 rounded-lg">
                                                <span><?php echo htmlspecialchars($item['name']); ?> (<?php echo ucfirst($item['listing_type']); ?>)</span>
                                            </td>
                                            <td>K<?php echo number_format($item['price'] * 1750, 0, '', ','); ?></td>
                                            <td>
                                                <div class="flex items-center">
                                                    <button class="quantity-btn bg-gray-200 text-gray-800 rounded-l-full hover:bg-gray-300 decrease-quantity" data-cart-item-id="<?php echo $item['cart_item_id']; ?>">-</button>
                                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1" max="100" class="quantity-input text-center border-t border-b border-gray-300 dark:border-white text-gray-800 dark:text-white focus:outline-none w-12" data-cart-item-id="<?php echo $item['cart_item_id']; ?>">
                                                    <button class="quantity-btn bg-gray-200 text-gray-800 rounded-r-full hover:bg-gray-300 increase-quantity" data-cart-item-id="<?php echo $item['cart_item_id']; ?>">+</button>
                                                </div>
                                            </td>
                                            <td>K<?php echo number_format($item['price'] * 1750 * $item['quantity'], 0, '', ','); ?></td>
                                            <td>
                                                <button class="text-red-500 hover:text-red-700 remove-item" data-cart-item-id="<?php echo $item['cart_item_id']; ?>">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="cart-footer mt-8">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                                <div class="text-base sm:text-lg font-semibold text-gray-800">
                                    Total: <span id="cart-total">K<?php echo number_format($total, 0, '', ','); ?></span>
                                </div>
                                <a href="checkout.php" id="proceed-checkout" class="w-full sm:w-auto bg-[#714315] text-white py-3 px-8 rounded-full hover:bg-[#5a330f] shadow-lg sm:text-sm sm:py-2 sm:px-6 <?php echo empty($cart_items) ? 'pointer-events-none opacity-50' : ''; ?>">Proceed to Checkout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#toggle-sidebar').click(function() {
                const sidebar = $('#sidebar');
                const mainContent = $('#main-content');
                const collapsed = sidebar.hasClass('w-20');
                sidebar.toggleClass('w-20 w-64');
                sidebar.find('span').toggleClass('hidden', !collapsed);
                sidebar.find('h1').toggleClass('hidden', !collapsed);
                mainContent.toggleClass('ml-64 ml-20');
            });
            $('#mobile-menu').click(function() {
                $('#sidebar').toggleClass('open');
            });
            $('#profile-toggle').click(function() {
                $('#profile-dropdown').toggleClass('hidden');
            });
            function updateCart(action, cart_item_id, quantity = null) {
                $.post('cart_actions.php', { action: action, cart_item_id: cart_item_id, quantity: quantity }, function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        Toastify({ text: response.message || 'Failed to update cart', backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                    }
                }, 'json').fail(function() {
                    Toastify({ text: 'Error connecting to server', backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                });
            }
            $('.decrease-quantity').click(function() {
                const cart_item_id = $(this).data('cart-item-id');
                const input = $(this).siblings('.quantity-input');
                let quantity = parseInt(input.val());
                if (quantity > 1) {
                    quantity--;
                    input.val(quantity);
                    updateCart('update', cart_item_id, quantity);
                }
            });
            $('.increase-quantity').click(function() {
                const cart_item_id = $(this).data('cart-item-id');
                const input = $(this).siblings('.quantity-input');
                let quantity = parseInt(input.val());
                if (quantity < 100) {
                    quantity++;
                    input.val(quantity);
                    updateCart('update', cart_item_id, quantity);
                }
            });
            $('.quantity-input').on('input', function() {
                const cart_item_id = $(this).data('cart-item-id');
                let quantity = parseInt($(this).val());
                if (isNaN(quantity) || quantity < 1) quantity = 1;
                if (quantity > 100) quantity = 100;
                $(this).val(quantity);
                updateCart('update', cart_item_id, quantity);
            });
            $('.remove-item').click(function() {
                const cart_item_id = $(this).data('cart-item-id');
                updateCart('remove', cart_item_id);
            });
            gsap.from('#cart-items tr', { opacity: 0, y: 20, duration: 0.5, stagger: 0.1 });
        });
    </script>
</body>
</html>
?>