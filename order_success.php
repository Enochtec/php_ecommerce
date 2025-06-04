
// <!-- <?php
// require_once 'config.php';

// if (!isset($_SESSION['order_success']) {
//     redirect('products.php');
// } -->

// unset($_SESSION['order_success']);

// if (!isset($_GET['order_id'])) {
//     redirect('products.php');
// }

// $order_id = (int)$_GET['order_id'];
// $user_id = isLoggedIn() ? $_SESSION['user_id'] : 0;

// // Get order details
// $stmt = $pdo->prepare("
//     SELECT o.*, COUNT(oi.order_item_id) as item_count 
//     FROM orders o 
//     LEFT JOIN order_items oi ON o.order_id = oi.order_id 
//     WHERE o.order_id = ? AND (o.user_id = ? OR ? = 0)
//     GROUP BY o.order_id
// ");
// $stmt->execute([$order_id, $user_id, $user_id]);
// $order = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$order) {
//     redirect('products.php');
// }
// ?>

// <?php include 'header.php'; ?>

// <div class="container mt-4">
//     <div class="card text-center">
//         <div class="card-header bg-success text-white">
//             <h2>Order Confirmation</h2>
//         </div>
//         <div class="card-body">
//             <h1 class="text-success mb-4"><i class="bi bi-check-circle-fill"></i></h1>
//             <h3 class="card-title">Thank you for your order!</h3>
//             <p class="card-text">Your order has been placed successfully.</p>
//             <div class="alert alert-info text-start">
//                 <h5>Order Details</h5>
//                 <p><strong>Order Number:</strong> #<?php echo $order['order_id']; ?></p>
//                 <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
//                 <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
//                 <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
//             </div>
//             <p>We've sent a confirmation email to your registered email address.</p>
//             <?php if (isLoggedIn()): ?>
//                 <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">View Order Details</a>
//             <?php endif; ?>
//             <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
//         </div>
//         <div class="card-footer text-muted">
//             Need help? <a href="contact.php">Contact us</a>
//         </div>
//     </div>
// </div>

// <?php include 'footer.php'; ?>
