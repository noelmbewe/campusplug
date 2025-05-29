<header class="bg-white shadow p-4 flex justify-between items-center">
    <div class="flex items-center">
        <button id="mobile-menu" class="text-gray-700 focus:outline-none md:hidden mr-4">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="text-xl font-semibold">Vendor Dashboard</h2>
    </div>
    <div class="relative">
        <button id="profile-toggle" class="flex items-center text-gray-700 focus:outline-none">
            <i class="fas fa-user-circle mr-2"></i>
            <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Vendor'); ?></span>
            <i class="fas fa-chevron-down ml-2"></i>
        </button>
        <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg hidden">
            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="../logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">Logout</a>
        </div>
    </div>
</header>
?>