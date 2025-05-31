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

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: {
            50: '#f0f9ff',
            100: '#e0f2fe',
            200: '#bae6fd',
            300: '#7dd3fc',
            400: '#38bdf8',
            500: '#0ea5e9',
            600: '#0284c7',
            700: '#0369a1',
            800: '#075985',
            900: '#0c4a6e',
          }
        },
        fontFamily: {
          sans: ['Inter', 'sans-serif'],
        },
      }
    }
  }
</script>

<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-4xl mx-auto">
    <!-- Order Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Order #ESC0<?php echo $order['order_id']; ?></h1>
        <p class="text-sm text-gray-500 mt-1">
          Placed on <?php echo date('M d, Y \a\t H:i', strtotime($order['created_at'])); ?>
        </p>
      </div>
      <div class="mt-4 md:mt-0">
        <a href="orders.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to orders
        </a>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <!-- Order Status Banner -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <?php
              $statusColor = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'processing' => 'bg-blue-100 text-blue-800',
                'shipped' => 'bg-indigo-100 text-indigo-800',
                'delivered' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800'
              ];
              $defaultColor = 'bg-gray-100 text-gray-800';
              ?>
              <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $statusColor[strtolower($order['status'])] ?? $defaultColor; ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">
                <?php
                $statusMessage = [
                  'pending' => 'Your order is being processed',
                  'processing' => 'We are preparing your order',
                  'shipped' => 'Your order is on the way',
                  'delivered' => 'Your order has been delivered',
                  'cancelled' => 'Your order has been cancelled'
                ];
                echo $statusMessage[strtolower($order['status'])] ?? 'Your order is being processed';
                ?>
              </p>
              <?php if ($order['status'] === 'shipped'): ?>
                <p class="text-sm text-gray-500 mt-1">Expected delivery: <?php echo date('M d, Y', strtotime('+3 days')); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="mt-4 sm:mt-0">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
              Need help with this order?
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
        <!-- Order Summary -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Order summary</h2>
          <div class="space-y-6">
            <div class="flex items-center justify-between">
              <dt class="text-sm text-gray-600">Order number</dt>
              <dd class="text-sm font-medium text-gray-900">ESC0<?php echo $order['order_id']; ?></dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-gray-600">Payment method</dt>
              <dd class="text-sm font-medium text-gray-900">
                <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?>
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-gray-600">Order date</dt>
              <dd class="text-sm font-medium text-gray-900">
                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-gray-600">Total amount</dt>
              <dd class="text-sm font-medium text-gray-900">
                $<?php echo number_format($order['total_amount'], 2); ?>
              </dd>
            </div>
          </div>
        </div>

        <!-- Shipping & Billing -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping & Billing</h2>
          <div class="space-y-6">
            <div>
              <h3 class="text-sm font-medium text-gray-900">Shipping address</h3>
              <address class="mt-2 text-sm text-gray-500 not-italic">
                <?php echo nl2br($order['shipping_address']); ?>
              </address>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-900">Billing address</h3>
              <address class="mt-2 text-sm text-gray-500 not-italic">
                <?php echo nl2br($order['billing_address']); ?>
              </address>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="border-t border-gray-200 px-6 py-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Items in your order</h2>
        <div class="space-y-8">
          <?php foreach ($order_items as $item): ?>
            <div class="flex flex-col sm:flex-row">
              <div class="flex-shrink-0 mb-4 sm:mb-0">
                <img src="<?php echo $item['image_url'] ?: 'https://via.placeholder.com/150'; ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                     class="w-20 h-20 rounded-md object-cover object-center">
              </div>
              <div class="ml-0 sm:ml-6 flex-1">
                <div class="flex flex-col sm:flex-row sm:justify-between">
                  <div>
                    <h3 class="text-base font-medium text-gray-900">
                      <?php echo $item['name']; ?>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">SKU: <?php echo substr(md5($item['product_id']), 0, 8); ?></p>
                  </div>
                  <p class="mt-2 sm:mt-0 text-sm font-medium text-gray-900">
                    $<?php echo number_format($item['price'], 2); ?>
                  </p>
                </div>
                <div class="mt-4 flex-1 flex items-end justify-between">
                  <p class="text-sm text-gray-500">Qty <?php echo $item['quantity']; ?></p>
                  <div class="ml-4">
                    <button type="button" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                      Buy it again
                    </button>
                    <span class="mx-2 text-gray-300">|</span>
                    <button type="button" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                      Write a review
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Order Total -->
      <div class="border-t border-gray-200 px-6 py-6 bg-gray-50">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Order total</h2>
        <div class="space-y-4">
          <div class="flex justify-between">
            <dt class="text-sm text-gray-600">Subtotal</dt>
            <dd class="text-sm font-medium text-gray-900">
              $<?php echo number_format($order['total_amount'], 2); ?>
            </dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-sm text-gray-600">Shipping</dt>
            <dd class="text-sm font-medium text-gray-900">$0.00</dd>
          </div>
          <div class="flex justify-between border-t border-gray-200 pt-4">
            <dt class="text-base font-medium text-gray-900">Total</dt>
            <dd class="text-base font-medium text-gray-900">
              $<?php echo number_format($order['total_amount'], 2); ?>
            </dd>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-4">
      <button type="button" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        Download invoice
      </button>
      <button type="button" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        Track order
      </button>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>