<?php
session_start();
if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['cart_json'])) {
    header('Location: order.php');
    exit;
}
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$cart = json_decode($_POST['cart_json'], true) ?: [];
$products = [];
$products_file = __DIR__ . '/products.json';
if (file_exists($products_file)) {
    $products_arr = json_decode(file_get_contents($products_file), true);
    foreach ($products_arr as $item) {
        $products[$item['id']] = $item;
    }
}
$order_items = [];
$total = 0;
foreach ($cart as $id => $qty) {
    if (isset($products[$id])) {
        $order_items[] = [
            'id' => $id,
            'name' => $products[$id]['name'],
            'price' => $products[$id]['price'],
            'qty' => $qty,
        ];
        $total += $products[$id]['price'] * $qty;
    }
}
$order = [
    'name' => $name,
    'phone' => $phone,
    'items' => $order_items,
    'total' => $total,
    'date' => date('Y-m-d H:i:s'),
];
// Сохраняем заказ в orders.json
$file = __DIR__ . '/orders.json';
$orders = [];
if (file_exists($file)) {
    $orders = json_decode(file_get_contents($file), true) ?: [];
}
$orders[] = $order;
file_put_contents($file, json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
// Отправляем в Telegram
$token = '7780369036:AAHDgni9RQnp9uzTcnsGzAUk8413Zrd38oo';
$chat_id = '7457764790';
$message = "Новая заявка:\nИмя: $name\nТелефон: $phone\n";
foreach ($order_items as $item) {
    $message .= "{$item['name']} x {$item['qty']} = " . ($item['price'] * $item['qty']) . "₽\n";
}
$message .= "Итого: $total ₽";
if ($chat_id) {
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявка отправлена — 3D Маркетплейс</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f7f7f9;
            color: #222;
            font-family: 'Inter', Arial, sans-serif;
        }
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            box-shadow: 0 2px 8px #eee;
        }
        .navbar .navbar-brand {
            color: #ff6a00;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -1px;
        }
        .navbar .nav-link {
            color: #222;
            font-weight: 500;
            margin: 0 12px;
            border-bottom: 2px solid transparent;
            transition: border .2s, color .2s;
        }
        .navbar .nav-link:hover {
            color: #ff6a00;
            border-bottom: 2px solid #ff6a00;
        }
        .wb-header {
            background: #fff;
            color: #222;
            padding: 18px 0 14px 0;
            border-radius: 0 0 18px 18px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px #eee;
            position: relative;
            overflow: hidden;
        }
        .wb-header .container { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; }
        .wb-logo { font-size: 2rem; font-weight: 800; letter-spacing: -2px; color: #ff6a00; text-shadow: none; display: flex; align-items: center; gap: 10px; }
        .wb-cart-btn { background: #232323; color: var(--rusefi-accent); border-radius: 50px; font-weight: 600; box-shadow: 0 2px 8px #111a; border: 2px solid var(--rusefi-accent); display: flex; align-items: center; gap: 8px; padding: 8px 22px; font-size: 1.1rem; transition: background .2s, color .2s, border .2s; }
        .wb-cart-btn:hover { background: var(--rusefi-accent); color: #232323; border-color: #fff; }
        .wb-cart-btn svg { width: 22px; height: 22px; }
        .success-block { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #eee; padding: 32px 32px 24px 32px; max-width: 480px; margin: 48px auto 0 auto; text-align: center; border: 1px solid #e5e5e5; }
        .success-block h1 { color: #ff6a00; font-weight: 800; margin-bottom: 18px; }
        .success-block p { font-size: 1.15rem; margin-bottom: 24px; color: #222; }
        .success-block .btn-primary { background: #ff6a00 !important; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; padding: 10px 32px; color: #fff; }
        .success-block .btn-primary:hover { background: #e55a00 !important; color: #fff; }
        @media (max-width: 600px) {
            .success-block { padding: 18px 8px 12px 8px; }
        }
        .btn, .btn-primary, .btn-outline-primary, .wb-cart-btn {
            background: var(--rusefi-accent) !important;
            color: #181818 !important;
            border: none;
        }
        .btn:hover, .btn-primary:hover, .btn-outline-primary:hover, .wb-cart-btn:hover {
            background: #e07c00 !important;
            color: #181818 !important;
        }
        ::selection { background: #ff6a00; color: #fff; }
        footer { background: #f7f7f9; color: #888; text-align: center; padding: 24px 0 12px 0; font-size: 1rem; border-top: 1px solid #e5e5e5; }
        footer a { color: #ff6a00; text-decoration: none; }
    </style>
</head>
<body>
<div class="bee-bg"></div>
<div class="wb-header">
    <div class="container">
        <span class="wb-logo">
            3D Print
        </span>
        <a href="cart.php" class="wb-cart-btn position-relative">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect x="5" y="9" width="18" height="12" rx="3" stroke="#ff9800" stroke-width="2"/><path d="M9 9V7a5 5 0 0 1 10 0v2" stroke="#ff9800" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
    </div>
</div>
<div class="container">
    <div class="success-block">
        <h1>Заявка отправлена</h1>
        <p>Спасибо! Ваша заявка принята. Мы свяжемся с вами в ближайшее время.</p>
        <a href="index.php" class="btn btn-primary">Вернуться в каталог</a>
    </div>
</div>
<footer>
  &copy; <?php echo date('Y'); ?> 3D Print &mdash; <a href="https://www.shop.rusefi.com">rusefi.com</a>
</footer>
<script>localStorage.removeItem('cart');</script>
</body>
</html> 