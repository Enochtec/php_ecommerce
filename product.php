<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = (int)$_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isLoggedIn()) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Check if product already in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_quantity, $existing_item['cart_id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }
    
    // Update cart count in session
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();
    
    $_SESSION['success'] = 'Product added to cart successfully!';
    redirect('cart.php');
}
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/400'; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo $product['name']; ?></h2>
            <p class="text-muted">Category: 
                <?php 
                if ($product['category_id']) {
                    $stmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
                    $stmt->execute([$product['category_id']]);
                    $category_name = $stmt->fetchColumn();
                    echo $category_name;
                } else {
                    echo 'Uncategorized';
                }
                ?>
            </p>
            <h4 class="my-3">$<?php echo number_format($product['price'], 2); ?></h4>
            
            <p><?php echo $product['description']; ?></p>
            
            <p>Stock: <?php echo $product['stock_quantity']; ?></p>
            
            <?php if (isLoggedIn()): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Please <a href="login.php">login</a> to add items to your cart.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>