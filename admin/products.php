<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$errors = [];
$success = '';

if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $order_count = $stmt->fetchColumn();

    if ($order_count > 0) {
        $errors[] = 'Cannot delete product because it exists in orders.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            $success = 'Product deleted successfully!';
        } else {
            $errors[] = 'Failed to delete product.';
        }
    }
}

$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-2xl font-bold mb-6">Manage Products</h2>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between mb-6">
        <a href="add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow">
            + Add New Product
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden text-sm">
            <thead class="bg-gray-100 text-gray-700 uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">ID</th>
                    <th class="text-left px-4 py-3">Image</th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Category</th>
                    <th class="text-left px-4 py-3">Price</th>
                    <th class="text-left px-4 py-3">Stock</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3"><?php echo $product['product_id']; ?></td>
                        <td class="px-4 py-3">
                            <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/50'; ?>" class="w-12 h-12 object-cover rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></td>
                        <td class="px-4 py-3">Ksh<?php echo number_format($product['price'], 2); ?></td>
                        <td class="px-4 py-3"><?php echo $product['stock_quantity']; ?></td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-sm">Edit</a>
                            <a href="products.php?delete=<?php echo $product['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
