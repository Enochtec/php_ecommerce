<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = (int)$_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isLoggedIn()) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_quantity, $existing_item['cart_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart_count'] = $stmt->fetchColumn();

    $_SESSION['success'] = 'Product added to cart successfully!';
    redirect('cart.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-slate-100 min-h-screen">

<?php include 'includes/header.php'; ?>

<!-- Breadcrumb Navigation -->
<div class="bg-white/80 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 py-3">
        <nav class="flex items-center space-x-2 text-sm text-slate-600">
            <a href="index.php" class="hover:text-slate-900 transition-colors">Home</a>
            <span class="text-slate-400">/</span>
            <a href="products.php" class="hover:text-slate-900 transition-colors">Products</a>
            <span class="text-slate-400">/</span>
            <span class="text-slate-900 font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8 lg:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
        
        <!-- Product Image Section -->
        <div class="space-y-4 animate-fade-in">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200/50">
                    <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/600x600?text=Product+Image'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-[500px] object-cover transition-transform duration-700 group-hover:scale-105">
                    
                    <!-- Image overlay with zoom icon -->
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300 flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/90 backdrop-blur-sm rounded-full p-3">
                            <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Thumbnail gallery placeholder -->
            <div class="flex space-x-3 overflow-x-auto pb-2">
                <?php for($i = 0; $i < 4; $i++): ?>
                <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 rounded-lg border-2 border-transparent hover:border-blue-500 transition-colors cursor-pointer">
                    <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/80x80'; ?>" 
                         alt="Thumbnail <?php echo $i + 1; ?>" 
                         class="w-full h-full object-cover rounded-md">
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Product Details Section -->
        <div class="space-y-6 animate-slide-up">
            <!-- Product Title & Category -->
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-blue-800 border border-blue-200/50">
                        <?php 
                        if ($product['category_id']) {
                            $stmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
                            $stmt->execute([$product['category_id']]);
                            echo htmlspecialchars($stmt->fetchColumn());
                        } else {
                            echo 'Uncategorized';
                        }
                        ?>
                    </span>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse-slow"></div>
                            In Stock
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 leading-tight">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
            </div>

            <!-- Price Section -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-200/50">
                <div class="flex items-baseline space-x-3">
                    <span class="text-4xl font-bold text-green-700">
                        $<?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="text-lg text-slate-500 line-through">
                        $<?php echo number_format($product['price'] * 1.2, 2); ?>
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        17% OFF
                    </span>
                </div>
                <p class="text-sm text-green-600 mt-2">Free shipping on orders over $50</p>
            </div>

            <!-- Product Description -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200/50">
                <h3 class="text-lg font-semibold text-slate-900 mb-3">Product Description</h3>
                <p class="text-slate-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>

            <!-- Stock Information -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/50">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-700">Available Stock:</span>
                    <span class="text-sm font-bold text-slate-900"><?php echo $product['stock_quantity']; ?> units</span>
                </div>
                <div class="mt-2 bg-slate-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500" 
                         style="width: <?php echo min(($product['stock_quantity'] / 100) * 100, 100); ?>%"></div>
                </div>
            </div>

            <!-- Add to Cart Section -->
            <?php if (isLoggedIn()): ?>
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200/50">
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-slate-700 mb-2">Quantity</label>
                            <div class="flex items-center space-x-3">
                                <button type="button" onclick="decreaseQuantity()" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input 
                                    type="number" 
                                    id="quantity" 
                                    name="quantity" 
                                    value="1" 
                                    min="1" 
                                    max="<?php echo $product['stock_quantity']; ?>" 
                                    class="w-20 text-center border-2 border-slate-200 rounded-xl px-3 py-2 font-semibold focus:outline-none focus:border-blue-500 transition-colors"
                                >
                                <button type="button" onclick="increaseQuantity()" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" name="add_to_cart" 
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h8"></path>
                                </svg>
                                <span>Add to Cart</span>
                            </button>
                            
                            <button type="button" 
                                    class="px-4 py-4 bg-white border-2 border-slate-200 hover:border-red-300 rounded-xl transition-colors group">
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 rounded-2xl p-6">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-amber-800">Login Required</h3>
                            <p class="text-amber-700">
                                Please <a href="login.php" class="font-semibold underline hover:text-amber-900 transition-colors">login</a> to add items to your cart.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Additional Features -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-white rounded-xl border border-slate-200/50">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <p class="text-xs font-medium text-slate-700">Free Shipping</p>
                </div>
                <div class="text-center p-4 bg-white rounded-xl border border-slate-200/50">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-xs font-medium text-slate-700">Quality Guaranteed</p>
                </div>
                <div class="text-center p-4 bg-white rounded-xl border border-slate-200/50">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <p class="text-xs font-medium text-slate-700">Easy Returns</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>