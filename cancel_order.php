<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];

    // Verify ownership + cancellable
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ? AND status IN ('pending', 'processing')");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $update = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?");
        $update->execute([$order_id]);

        $_SESSION['flash'] = "✅ Order #ESC0$order_id has been cancelled successfully.";
    } else {
        $_SESSION['flash'] = "⚠️ Unable to cancel the order. It might already be processed or delivered.";
    }
}

redirect('orders.php');
