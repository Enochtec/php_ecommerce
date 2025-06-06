<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Ecommerce'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'teal-gradient-start': '#4ECDC4',
                        'teal-gradient-end': '#44A08D',
                        'orange-primary': '#FF6B35',
                        'red-gradient': '#FF6B6B',
                        'yellow-gradient': '#4ECDC4'
                    }
                }
            }
        }
    </script>
    <style>
        .promo-banner {
            background: linear-gradient(135deg, #4ECDC4 0%, #44A08D 100%);
        }
        
        .shop-now-text {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .carousel-transition {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Promotional Banner -->
  <!-- Professional Promo Banner with Carousel Ads -->
<div class="promo-banner text-white py-2 font-semibold text-sm">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Trust Badge -->
            <div class="flex items-center gap-3 bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm">
                <div class="flex items-center justify-center bg-white text-blue-600 rounded-full w-6 h-6">
                    <i class="bi bi-check-lg text-sm"></i>
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="font-bold">Trusted Since 2018</span>
                    <small class="text-xs opacity-80">5+ Years Experience</small>
                </div>
            </div>

            <!-- Main Carousel -->
            <div class="flex-1 max-w-2xl mx-4">
                <div class="relative overflow-hidden h-10">
                    <!-- Carousel Items -->
                    <div class="absolute inset-0 flex items-center justify-center space-x-10 animate-marquee whitespace-nowrap">
                        <div class="flex items-center">
                            <i class="bi bi-truck mr-2"></i>
                            <span>Free Shipping on Orders Ksh 1000+</span>
                        </div>
                        <div class="flex items-center">
                            <i class="bi bi-arrow-repeat mr-2"></i>
                            <span>Easy 30-Day Returns</span>
                        </div>
                        <div class="flex items-center">
                            <i class="bi bi-shield-check mr-2"></i>
                            <span>Secure Checkout</span>
                        </div>
                        <div class="flex items-center text-yellow-300">
                            <i class="bi bi-lightning-charge mr-2"></i>
                            <span>Flash Sale: Up to 60% Off</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promo Buttons -->
            <div class="flex space-x-3">
                <a href="#" class="flex items-center bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-sm font-bold hover:bg-yellow-300 transition-colors">
                    <i class="bi bi-percent mr-1"></i>
                    <span>Today's Deals</span>
                </a>
                <a href="#" class="flex items-center bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-bold hover:bg-gray-100 transition-colors">
                    <i class="bi bi-stars mr-1"></i>
                    <span>New Arrivals</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 20s linear infinite;
    }
</style>

    <!-- Main Header -->
    <div class="bg-white shadow-lg py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Logo Section (Organized but styles preserved) -->
              <div class="flex-shrink-0">
    <a class="text-decoration-none flex items-end group" href="index.php">
        <!-- Slightly reduced logo size -->
        <img src="https://cdn-icons-png.flaticon.com/512/1374/1374128.png" 
             alt="ShopEasy" 
             class="h-8 w-8 mr-2 transition-transform group-hover:rotate-12"> <!-- Changed from h-9 w-9 to h-8 w-8 -->
        
        <!-- Main logo text with subscript -->
        <div class="flex flex-col leading-none">
            <span class="text-2xl font-serif tracking-wide">
                <span class="text-blue-700 font-bold">Shop</span>
                <span class="text-gray-700 font-medium">Easy</span>
            </span>
            <!-- <span class="text-xs text-gray-500 mt-0.5 ml-px font-light italic">
                Since 2018
            </span> -->
        </div>
    </a>
</div>

                <!-- Adjacent Ad Banner (Unchanged) -->
               
                
                <!-- Navigation Links -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors" href="index.php">
                        <i class="bi bi-house-door mr-1"></i>Home
                    </a>
                    <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors" href="products.php">
                        <i class="bi bi-grid mr-1"></i>All Products
                    </a>
                   
                    <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors" href="#">
                        <i class="bi bi-lightning mr-1"></i>Deals
                    </a>
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <a class="flex items-center bg-yellow-400 text-gray-800 px-3 py-2 rounded-lg font-medium transition-colors" href="../admin/dashboard.php">
                            <i class="bi bi-speedometer2 mr-1"></i>Admin
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl mx-8 hidden md:block">
                    <div class="relative">
                        <form action="products.php" method="GET" class="flex">
                            <input 
                                type="text" 
                                name="search" 
                                class="w-full border-2 border-gray-200 rounded-l-lg px-4 py-3 text-base focus:outline-none focus:border-orange-600" 
                                placeholder="Search products, brands and categories"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                aria-label="Search products"
                                autocomplete="off"
                            >
                            <button 
                                type="submit" 
                                class="bg-orange-600 text-white px-6 py-3 rounded-r-lg font-semibold hover:bg-orange-700 transition-colors flex items-center justify-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="sr-only md:not-sr-only md:ml-2">Search</span>
                            </button>
                        </form>

                        <!-- Hidden search suggestions container -->
                        <div class="absolute z-10 hidden w-full mt-1 bg-white border-2 border-gray-200 rounded-lg shadow-lg" id="search-suggestions">
                            <div class="py-2">
                                <!-- Suggestions will be populated here via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>       
                
                <!-- Header Actions -->
                <div class="flex items-center space-x-6">
                    <?php if (isLoggedIn()): ?>
                        <div class="relative group">
                            <button class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors" id="accountDropdown" onclick="toggleDropdown('accountMenu')">
                                <i class="bi bi-person-circle mr-1"></i>
                                <span class="hidden sm:inline"><?php echo $_SESSION['username']; ?></span>
                                <i class="bi bi-chevron-down ml-1"></i>
                            </button>
                            <div id="accountMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <a class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-t-lg" href="profile.php">
                                    <i class="bi bi-person mr-2"></i>Profile
                                </a>
                                <a class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50" href="orders.php">
                                    <i class="bi bi-receipt mr-2"></i>My Orders
                                </a>
                                <?php if (isAdmin()): ?>
                                    <hr class="border-gray-200">
                                    <a class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50" href="../admin/dashboard.php">
                                        <i class="bi bi-speedometer2 mr-2"></i>Admin Panel
                                    </a>
                                <?php endif; ?>
                                <hr class="border-gray-200">
                                <a class="flex items-center px-4 py-2 text-red-600 hover:bg-gray-50 rounded-b-lg" href="logout.php">
                                    <i class="bi bi-box-arrow-right mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors">
                            <i class="bi bi-box-arrow-in-right mr-1"></i>
                            <span class="hidden sm:inline">Login</span>
                        </a>
                        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-full font-medium hover:bg-blue-700 transition-colors">
                            <i class="bi bi-person-plus mr-1"></i>Register
                        </a>
                    <?php endif; ?>
                    
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors" onclick="toggleDropdown('helpMenu')">
                            <i class="bi bi-question-circle mr-1"></i>
                            <span class="hidden sm:inline">Help</span>
                            <i class="bi bi-chevron-down ml-1"></i>
                        </button>
                        <div id="helpMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <a class="block px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-t-lg" href="contact.php">Contact Us</a>
                            <a class="block px-4 py-2 text-gray-700 hover:bg-gray-50" href="faq.php">FAQ</a>
                            <a class="block px-4 py-2 text-gray-700 hover:bg-gray-50" href="shipping.php">Shipping Info</a>
                            <a class="block px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-b-lg" href="returns.php">Returns</a>
                        </div>
                    </div>
                    
                    <a href="cart.php" class="flex items-center text-gray-700 hover:text-blue-600 font-medium transition-colors relative">
                        <i class="bi bi-cart3 text-xl"></i>
                        <span class="ml-1 hidden sm:inline">Cart</span>
                        <?php if (isLoggedIn() && isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?php echo $_SESSION['cart_count']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button class="lg:hidden ml-4" onclick="toggleDropdown('mobileMenu')">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Search Bar -->
            <div class="md:hidden mt-4">
                <form action="search.php" method="GET" class="flex">
                    <input type="text" name="q" class="flex-1 border-2 border-gray-200 rounded-l-lg px-4 py-2 text-base focus:outline-none focus:border-orange-600" placeholder="Search products...">
                    <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-r-lg font-semibold">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div id="mobileMenu" class="hidden lg:hidden bg-white border-t border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="space-y-2">
                <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium py-2" href="index.php">
                    <i class="bi bi-house-door mr-2"></i>Home
                </a>
                <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium py-2" href="products.php">
                    <i class="bi bi-grid mr-2"></i>All Products
                </a>
                <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium py-2" href="categories.php">
                    <i class="bi bi-list mr-2"></i>Categories
                </a>
                <a class="flex items-center text-gray-700 hover:text-blue-600 font-medium py-2" href="deals.php">
                    <i class="bi bi-lightning mr-2"></i>Deals
                </a>
                <?php if (isLoggedIn() && isAdmin()): ?>
                    <a class="flex items-center bg-yellow-400 text-gray-800 px-3 py-2 rounded-lg font-medium" href="../admin/dashboard.php">
                        <i class="bi bi-speedometer2 mr-2"></i>Admin
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Carousel for promotional text
        const promoTexts = [
            "Special Offers - Limited Time",
            "Free Shipping on Orders $50+",
            "Up to 70% Off Selected Items",
            "New Arrivals Every Week",
            "24/7 Customer Support",
            "Flash Sale - Today Only",
            "Buy 2 Get 1 Free"
        ];
        
        let currentIndex = 0;
        const carouselElement = document.getElementById('carouselText');
        
        function rotatePromoText() {
            currentIndex = (currentIndex + 1) % promoTexts.length;
            carouselElement.style.opacity = '0';
            
            setTimeout(() => {
                carouselElement.textContent = promoTexts[currentIndex];
                carouselElement.style.opacity = '1';
            }, 300);
        }
        
        // Change text every 3 seconds
        setInterval(rotatePromoText, 3000);
        
        // Dropdown functionality
        function toggleDropdown(menuId) {
            const menu = document.getElementById(menuId);
            const isHidden = menu.classList.contains('hidden');
            
            // Close all dropdowns first
            document.querySelectorAll('[id$="Menu"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
            
            // Toggle the clicked dropdown
            if (isHidden) {
                menu.classList.remove('hidden');
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id$="Menu"]');
            const buttons = document.querySelectorAll('[onclick*="toggleDropdown"]');
            
            let clickedButton = false;
            buttons.forEach(button => {
                if (button.contains(event.target)) {
                    clickedButton = true;
                }
            });
            
            if (!clickedButton) {
                dropdowns.forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const searchForm = document.querySelector('form[action="products.php"]');
            const suggestionsContainer = document.getElementById('search-suggestions');
            
            // Debounce function to limit how often we make API requests
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this, args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
            
            // Fetch search suggestions
            const fetchSuggestions = debounce(function(query) {
                if (query.length < 2) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }
                
                fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            suggestionsContainer.innerHTML = data.map(item => `
                                <a href="products.php?search=${encodeURIComponent(item)}" class="block px-4 py-2 hover:bg-gray-100">${item}</a>
                            `).join('');
                            suggestionsContainer.classList.remove('hidden');
                        } else {
                            suggestionsContainer.classList.add('hidden');
                        }
                    })
                    .catch(() => suggestionsContainer.classList.add('hidden'));
            }, 300);
            
            // Event listeners
            searchInput.addEventListener('input', function() {
                fetchSuggestions(this.value);
            });
            
            searchInput.addEventListener('focus', function() {
                if (this.value.length >= 2) {
                    fetchSuggestions(this.value);
                }
            });
            
            document.addEventListener('click', function(e) {
                if (!searchForm.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });
            
            // Handle keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' && !suggestionsContainer.classList.contains('hidden')) {
                    e.preventDefault();
                    const firstSuggestion = suggestionsContainer.querySelector('a');
                    if (firstSuggestion) firstSuggestion.focus();
                }
            });
        });
    </script>
</body>
</html>