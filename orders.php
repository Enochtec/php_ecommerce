<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get all user orders except those cancelled more than 24 hours ago
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.order_item_id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? 
    AND (
        o.status != 'cancelled' 
        OR (o.status = 'cancelled' AND o.updated_at >= NOW() - INTERVAL 24 HOUR)
    )
    GROUP BY o.order_id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
    <h2 class="text-2xl font-bold mb-6">My Orders</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="bg-green-100 text-green-800 p-4 mb-6 rounded-md shadow">
            <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="bg-blue-100 text-blue-700 p-4 rounded-md mb-4">
            You haven't placed any orders yet. <a href="products.php" class="underline font-medium">Start shopping</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Order #ESC0</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Items</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Total</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-4 py-2">ESC0<?= $order['order_id']; ?></td>
                            <td class="px-4 py-2"><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="px-4 py-2"><?= $order['item_count']; ?></td>
                            <td class="px-4 py-2">Ksh<?= number_format($order['total_amount'], 2); ?></td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded 
                                    <?php 
                                        switch ($order['status']) {
                                            case 'pending': echo 'bg-yellow-200 text-yellow-800'; break;
                                            case 'processing': echo 'bg-blue-200 text-blue-800'; break;
                                            case 'shipped': echo 'bg-indigo-200 text-indigo-800'; break;
                                            case 'delivered': echo 'bg-green-200 text-green-800'; break;
                                            case 'cancelled': echo 'bg-red-200 text-red-800'; break;
                                            default: echo 'bg-gray-200 text-gray-800';
                                        }
                                    ?>
                                ">
                                    <?= ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="order_details.php?id=<?= $order['order_id']; ?>" 
                                   class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm font-medium">
                                    View
                                </a>
                                <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                    <form action="cancel_order.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                        <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium">
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
