<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
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
            
            // Send order confirmation email
            $to = $_SESSION['user_email'];
            $subject = "Order Confirmation #" . $order_id;
            $message = "
                <html>
                <head>
                    <title>Order Confirmation #$order_id</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .order-table { width: 100%; border-collapse: collapse; }
                        .order-table th, .order-table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                        .order-table th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <h2>Thank you for your order!</h2>
                    <p><strong>Order ID:</strong> #$order_id</p>
                    <p><strong>Total Amount:</strong> Ksh" . number_format($total, 2) . "</p>
                    <p>We'll process your order shortly.</p>
                    <h3>Order Details:</h3>
                    <table class='order-table'>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                        " . implode('', array_map(function($item) {
                            return "<tr>
                                <td>{$item['name']}</td>
                                <td>Ksh" . number_format($item['price'], 2) . "</td>
                                <td>{$item['quantity']}</td>
                                <td>Ksh" . number_format($item['price'] * $item['quantity'], 2) . "</td>
                            </tr>";
                        }, $cart_items)) . "
                        <tr>
                            <td colspan='3'><strong>Total</strong></td>
                            <td><strong>Ksh" . number_format($total, 2) . "</strong></td>
                        </tr>
                    </table>
                    <h3>Customer Information</h3>
                    <p><strong>Shipping Address:</strong><br>$shipping_address</p>
                    <p><strong>Billing Address:</strong><br>$billing_address</p>
                    <p><strong>Payment Method:</strong> " . ucwords(str_replace('_', ' ', $payment_method)) . "</p>
                </body>
                </html>
            ";
            
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: ewanyama43@gmail.com" . "\r\n";
            
            mail($to, $subject, $message, $headers);
            
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6">Checkout</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6">
                Your cart is empty. <a href="products.php" class="text-blue-600 hover:text-blue-800 font-semibold">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Order Summary -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white rounded-lg shadow-md mb-6">
                        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium">Order Summary</h3>
                        </div>
                        <div class="p-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/50'; ?>" width="50" class="mr-3">
                                                    <span><?php echo $item['name']; ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">Ksh<?php echo number_format($item['price'], 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['quantity']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">Ksh<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <th colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <td class="px-6 py-4 whitespace-nowrap">Ksh<?php echo number_format($total, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping</th>
                                        <td class="px-6 py-4 whitespace-nowrap">Ksh0.00</td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold">Ksh<?php echo number_format($total, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Checkout Form -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white rounded-lg shadow-md sticky top-4">
                        <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium">Shipping & Billing Information</h3>
                        </div>
                        <div class="p-6">
                            <form method="POST">
                                <?php if (!empty($errors)): ?>
                                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                                        <?php foreach ($errors as $error): ?>
                                            <div><?php echo $error; ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="shipping_address" name="shipping_address" rows="4" required><?php 
                                        echo htmlspecialchars($_POST['shipping_address'] ?? ($user['address'] ?? '')); 
                                    ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Billing Address</label>
                                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="billing_address" name="billing_address" rows="4" required><?php 
                                        echo htmlspecialchars($_POST['billing_address'] ?? ($user['address'] ?? '')); 
                                    ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                        <option value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                                        <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash_on_delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Place Order
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>