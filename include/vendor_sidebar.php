<aside id="sidebar" class="bg-[#714315] text-white w-64 h-screen fixed top-0 left-0 flex flex-col transition-transform duration-300 z-50">
    <div class="p-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">CampusPlug</h1>
        <button id="toggle-sidebar" class="text-white focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <nav class="flex-1">
        <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f] <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-[#5a330f]' : ''; ?>">
            <i class="fas fa-home mr-2"></i>
            <span>Dashboard</span>
        </a>
        <a href="products.php" class="flex items-center p-4 hover:bg-[#5a330f] <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-[#5a330f]' : ''; ?>">
            <i class="fas fa-box mr-2"></i>
            <span>Products</span>
        </a>
        <a href="orders.php" class="flex items-center p-4 hover:bg-[#5a330f] <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'bg-[#5a330f]' : ''; ?>">
            <i class="fas fa-shopping-cart mr-2"></i>
            <span>Orders</span>
        </a>
        <a href="profile.php" class="flex items-center p-4 hover:bg-[#5a330f] <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-[#5a330f]' : ''; ?>">
            <i class="fas fa-user mr-2"></i>
            <span>Profile</span>
        </a>
    </nav>
</aside>
?>