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
      <!-- Sidebar Categories -->
      <div class="w-full lg:w-1/5">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">Shop Categories</h3>
          </div>
          <ul class="divide-y divide-gray-100">
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $category): ?>
                <li class="px-6 py-3 hover:bg-gray-50 transition-colors duration-150">
                  <a href="products.php?category_id=<?= $category['category_id']; ?>" 
                     class="flex items-center text-gray-700 hover:text-primary-600 font-medium">
                    <span class="ml-3"><?= htmlspecialchars($category['name']); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="px-6 py-4 text-gray-500">No categories available.</li>
            <?php endif; ?>
          </ul>
        </div>
        
        <!-- Promo Banner -->
        <div class="mt-6 bg-gradient-to-r from-primary-500 to-primary-700 rounded-xl p-6 text-white">
          <h4 class="font-bold text-lg mb-2">Summer Sale</h4>
          <p class="text-sm mb-4">Up to 50% off selected items</p>
          <a href="#" class="inline-block bg-white text-primary-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition">Shop Now</a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="w-full lg:w-4/5">
        <!-- Carousel -->
     <!-- Carousel -->
<div class="relative rounded-2xl overflow-hidden shadow-2xl mb-12 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 border border-slate-200/50 dark:border-slate-700/50">
  <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner rounded-2xl overflow-hidden">
      <?php $colorSlides = [
        ["bg-gradient-to-r from-purple-500 to-indigo-600", "Premium Collection"],
        ["bg-gradient-to-r from-emerald-500 to-teal-600", "New Arrivals"],
        ["bg-gradient-to-r from-amber-500 to-orange-600", "Limited Time Offers"]
      ]; ?>
      <?php foreach ($colorSlides as $index => $slide): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?> relative">
          <div class="<?= $slide[0]; ?> w-full h-[400px] flex items-center justify-center transition-all duration-700">
            <h2 class="text-4xl md:text-5xl font-bold text-white tracking-tight drop-shadow-lg"><?= $slide[1]; ?></h2>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Previous Button -->
    <button class="carousel-control-prev absolute left-6 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full w-12 h-12 flex items-center justify-center hover:bg-white hover:scale-110 transition-all duration-300 shadow-lg border border-white/20 group" 
            type="button" 
            data-bs-target="#carouselExample" 
            data-bs-slide="prev">
      <svg class="w-5 h-5 text-slate-700 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
      </svg>
      <span class="visually-hidden">Previous</span>
    </button>
    
    <!-- Next Button -->
    <button class="carousel-control-next absolute right-6 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full w-12 h-12 flex items-center justify-center hover:bg-white hover:scale-110 transition-all duration-300 shadow-lg border border-white/20 group" 
            type="button" 
            data-bs-target="#carouselExample" 
            data-bs-slide="next">
      <svg class="w-5 h-5 text-slate-700 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
      <span class="visually-hidden">Next</span>
    </button>
    
    <!-- Slide Indicators -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
      <?php foreach ($colorSlides as $index => $slide): ?>
        <button type="button" 
                data-bs-target="#carouselExample" 
                data-bs-slide-to="<?= $index; ?>" 
                class="w-3 h-3 rounded-full bg-white/60 hover:bg-white transition-all duration-300 <?= $index === 0 ? 'bg-white' : ''; ?>"
                aria-current="<?= $index === 0 ? 'true' : 'false'; ?>" 
                aria-label="Slide <?= $index + 1; ?>">
        </button>
      <?php endforeach; ?>
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

<?php include 'includes/footer.php'; ?>