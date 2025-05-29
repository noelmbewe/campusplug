<?php
require_once '../db_connect.php';
require_once '../user_functions.php';
require_once '../buyer_manager.php';
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
        .product-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .hero { background: linear-gradient(135deg, #714315, #5a330f); }
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
                <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f] bg-[#5a330f]">
                    <i class="fas fa-home mr-2"></i>
                    <span>Shop</span>
                </a>
                <a href="cart.php" class="flex items-center p-4 hover:bg-[#5a330f] relative">
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
                <!-- Hero Section -->
                <div class="hero text-white rounded-lg p-8 mb-6">
                    <h1 class="text-3xl font-bold">Welcome to CampusPlug!</h1>
                    <p class="text-lg">Discover amazing deals on campus essentials.</p>
                </div>

                <!-- Filters -->
                <div x-data="{ filters: <?php echo json_encode($filters); ?> }" class="bg-white rounded-lg shadow p-6 mb-6">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Category</label>
                            <select name="category_id" x-model="filters.category_id" class="w-full p-2 border rounded">
                                <option value="">All</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Listing Type</label>
                            <select name="listing_type" x-model="filters.listing_type" class="w-full p-2 border rounded">
                                <option value="">All</option>
                                <option value="sale">Sale</option>
                                <option value="rent">Rent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Price Range</label>
                            <div class="flex space-x-2">
                                <input type="number" name="price_min" x-model="filters.price_min" placeholder="Min" class="w-1/2 p-2 border rounded">
                                <input type="number" name="price_max" x-model="filters.price_max" placeholder="Max" class="w-1/2 p-2 border rounded">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Search</label>
                            <input type="text" name="search" x-model="filters.search" placeholder="Search products..." class="w-full p-2 border rounded">
                        </div>
                    </form>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow p-4 product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 object-cover rounded mb-4">
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category'] ?: 'N/A'); ?></p>
                            <p class="text-lg font-bold">$<?php echo number_format($product['price'], 2); ?></p>
                            <p class="text-sm"><?php echo htmlspecialchars($product['listing_type']); ?></p>
                            <button class="add-to-cart bg-blue-500 text-white px-4 py-2 rounded mt-2 w-full hover:bg-blue-600" 
                                    data-id="<?php echo $product['product_id']; ?>" 
                                    data-type="<?php echo $product['listing_type']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Featured Products Carousel -->
                <div class="mt-6">
                    <h3 class="text-xl font-semibold mb-4">Featured Products</h3>
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <?php foreach ($products as $product): ?>
                                <div class="swiper-slide">
                                    <div class="bg-white rounded-lg shadow p-4">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 object-cover rounded mb-4">
                                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="text-lg font-bold">$<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
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

            // Filter Form
            $('#filter-form').on('change input', function() {
                const data = $(this).serialize();
                window.location.href = 'dashboard.php?' + data;
            });

            // Add to Cart
            $('.add-to-cart').click(function() {
                const product_id = $(this).data('id');
                const listing_type = $(this).data('type');
                $.post('cart_actions.php', { action: 'add', product_id: product_id, quantity: 1, listing_type: listing_type }, function(response) {
                    if (response.success) {
                        Toastify({ text: response.message, backgroundColor: '#2ecc71' }).showToast();
                        location.reload();
                    } else {
                        Toastify({ text: response.message, backgroundColor: '#e74c3c' }).showToast();
                    }
                });
            });

            // Swiper Carousel
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
                }
            });
        });
    </script>
</body>
</html>
?>