<?php
require_once 'config.php';

$featured_products = getProducts(null, 8);
$categories = getCategories();
?>

<?php include 'includes/header.php'; ?>

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            50: '#f0f9ff',
            100: '#e0f2fe',
            200: '#bae6fd',
            300: '#7dd3fc',
            400: '#38bdf8',
            500: '#0ea5e9',
            600: '#0284c7',
            700: '#0369a1',
            800: '#075985',
            900: '#0c4a6e',
          }
        },
        fontFamily: {
          sans: ['Inter', 'sans-serif'],
        },
      }
    }
  }
</script>

<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Sidebar Categories - Improved Styling -->
      <div class="w-full lg:w-1/5">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>
              Shop Categories
            </h3>
          </div>
          <ul class="divide-y divide-gray-100 max-h-[calc(100vh-300px)] overflow-y-auto">
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $category): ?>
                <li class="px-6 py-3 hover:bg-gray-50 transition-colors duration-200 group">
                  <a href="products.php?category_id=<?= $category['category_id']; ?>" 
                     class="flex items-center text-gray-700 hover:text-primary-600 font-medium">
                    <?php if(isset($category['icon'])): ?>
                      <span class="text-gray-400 group-hover:text-primary-500 mr-3"><?= $category['icon'] ?></span>
                    <?php else: ?>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-primary-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                      </svg>
                    <?php endif; ?>
                    <span class="flex-grow"><?= htmlspecialchars($category['name']); ?></span>
                    <span class="text-xs bg-primary-100 text-primary-800 px-2 py-1 rounded-full"><?= rand(10, 200) ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-gray-400 group-hover:text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="px-6 py-4 text-gray-500 flex flex-col items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                No categories available
              </li>
            <?php endif; ?>
          </ul>
          
          <!-- Promo Banner -->
          <div class="bg-gradient-to-r from-orange-500 to-pink-500 p-4 text-white">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-white bg-opacity-20 p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </div>
              <div class="ml-3">
                <h4 class="font-bold">Special Offer</h4>
                <p class="text-sm">Up to 60% off</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Additional Filters -->
        <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">Filters</h3>
          </div>
          <div class="p-4">
            <div class="mb-4">
              <h4 class="font-medium text-gray-700 mb-2">Price Range</h4>
              <div class="flex items-center justify-between space-x-4">
                <input type="number" placeholder="Min" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                <span class="text-gray-500">to</span>
                <input type="number" placeholder="Max" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
              </div>
            </div>
            <button class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="w-full lg:w-4/5">
        <!-- Multi-Item Advertisement Carousel -->
        <div class="relative rounded-2xl overflow-hidden shadow-xl mb-12">
          <div class="relative h-64 overflow-hidden">
            <!-- Carousel Container -->
            <div id="carousel" class="flex transition-transform duration-500 ease-in-out h-full">
              <!-- Slide Group 1 -->
              <div class="w-full flex-shrink-0 flex">
                <!-- Ad 1 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1926&q=80" 
                       alt="Electronics Sale" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Electronics</h3>
                      <p class="text-sm mb-2">Up to 70% Off</p>
                      <a href="#" class="inline-block bg-white text-primary-600 px-3 py-1 rounded-full text-xs font-semibold">Shop Now</a>
                    </div>
                  </div>
                </div>
                
                <!-- Ad 2 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                       alt="Fashion Collection" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Fashion</h3>
                      <p class="text-sm mb-2">New Arrivals</p>
                      <a href="#" class="inline-block bg-white text-pink-600 px-3 py-1 rounded-full text-xs font-semibold">Explore</a>
                    </div>
                  </div>
                </div>
                
                <!-- Ad 3 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2058&q=80" 
                       alt="Home & Garden" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Home & Garden</h3>
                      <p class="text-sm mb-2">Special Deals</p>
                      <a href="#" class="inline-block bg-white text-green-600 px-3 py-1 rounded-full text-xs font-semibold">Discover</a>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Slide Group 2 -->
              <div class="w-full flex-shrink-0 flex">
                <!-- Ad 4 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                       alt="Sports & Fitness" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Sports</h3>
                      <p class="text-sm mb-2">Gear Up</p>
                      <a href="#" class="inline-block bg-white text-orange-600 px-3 py-1 rounded-full text-xs font-semibold">Shop Now</a>
                    </div>
                  </div>
                </div>
                
                <!-- Ad 5 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1999&q=80" 
                       alt="Watches" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Watches</h3>
                      <p class="text-sm mb-2">Luxury Brands</p>
                      <a href="#" class="inline-block bg-white text-purple-600 px-3 py-1 rounded-full text-xs font-semibold">View All</a>
                    </div>
                  </div>
                </div>
                
                <!-- Ad 6 -->
                <div class="w-1/3 h-full relative">
                  <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2076&q=80" 
                       alt="Beauty Products" 
                       class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center p-4">
                    <div class="text-center text-white">
                      <h3 class="text-xl font-bold mb-1">Beauty</h3>
                      <p class="text-sm mb-2">New Collection</p>
                      <a href="#" class="inline-block bg-white text-rose-600 px-3 py-1 rounded-full text-xs font-semibold">Explore</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Buttons -->
            <button id="prevBtn" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg transition-all duration-300 hover:scale-110">
              <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            
            <button id="nextBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg transition-all duration-300 hover:scale-110">
              <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>

            <!-- Slide Indicators -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
              <button class="indicator w-2 h-2 rounded-full bg-white bg-opacity-60 hover:bg-opacity-100 transition-all duration-300" data-slide="0"></button>
              <button class="indicator w-2 h-2 rounded-full bg-white bg-opacity-60 hover:bg-opacity-100 transition-all duration-300" data-slide="1"></button>
            </div>
          </div>
        </div>

        <!-- Featured Products -->
        <div class="mb-6 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">Featured Products</h2>
          <a href="products.php" class="text-primary-600 hover:text-primary-800 font-medium text-sm flex items-center">
            View all
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
        
        <!-- Product Grid (unchanged from your original) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $product): ?>
              <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300 group">
                <div class="relative overflow-hidden">
                  <img src="<?= $product['image_url'] ?: 'https://via.placeholder.com/400x400'; ?>" 
                       class="w-full h-60 object-cover transition-transform duration-500 group-hover:scale-105" 
                       alt="<?= htmlspecialchars($product['name']); ?>">
                  <div class="absolute top-3 right-3">
                    <button class="bg-white rounded-full p-2 shadow-md hover:bg-primary-100 transition-colors duration-200">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </button>
                  </div>
                </div>
                <div class="p-4">
                  <div class="flex justify-between items-start mb-1">
                    <h3 class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($product['name']); ?></h3>
                    <?php if(isset($product['discount_price'])): ?>
                      <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2 py-0.5 rounded">-<?= round(100 - ($product['discount_price'] / $product['price'] * 100)) ?>%</span>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center mb-3">
                    <div class="flex text-yellow-400">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      <?php endfor; ?>
                    </div>
                    <span class="text-gray-500 text-xs ml-1">(24)</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <?php if(isset($product['discount_price'])): ?>
                        <span class="text-lg font-bold text-gray-900">$<?= number_format($product['discount_price'], 2); ?></span>
                        <span class="text-sm text-gray-500 line-through ml-1">$<?= number_format($product['price'], 2); ?></span>
                      <?php else: ?>
                        <span class="text-lg font-bold text-gray-900">$<?= number_format($product['price'], 2); ?></span>
                      <?php endif; ?>
                    </div>
                    <a href="product.php?id=<?= $product['product_id']; ?>" 
                       class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                      View
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-span-4">
              <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-6 rounded-lg text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="font-medium text-lg mb-1">No featured products</h3>
                <p class="text-sm">Check back soon for our latest collection!</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Carousel functionality for multi-item ads
let currentSlide = 0;
const totalSlides = 2; // Now we have 2 groups of 3 ads each
const carousel = document.getElementById('carousel');
const indicators = document.querySelectorAll('.indicator');

function updateCarousel() {
  carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
  
  // Update indicators
  indicators.forEach((indicator, index) => {
    if (index === currentSlide) {
      indicator.classList.remove('bg-opacity-60');
      indicator.classList.add('bg-opacity-100', 'w-3');
    } else {
      indicator.classList.remove('bg-opacity-100', 'w-3');
      indicator.classList.add('bg-opacity-60');
    }
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % totalSlides;
  updateCarousel();
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
  updateCarousel();
}

// Event listeners
document.getElementById('nextBtn').addEventListener('click', nextSlide);
document.getElementById('prevBtn').addEventListener('click', prevSlide);

// Indicator clicks
indicators.forEach((indicator, index) => {
  indicator.addEventListener('click', () => {
    currentSlide = index;
    updateCarousel();
  });
});

// Auto-play carousel
let carouselInterval = setInterval(nextSlide, 5000);

// Pause on hover
carousel.addEventListener('mouseenter', () => {
  clearInterval(carouselInterval);
});

carousel.addEventListener('mouseleave', () => {
  carouselInterval = setInterval(nextSlide, 5000);
});

// Initialize
updateCarousel();
</script>

<?php include 'includes/footer.php'; ?>