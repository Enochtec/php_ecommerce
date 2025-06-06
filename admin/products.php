<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$errors = [];
$success = '';

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    // Check if product exists in any order
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $order_count = $stmt->fetchColumn();
    
    if ($order_count > 0) {
        $errors[] = 'Cannot delete product because it exists in orders.';
    } else {
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            $success = 'Product deleted successfully!';
        } else {
            $errors[] = 'Failed to delete product.';
        }
    }
}

// Get all products with category names
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Products</h2>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <?php echo displayError($error); ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?php echo displaySuccess($success); ?>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="add_product.php" class="btn btn-primary">Add New Product</a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td>
                            <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/50'; ?>" width="50" alt="<?php echo $product['name']; ?>">
                        </td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category_name'] ?: 'Uncategorized'; ?></td>
                        <td>Ksh<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="products.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>