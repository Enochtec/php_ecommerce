<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$errors = [];
$success = '';

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Check if category has products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();
    
    if ($product_count > 0) {
        $errors[] = 'Cannot delete category because it has products assigned to it.';
    } else {
        // Delete category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        if ($stmt->execute([$category_id])) {
            $success = 'Category deleted successfully!';
        } else {
            $errors[] = 'Failed to delete category.';
        }
    }
}

// Get all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manage Categories</h2>
        <a href="add_category.php" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add New Category
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="mb-6">
            <?php foreach ($errors as $error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $category['category_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($category['description']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="edit_category.php?id=<?php echo $category['category_id']; ?>" class="text-yellow-600 hover:text-yellow-900 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="categories.php?delete=<?php echo $category['category_id']; ?>" class="text-red-600 hover:text-red-900 flex items-center" onclick="return confirm('Are you sure you want to delete this category?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>