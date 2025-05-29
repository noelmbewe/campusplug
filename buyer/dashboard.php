<?php

require_once '../db_connect.php';
require_once '../user_functions.php';
require_once 'buyer_manager.php';
checkBuyer();
$buyer = new BuyerManager($pdo, $_SESSION['user_id']);
$filters = [
    'category_id' => $_GET['category_id'] ?? '',
    'listing_type' => $_GET['listing_type'] ?? '',
    'price_min' => $_GET['price_min'] ?? '',
    'price_max' => $_GET['price_max'] ?? '',
    'search' => $_GET['search'] ?? ''
];
$products = $buyer->getProducts($filters);
$categories = $buyer->getCategories();
$cart_count = count($buyer->getCart());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusPlug - Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <style>
        .product-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            border: 2px solid transparent;
            background: white;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
            border-image: linear-gradient(45deg, #714315, #5a330f) 1;
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .badge.sale { background: #ef4444; }
        .badge.rent { background: #3b82f6; }
        .quick-view {
            opacity: 0;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            transition: opacity 0.3s ease;
        }
        .product-card:hover .quick-view { opacity: 1; }
        .hero {
            background: linear-gradient(135deg, #714315, #5a330f);
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1), transparent);
            animation: rotate 20s linear infinite;
        }
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .product-image { height: 150px; }
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
                <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f] bg-[#5a330f]">
                    <i class="fas fa-home mr-2"></i>
                    <span>Shop</span>
                </a>
                <a href="cart.php" class="flex items-center p-4 hover:bg-[#5a330f] relative">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    <span>Cart</span>
                    <span id="cart-count" class="absolute right-4 bg-red-500 text-white rounded-full px-2 py-1 text-xs"><?php echo $cart_count; ?></span>
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
                    <h2 class="text-xl font-semibold">Shop</h2>
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
                <div class="hero text-white rounded-lg p-8 mb-6 relative z-10">
                    <h1 class="text-3xl font-bold">Welcome to CampusPlug!</h1>
                    <p class="text-lg">Discover amazing deals on campus essentials in MWK.</p>
                </div>
                <div x-data="{ filters: <?php echo json_encode($filters); ?> }" class="bg-white rounded-lg shadow p-6 mb-6">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Category</label>
                            <select name="category_id" x-model="filters.category_id" class="w-full p-2 border rounded">
                                <option value="">All</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo $filters['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Listing Type</label>
                            <select name="listing_type" x-model="filters.listing_type" class="w-full p-2 border rounded">
                                <option value="">All</option>
                                <option value="sale" <?php echo $filters['listing_type'] == 'sale' ? 'selected' : ''; ?>>Sale</option>
                                <option value="rent" <?php echo $filters['listing_type'] == 'rent' ? 'selected' : ''; ?>>Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Price Range (MWK)</label>
                            <div class="flex space-x-2">
                                <input type="number" name="price_min" x-model="filters.price_min" placeholder="Min" class="w-1/2 p-2 border rounded" min="0">
                                <input type="number" name="price_max" x-model="filters.price_max" placeholder="Max" class="w-1/2 p-2 border rounded" min="0">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Search</label>
                            <input type="text" name="search" x-model="filters.search" placeholder="Search products..." class="w-full p-2 border rounded">
                        </div>
                    </form>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php if (empty($products)): ?>
                        <div class="col-span-full text-center p-6 bg-white rounded-lg shadow">
                            <p class="text-gray-600">No products found. <a href="dashboard.php" class="text-blue-500 hover:underline">Clear Filters</a></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image w-full">
                                <div class="p-4">
                                    <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($product['category'] ?: 'N/A'); ?></p>
                                    <p class="text-lg font-semibold text-yellow-600">K<?php echo number_format($product['price'] * 1750, 0, '', ','); ?></p>
                                    <p class="text-sm text-gray-600">In Stock</p>
                                    <span class="badge <?php echo $product['listing_type']; ?>">
                                        <?php echo ucfirst($product['listing_type']); ?>
                                    </span>
                                    <div class="mt-3 flex space-x-2">
                                        <button class="add-to-cart bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 transition-colors" 
                                                data-product-id="<?php echo $product['product_id']; ?>" 
                                                data-listing-type="<?php echo $product['listing_type']; ?>">
                                            <span class="cart-text">Add to Cart</span>
                                            <span class="cart-loading hidden"><i class="fas fa-spinner fa-spin"></i> Adding...</span>
                                        </button>
                                        <button class="quick-view bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300" 
                                                data-product-id="<?php echo $product['product_id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($products)): ?>
                    <div class="mt-8">
                        <h3 class="text-2xl font-semibold mb-4">Featured Products</h3>
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <?php foreach ($products as $product): ?>
                                    <div class="swiper-slide">
                                        <div class="bg-white rounded-lg shadow p-4">
                                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 object-cover rounded mb-4">
                                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="text-lg font-bold text-yellow-600">K<?php echo number_format($product['price'] * 1750, 0, '', ','); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
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
            $('#filter-form').on('change input', function() {
                const data = $(this).serialize();
                window.location.href = 'dashboard.php?' + data;
            });
            $('.add-to-cart').click(function() {
                const button = $(this);
                const product_id = button.data('product-id');
                const listing_type = button.data('listing-type');
                button.find('.cart-text').addClass('hidden');
                button.find('.cart-loading').removeClass('hidden');
                $.post('/campusplug/buyer/cart_actions.php', { action: 'add', product_id: product_id, quantity: 1, listing_type: listing_type }, function(response) {
                    if (response.success) {
                        $('#cart-count').text(response.cart_count);
                        Toastify({ text: response.message, backgroundColor: '#2ecc71', duration: 3000 }).showToast();
                    } else {
                        Toastify({ text: response.message || 'Failed to add to cart', backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
                    Toastify({ text: 'Error connecting to server: ' + textStatus, backgroundColor: '#e74c3c', duration: 3000 }).showToast();
                }).always(function() {
                    button.find('.cart-text').removeClass('hidden');
                    button.find('.cart-loading').addClass('hidden');
                });
            });
            $('.quick-view').click(function() {
                const product_id = $(this).data('product-id');
                Toastify({ text: `Quick view for product ${product_id} (coming soon)`, backgroundColor: '#3b82f6', duration: 3000 }).showToast();
            });
            new Swiper('.swiper-container', {
                slidesPerView: 1,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 4 },
                },
                loop: <?php echo empty($products) ? 'false' : 'true'; ?>,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                }
            });
        });
    </script>
</body>
</html>
?>