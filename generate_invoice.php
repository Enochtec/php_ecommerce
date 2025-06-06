<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_POST['order_id'])) {
    redirect('orders.php');
}

$order_id = (int)$_POST['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $order_items));
$tax_rate = 0.08;
$tax_amount = $subtotal;
$shipping = 15.00;
$discount = 0.00;
$grand_total = $subtotal + $shipping - $discount;

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #ESC0<?= $order_id ?></title>
    <style>
        @page {
            margin: 0.3in;
            size: A4;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0; 
            color:rgb(22, 35, 46); 
            font-size: 15px;
            background-color: #fff;
        }
        .invoice-container { 
            max-width: 100%;
            margin: 0;
            background: #fff;
            padding: 0;
        }
        .header-section {
            background-color: #f8fafc;
            color: rgb(186, 118, 17);
            padding: 30px;
            margin-bottom: 25px;
            border-radius: 8px;
            border: 2px solid rgb(225, 243, 253);
            border-left: 4px solid #4ECDC4;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .company-info {
            display: flex;
            flex-direction: column;
        }
        .company-name {
            font-size: 36px;
            font-weight: 900;
            color:  rgb(231, 143, 50);
            letter-spacing: 2px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .company-tagline {
            font-size: 18px;
            color:  rgb(49, 233, 181);
            font-style: italic;
            font-weight: 300;
            opacity: 0.9;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-title {
            font-size: 42px;
            font-weight: 900;
            color: rgb(186, 118, 17);
            margin-bottom: 10px;
            letter-spacing: 4px;
        }
        .invoice-number {
            font-size: 22px;
            color: rgb(55, 24, 167);
            font-weight: bold;
            margin-bottom: 8px;
        }
        .invoice-date {
            font-size: 18px;
            color: black;
            opacity: 0.9;
        }
        .content-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 20px;
        }
        .info-card {
            width: 48%;
            padding: 20px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .info-card-title {
            font-size: 20px;
            font-weight: bold;
            color: #4ECDC4;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #4ECDC4;
        }
        .info-card-content {
            font-size: 16px;
            line-height: 1.6;
            color: #4a5568;
        }
        .company-details-section {
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #4ECDC4;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-paid {
            background-color: #48bb78;
            color: white;
        }
        .status-pending {
            background-color: #ed8936;
            color: white;
        }
        .status-shipped {
            background-color: #4299e1;
            color: white;
        }
        .items-section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 24px;
            font-weight: bold;
            color: #4ECDC4;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 3px solid #e2e8f0;
        }
        table.items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 0; 
            font-size: 16px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .items-table th { 
            background-color: #4ECDC4;
            color: white;
            text-align: left; 
            padding: 15px; 
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #e2e8f0;
            font-size: 16px;
            background: white;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .totals-container {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
        }
        .totals-table { 
            width: 380px;
            font-size: 17px;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .totals-table td {
            padding: 12px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        .totals-table tr:last-child td {
            border-bottom: none;
        }
        .totals-table .subtotal-row {
            background-color: #f8fafc;
        }
        .grand-total-row { 
            font-weight: bold; 
            font-size: 19px;
            background-color: #4ECDC4;
            color: white;
        }
        .footer-section {
            margin-top: 35px;
            padding: 25px;
            background-color: #f7fafc;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .footer-section .thank-you {
            font-size: 26px;
            font-weight: bold;
            color:rgb(212, 147, 71);
            margin-bottom: 12px;
        }
        .footer-section .contact-info {
            font-size: 17px;
            color: #4a5568;
            line-height: 1.6;
        }
        .footer-section .contact-details {
            font-weight: bold;
            color:rgb(28, 6, 172);
            margin-top: 8px;
            font-size: 18px;
        }
        .brand-accent {
            color:rgb(63, 50, 204);
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header-section">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-name">SHOP EASY</div>
                    <div class="company-tagline">Your Trusted Shopping Partner</div>
                </div>
                
                <div class="invoice-details">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#ESC0<?= $order_id ?></div>
                    <div class="invoice-date"><?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <div class="content-section">
            <div class="info-card">
                <div class="info-card-title">From</div>
                <div class="company-details-section">
                    <div><strong>Shop Easy Inc.</strong></div>
                    <div>123 Business Street</div>
                    <div>Eldoret, Kenya 10001</div>
                    <div>Phone: (123) 456-7890</div>
                    <div>Email: info@shopeasy.com</div>
                    <div>Website: www.shopeasy.com</div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-card-title">Bill To</div>
                <div class="info-card-content">
                    <?= nl2br(htmlspecialchars($order['billing_address'])) ?>
                    
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                        <div><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></div>
                        <div><strong>Due Date:</strong> <?= date('F j, Y', strtotime($order['created_at'] . ' +30 days')) ?></div>
                        <div>
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                        <?php if ($order['status'] === 'shipped' && !empty($order['tracking_number'])): ?>
                            <div style="margin-top: 8px;"><strong>Tracking:</strong> <?= $order['tracking_number'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="items-section">
            <div class="section-title">Order Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th class="text-center" style="width: 15%;">Qty</th>
                        <th class="text-right" style="width: 17.5%;">Unit Price</th>
                        <th class="text-right" style="width: 17.5%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-right">Ksh<?= number_format($item['price'], 2) ?></td>
                        <td class="text-right">Ksh<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="totals-container">
            <table class="totals-table">
                <tr class="subtotal-row">
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right">Ksh<?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php if ($discount > 0): ?>
                <tr class="subtotal-row">
                    <td><strong>Discount</strong></td>
                    <td class="text-right">-Ksh<?= number_format($discount, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="subtotal-row">
                    <td><strong>Shipping</strong></td>
                    <td class="text-right">Ksh<?= number_format($shipping, 2) ?></td>
                </tr>
                <tr class="subtotal-row">
                    <td><strong>Total</strong></td>
                    <td class="text-right">Ksh<?= number_format($grand_total, 2) ?></td>
                </tr>
                <tr class="grand-total-row">
                    <td><strong>AMOUNT DUE</strong></td>
                    <td class="text-right"><strong>Ksh<?= number_format($grand_total, 2) ?></strong></td>
                </tr>
            </table>
        </div>

        <div class="footer-section">
            <div class="thank-you">Thank you for shopping with Us!</div>
            <div class="contact-info">
                If you have any questions about this invoice, please contact:
                <div class="contact-details">
                    Email: billing@yourcompany.com | Phone: (415) 123-4567
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$options->set('isHtml5ParserEnabled', true);
$options->set('dpi', 150);
$options->set('defaultPaperSize', 'A4');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the generated PDF
$dompdf->stream("invoice_ESC0{$order_id}.pdf", [
    'Attachment' => true
]);
exit;
?>