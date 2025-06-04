<?php
require_once 'config.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

if ($category_id) {
    $products = getProducts($category_id);
    $category = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
    $category->execute([$category_id]);
    $category_name = $category->fetchColumn();
} elseif ($search) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $search_term = "%$search%";
    $stmt->execute([$search_term, $search_term]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = getProducts();
}

$categories = getCategories();
?>

<?php include 'includes/header.php'; ?>

<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Sidebar Categories -->
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
            <li class="px-6 py-3 hover:bg-gray-50 transition-colors duration-200 group">
              <a href="products.php" class="flex items-center text-gray-700 hover:text-primary-600 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-primary-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="flex-grow">All Categories</span>
              </a>
            </li>
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
                </a>
              </li>
            <?php endforeach; ?>
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
        
        <!-- Filter Section -->
        <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">Filters</h3>
          </div>
          <div class="p-4">
            <form method="GET" action="products.php">
              <div class="mb-4">
                <label for="search" class="block text-gray-700 text-sm font-medium mb-2">Search</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
              </div>
              <div class="mb-4">
                <h4 class="font-medium text-gray-700 mb-2">Price Range</h4>
                <div class="flex items-center justify-between space-x-4">
                  <input type="number" placeholder="Min" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                  <span class="text-gray-500">to</span>
                  <input type="number" placeholder="Max" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>
              </div>
              <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                Apply Filters
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="w-full lg:w-4/5">
        <!-- Page Title -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-800">
            <?php if (isset($category_name)): ?>
              Category: <?php echo htmlspecialchars($category_name); ?>
            <?php elseif ($search): ?>
              Search Results for "<?php echo htmlspecialchars($search); ?>"
            <?php else: ?>
              All Products
            <?php endif; ?>
          </h2>
          <?php if ($search || isset($category_name)): ?>
            <a href="products.php" class="text-primary-600 hover:text-primary-800 text-sm font-medium inline-flex items-center mt-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to all products
            </a>
          <?php endif; ?>
        </div>
        
        <?php if (empty($products)): ?>
          <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-6 rounded-lg text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="font-medium text-lg mb-1">No products found</h3>
            <p class="text-sm">Try adjusting your search or filter criteria</p>
          </div>
        <?php else: ?>
          <!-- Product Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
              <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300 group">
                <div class="relative overflow-hidden">
                  <img src="<?= $product['image_url'] ?: 'https://via.placeholder.com/400x400'; ?>" 
                       class="w-full h-60 object-cover transition-transform duration-500 group-hover:scale-105" 
                       alt="<?= htmlspecialchars($product['name']); ?>">
                  <?php if(isset($product['discount_price'])): ?>
                    <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">
                      -<?= round(100 - ($product['discount_price'] / $product['price'] * 100)) ?>%
                    </span>
                  <?php endif; ?>
                  <div class="absolute top-3 right-3 flex flex-col space-y-2">
                    <button class="bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-colors duration-200">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </button>
                    <?php if (isLoggedIn()): ?>
                      <a href="cart.php?action=add&id=<?= $product['product_id']; ?>" 
                         class="bg-primary-600 text-white rounded-full p-2 shadow-md hover:bg-primary-700 transition-colors duration-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="p-4">
                  <div class="flex justify-between items-start mb-1">
                    <h3 class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($product['name']); ?></h3>
                  </div>
                  <div class="flex items-center mb-3">
                    <div class="flex text-yellow-400">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      <?php endfor; ?>
                    </div>
                    <span class="text-gray-500 text-xs ml-1">(<?= rand(5, 150) ?>)</span>
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
                    <div class="flex space-x-2">
                      <a href="product.php?id=<?= $product['product_id']; ?>" 
                         class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                        View
                      </a>
                      <?php if (isLoggedIn()): ?>
                        <a href="cart.php?action=add&id=<?= $product['product_id']; ?>" 
                           class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                          </svg>
                          Add
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>