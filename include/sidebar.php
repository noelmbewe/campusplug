<?php
$current_page = basename($_SERVER['PHP_SELF']);
$collapsed = isset($_SESSION['sidebar_collapsed']) && $_SESSION['sidebar_collapsed'];
?>
<aside id="sidebar" class="bg-[#714315] text-white h-full fixed transition-all duration-300 <?php echo $collapsed ? 'w-20' : 'w-64'; ?>">
    <div class="p-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold <?php echo $collapsed ? 'hidden' : ''; ?>">CampusPlug</h1>
        <button id="toggle-sidebar" class="text-white focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <nav class="mt-4">
        <ul>
            <li>
                <a href="dashboard.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-home mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'users.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-users mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Users</span>
                </a>
            </li>
            <li>
                <a href="vendors.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'vendors.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-store mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Vendors</span>
                </a>
            </li>
            <li>
                <a href="listings.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'listings.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-box mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Listings</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'reports.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-flag mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Reports</span>
                </a>
            </li>
            <li>
                <a href="stats.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'stats.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-chart-bar mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Stats</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="flex items-center p-4 hover:bg-[#5a330f] transition-colors duration-200 <?php echo $current_page == 'settings.php' ? 'bg-[#5a330f]' : ''; ?>">
                    <i class="fas fa-cog mr-3"></i>
                    <span class="<?php echo $collapsed ? 'hidden' : ''; ?>">Settings</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
?>