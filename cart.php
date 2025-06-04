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
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <a href="cart.php?action=decrease&id=<?php echo $item['product_id']; ?>" class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100">-</a>
                                                <span class="px-3 py-1"><?php echo $item['quantity']; ?></span>
                                                <a href="cart.php?action=increase&id=<?php echo $item['product_id']; ?>" class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-100">+</a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
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
            
            <div class="w-full md:w-1/3">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium">Order Summary</h3>
                    </div>
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-0 py-2 whitespace-nowrap font-medium">Subtotal</td>
                                    <td class="px-0 py-2 whitespace-nowrap text-right">$<?php echo number_format($total, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="px-0 py-2 whitespace-nowrap font-medium">Shipping</td>
                                    <td class="px-0 py-2 whitespace-nowrap text-right">$0.00</td>
                                </tr>
                                <tr>
                                    <td class="px-0 py-2 whitespace-nowrap font-medium">Total</td>
                                    <td class="px-0 py-2 whitespace-nowrap text-right font-bold">$<?php echo number_format($total, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <button id="checkoutButton" class="mt-6 w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Checkout Modal -->
        <div id="checkoutModal" class="hidden fixed inset-0 overflow-y-auto z-50">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Checkout</h3>
                                <form method="POST" class="w-full">
                                    <div class="mb-4">
                                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="shipping_address" name="shipping_address" required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Billing Address</label>
                                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="billing_address" name="billing_address" required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" id="payment_method" name="payment_method" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cash_on_delivery">Cash on Delivery</option>
                                        </select>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" name="checkout" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Place Order
                                        </button>
                                        <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Get the modal and button elements
            const modal = document.getElementById('checkoutModal');
            const checkoutButton = document.getElementById('checkoutButton');
            
            // Function to open modal
            function openModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            // Function to close modal
            function closeModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
            
            // Event listener for checkout button
            checkoutButton.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        </script>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>