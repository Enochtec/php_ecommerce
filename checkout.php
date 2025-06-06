<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.stock_quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $billing_address = sanitize($_POST['billing_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Validation
    if (empty($shipping_address)) $errors[] = 'Shipping address is required';
    if (empty($billing_address)) $errors[] = 'Billing address is required';
    if (empty($payment_method)) $errors[] = 'Payment method is required';
    
    // Check stock availability
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock_quantity']) {
            $errors[] = "Not enough stock for {$item['name']} (Available: {$item['stock_quantity']})";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address, billing_address, payment_method)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $total,
                $shipping_address,
                $billing_address,
                $payment_method
            ]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items and update stock
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update product stock
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE product_id = ?
                ");
                $stmt->execute([
                    $item['quantity'],
                    $item['product_id']
                ]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Update cart count in session
            $_SESSION['cart_count'] = 0;
            
            $pdo->commit();
            
            $_SESSION['order_success'] = true;
            redirect('order_success.php?order_id=' . $order_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}

// Get user details for autofill
$user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user->execute([$user_id]);
$user = $user->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Checkout</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">Your cart is empty. <a href="products.php">Continue shopping</a></div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        Order Summary
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
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Subtotal</th>
                                    <td>Ksh<?php echo number_format($total, 2); ?></td>
                                </tr>
                                <tr>
                                    <th colspan="3">Shipping</th>
                                    <td>Ksh0.00</td>
                                </tr>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <td>Ksh<?php echo number_format($total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Payment Method
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                            <label class="form-check-label" for="credit_card">
                                Credit Card
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                            <label class="form-check-label" for="paypal">
                                PayPal
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash_on_delivery">
                            <label class="form-check-label" for="cod">
                                Cash on Delivery
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Shipping & Billing Information
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?php echo $error; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" required><?php 
                                    echo $user['address'] ?? ''; 
                                ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="same_as_shipping">
                                <label class="form-check-label" for="same_as_shipping">Same as shipping address</label>
                            </div>
                            
                            <div class="mb-3">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="4" required><?php 
                                    echo $user['address'] ?? ''; 
                                ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-fill billing address when checkbox is clicked
document.getElementById('same_as_shipping').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('billing_address').value = document.getElementById('shipping_address').value;
    }
});
</script>

<?php include 'footer.php'; ?>