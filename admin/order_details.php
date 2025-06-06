<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $order['status'] = $status;
        $success = 'Order status updated successfully!';
    } else {
        $errors[] = 'Failed to update order status.';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h2 class="text-2xl font-bold mb-6">Order #<?php echo $order['order_id']; ?></h2>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Info -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                <p><span class="font-semibold">Customer:</span> <?php echo $order['username']; ?> (<?php echo $order['email']; ?>)</p>
                <p><span class="font-semibold">Order Date:</span> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>

                <form method="POST" class="my-4 flex items-center space-x-4">
                    <label for="status" class="font-semibold">Status:</label>
                    <select name="status" id="status" class="rounded border px-3 py-1 text-sm">
                        <?php
                        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                        foreach ($statuses as $s) {
                            $selected = $order['status'] === $s ? 'selected' : '';
                            echo "<option value='$s' $selected>" . ucfirst($s) . "</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="update_status" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                        Update
                    </button>
                </form>

                <p><span class="font-semibold">Payment Method:</span> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                <p><span class="font-semibold">Total Amount:</span> Ksh<?php echo number_format($order['total_amount'], 2); ?></p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Shipping Address</h3>
                <p><?php echo nl2br($order['shipping_address']); ?></p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Billing Address</h3>
                <p><?php echo nl2br($order['billing_address']); ?></p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 text-left">
                        <tr>
                            <th class="p-2">Product</th>
                            <th class="p-2">Price</th>
                            <th class="p-2">Quantity</th>
                            <th class="p-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr class="border-b">
                                <td class="p-2 flex items-center space-x-2">
                                    <img src="../<?php echo $item['image_url'] ?: 'https://via.placeholder.com/50'; ?>" class="w-10 h-10 object-cover rounded" />
                                    <span><?php echo $item['name']; ?></span>
                                </td>
                                <td class="p-2">Ksh<?php echo number_format($item['price'], 2); ?></td>
                                <td class="p-2"><?php echo $item['quantity']; ?></td>
                                <td class="p-2">Ksh<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="font-semibold">
                        <tr>
                            <td colspan="3" class="p-2 text-right">Subtotal</td>
                            <td class="p-2">Ksh<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="p-2 text-right">Shipping</td>
                            <td class="p-2">Ksh0.00</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="p-2 text-right">Total</td>
                            <td class="p-2">Ksh<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($success)): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?php echo $success; ?>',
        confirmButtonColor: '#3085d6',
    });
</script>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: `<?php foreach ($errors as $error) echo $error . "<br>"; ?>`,
        confirmButtonColor: '#d33',
    });
</script>
<?php endif; ?>
