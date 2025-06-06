<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn();
$total_revenue = $total_revenue ?: 0;

$recent_orders = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-7xl mx-auto px-4 py-6">
    <h2 class="text-2xl font-semibold mb-6">Admin Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-600 text-white p-4 rounded shadow">
            <h5 class="text-lg font-medium">Total Products</h5>
            <p class="text-4xl mt-2"><?php echo $total_products; ?></p>
        </div>
        <div class="bg-green-600 text-white p-4 rounded shadow">
            <h5 class="text-lg font-medium">Total Users</h5>
            <p class="text-4xl mt-2"><?php echo $total_users; ?></p>
        </div>
        <div class="bg-cyan-600 text-white p-4 rounded shadow">
            <h5 class="text-lg font-medium">Total Orders</h5>
            <p class="text-4xl mt-2"><?php echo $total_orders; ?></p>
        </div>
        <div class="bg-yellow-500 text-white p-4 rounded shadow">
            <h5 class="text-lg font-medium">Total Revenue</h5>
            <p class="text-4xl mt-2">Ksh<?php echo number_format($total_revenue, 2); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border rounded shadow">
            <div class="border-b px-4 py-2 font-semibold text-gray-700">
                Recent Orders
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm text-left border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border">Order #ESC0</th>
                            <th class="px-4 py-2 border">Customer</th>
                            <th class="px-4 py-2 border">Date</th>
                            <th class="px-4 py-2 border">Total</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?php echo $order['order_id']; ?></td>
                                <td class="px-4 py-2"><?php echo $order['username']; ?></td>
                                <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td class="px-4 py-2">Ksh<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="px-4 py-2">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded 
                                        <?php 
                                            switch ($order['status']) {
                                                case 'pending': echo 'bg-yellow-400 text-white'; break;
                                                case 'processing': echo 'bg-cyan-500 text-white'; break;
                                                case 'shipped': echo 'bg-blue-600 text-white'; break;
                                                case 'delivered': echo 'bg-green-600 text-white'; break;
                                                case 'cancelled': echo 'bg-red-600 text-white'; break;
                                                default: echo 'bg-gray-400 text-white';
                                            }
                                        ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="inline-block px-3 py-1 text-white bg-blue-600 rounded text-xs">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="mt-4">
                    <a href="orders.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm">View All Orders</a>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded shadow">
            <div class="border-b px-4 py-2 font-semibold text-gray-700">
                Admin Links
            </div>
            <div class="p-4 space-y-3">
                <a href="products.php" class="block bg-blue-600 text-white text-center py-2 rounded">Manage Products</a>
                <a href="categories.php" class="block bg-green-600 text-white text-center py-2 rounded">Manage Categories</a>
                <a href="users.php" class="block bg-cyan-600 text-white text-center py-2 rounded">Manage Users</a>
                <a href="orders.php" class="block bg-yellow-500 text-white text-center py-2 rounded">Manage Orders</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
