<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE order_id = ? AND user_id = ?
");
$stmt->execute([$order_id, $user_id]);
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
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Order #ESC0<?php echo $order['order_id']; ?></h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    Order Information
                </div>
                <div class="card-body">
                    <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge 
                            <?php 
                            switch ($order['status']) {
                                case 'pending': echo 'bg-warning'; break;
                                case 'processing': echo 'bg-info'; break;
                                case 'shipped': echo 'bg-primary'; break;
                                case 'delivered': echo 'bg-success'; break;
                                case 'cancelled': echo 'bg-danger'; break;
                                default: echo 'bg-secondary';
                            }
                            ?>
                        ">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    Shipping Address
                </div>
                <div class="card-body">
                    <p><?php echo nl2br($order['shipping_address']); ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    Billing Address
                </div>
                <div class="card-body">
                    <p><?php echo nl2br($order['billing_address']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Order Items
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/50'; ?>" width="50" class="me-2">
                                        <?php echo $item['name']; ?>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Subtotal</th>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <th colspan="3">Shipping</th>
                                <td>$0.00</td>
                            </tr>
                            <tr>
                                <th colspan="3">Total</th>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>