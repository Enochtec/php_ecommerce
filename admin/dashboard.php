<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Get stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn();
$total_revenue = $total_revenue ?: 0;

// Get recent orders
$recent_orders = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text display-4"><?php echo $total_products; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $total_users; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text display-4"><?php echo $total_orders; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text display-4">$<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Recent Orders
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #ESC0</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['username']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Admin Links
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="products.php" class="btn btn-primary">Manage Products</a>
                        <a href="categories.php" class="btn btn-success">Manage Categories</a>
                        <a href="users.php" class="btn btn-info">Manage Users</a>
                        <a href="orders.php" class="btn btn-warning">Manage Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>