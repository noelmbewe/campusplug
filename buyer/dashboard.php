
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
    <!-- Simplified styles -->
    <style>
        .add-to-cart { background: #2563eb; color: white; padding: 8px; border-radius: 4px; }
        .add-to-cart:hover { background: #1d4ed8; }
        .cart-loading { display: none; }
    </style>
</head>
<body>
    <main>
        <div>
            <?php foreach ($products as $product): ?>
                <div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <button class="add-to-cart" data-product-id="<?php echo $product['product_id']; ?>" data-listing-type="<?php echo $product['listing_type']; ?>">
                        <span class="cart-text">Add to Cart</span>
                        <span class="cart-loading"><i class="fas fa-spinner fa-spin"></i> Adding...</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <div>Cart: <span id="cart-count"><?php echo $cart_count; ?></span></div>
    </main>
    <script>
        $(document).ready(function() {
            $('.add-to-cart').click(function() {
                const button = $(this);
                const product_id = button.data('product-id');
                const listing_type = button.data('listing-type');
                button.find('.cart-text').hide();
                button.find('.cart-loading').show();
                $.post('/campusplug/buyer/cart_actions.php', {
                    action: 'add',
                    product_id: product_id,
                    quantity: 1,
                    listing_type: listing_type
                }, function(response) {
                    if (response.success) {
                        $('#cart-count').text(response.cart_count);
                        Toastify({ text: response.message, style: { background: '#22c55e' }, duration: 3000 }).showToast();
                    } else {
                        Toastify({ text: response.message || 'Failed to add to cart', style: { background: '#ef4444' }, duration: 3000 }).showToast();
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
                    Toastify({ text: 'Server error', style: { background: '#ef4444' }, duration: 3000 }).showToast();
                }).always(function() {
                    button.find('.cart-text').show();
                    button.find('.cart-loading').hide();
                });
            });
        });
    </script>
</body>
</html>
```