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

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <!-- Categories -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Categories</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li class="px-6 py-3 hover:bg-gray-50">
                        <a href="products.php" class="block text-gray-700 hover:text-blue-600">All Categories</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="px-6 py-3 hover:bg-gray-50">
                            <a href="products.php?category_id=<?php echo $category['category_id']; ?>" class="block text-gray-700 hover:text-blue-600">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Filter -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Filter</h3>
                </div>
                <div class="p-6">
                    <form method="GET" action="products.php">
                        <div class="mb-4">
                            <label for="search" class="block text-gray-700 text-sm font-medium mb-2">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Filter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="w-full lg:w-3/4">
            <h2 class="text-2xl font-bold mb-6">
                <?php if (isset($category_name)): ?>
                    Category: <?php echo htmlspecialchars($category_name); ?>
                <?php elseif ($search): ?>
                    Search Results for "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h2>
            
            <?php if (empty($products)): ?>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                    No products found.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full flex flex-col">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://via.placeholder.com/200'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-800 font-bold mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                                <div class="mt-auto">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md transition-colors">
                                            View Details
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="cart.php?action=add&id=<?php echo $product['product_id']; ?>" 
                                               class="bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md transition-colors">
                                                Add to Cart
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

<?php include 'includes/footer.php'; ?>