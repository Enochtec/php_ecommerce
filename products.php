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

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Categories
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="products.php">All Categories</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item">
                            <a href="products.php?category_id=<?php echo $category['category_id']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Filter
                </div>
                <div class="card-body">
                    <form method="GET" action="products.php">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo $search ?? ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="mb-4">
                <?php if (isset($category_name)): ?>
                    Category: <?php echo $category_name; ?>
                <?php elseif ($search): ?>
                    Search Results for "<?php echo $search; ?>"
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h2>
            
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No products found.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/200'; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                    <p class="card-text">$<?php echo number_format($product['price'], 2); ?></p>
                                    <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary">View Details</a>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="cart.php?action=add&id=<?php echo $product['product_id']; ?>" class="btn btn-success">Add to Cart</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>