<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle cart actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    switch ($_GET['action']) {
        case 'add':
            // Check if product already in cart
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                // Update quantity
                $new_quantity = $existing_item['quantity'] + 1;
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                $stmt->execute([$new_quantity, $existing_item['cart_id']]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$user_id, $product_id]);
            }
            break;
            
        case 'remove':
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            break;
            
        case 'increase':
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            break;
            
        case 'decrease':
            // Check current quantity
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $quantity = $stmt->fetchColumn();
            
            if ($quantity > 1) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            }
            break;
    }
    
    // Update cart count in session
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();
    
    redirect('cart.php');
}

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

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Validate form data
    $errors = [];
    $shipping_address = sanitize($_POST['shipping_address']);
    $billing_address = sanitize($_POST['billing_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    if (empty($shipping_address)) $errors[] = 'Shipping address is required';
    if (empty($billing_address)) $errors[] = 'Billing address is required';
    if (empty($payment_method)) $errors[] = 'Payment method is required';
    
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
            
            // Add order items
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
            
            // Send order confirmation email
            $to = $_SESSION['user_email'];
            $subject = "Order Confirmation #" . $order_id;
            $message = "
                <html>
                <head>
                    <title>Order Confirmation</title>
                </head>
                <body>
                    <h2>Thank you for your order!</h2>
                    <p>Order ID: #$order_id</p>
                    <p>Total Amount: Ksh" . number_format($total, 2) . "</p>
                    <p>We'll process your order shortly.</p>
                    <h3>Order Details:</h3>
                    <ul>
                        " . implode('', array_map(function($item) {
                            return "<li>{$item['name']} - {$item['quantity']} x Ksh" . number_format($item['price'], 2) . "</li>";
                        }, $cart_items)) . "
                    </ul>
                </body>
                </html>
            ";
            
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: ewanyama43@gmail.com" . "\r\n";
            
            mail($to, $subject, $message, $headers);
            
            $_SESSION['success'] = 'Order placed successfully!';
            redirect('orders.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Shopping Cart</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (empty($cart_items)): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6">
            Your cart is empty. <a href="products.php" class="text-blue-600 hover:text-blue-800 font-semibold">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Cart Items Section -->
            <div class="w-full md:w-2/3">
                <div class="bg-white rounded-lg shadow-md mb-6">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <a href="cart.php?action=decrease&id=<?php echo $item['product_id']; ?>" class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100">-</a>
                                                <span class="px-3 py-1"><?php echo $item['quantity']; ?></span>
                                                <a href="cart.php?action=increase&id=<?php echo $item['product_id']; ?>" class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100">+</a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">Ksh<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="cart.php?action=remove&id=<?php echo $item['product_id']; ?>" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">Remove</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Checkout Summary Section -->
            <div class="w-full md:w-1/3">
                <div class="bg-white rounded-lg shadow-md sticky top-4">
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium">Order Summary</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="font-medium">Subtotal</span>
                                <span>Ksh<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Shipping</span>
                                <span>Ksh0.00</span>
                            </div>
                            <div class="border-t border-gray-200 pt-4 flex justify-between font-bold">
                                <span>Total</span>
                                <span>Ksh<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Checkout Form -->
                        <form method="POST" class="mt-6 space-y-4">
                            <div>
                                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="shipping_address" name="shipping_address" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                            </div>
                            
                            <div>
                                <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Billing Address</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="billing_address" name="billing_address" required><?php echo isset($_POST['billing_address']) ? htmlspecialchars($_POST['billing_address']) : ''; ?></textarea>
                            </div>
                            
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                    <option value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                                    <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                    <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash_on_delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="checkout" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
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