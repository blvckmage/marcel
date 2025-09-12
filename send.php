<?php
session_start();
if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['cart_json'])) {
    header('Location: order.php');
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';
$texts = [
    'ru' => [
        'shop' => 'Магазин',
        'contacts' => 'Контакты',
        'cart' => 'Корзина',
        'order_sent' => 'Заявка отправлена!',
        'thanks' => 'Спасибо за заказ! Мы свяжемся с вами в ближайшее время.',
        'back_to_shop' => 'Вернуться в магазин',
        'phone_label' => 'Телефон для связи:',
    ],
    'kz' => [
        'shop' => 'Дүкен',
        'contacts' => 'Байланыс',
        'cart' => 'Себет',
        'order_sent' => 'Өтінім жіберілді!',
        'thanks' => 'Тапсырыс үшін рахмет! Біз сізбен жақын арада байланысамыз.',
        'back_to_shop' => 'Дүкенге оралу',
        'phone_label' => 'Байланыс телефоны:',
    ]
];
$lang = $_COOKIE['lang'] ?? 'ru';
$currency = $_POST['currency'] ?? 'KZT';
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$cart = json_decode($_POST['cart_json'], true) ?: [];
$products = [];
$products_file = __DIR__ . '/products.json';
if (file_exists($products_file)) {
    $products_arr = json_decode(file_get_contents($products_file), true);
    foreach ($products_arr as &$item) {
        if (!is_array($item['name'])) $item['name'] = ['ru'=>$item['name'], 'kz'=>''];
        if (!is_array($item['description'])) $item['description'] = ['ru'=>$item['description'], 'kz'=>''];
    }
    unset($item);
    foreach ($products_arr as $item) {
        $products[$item['id']] = $item;
    }
}
$order_items = [];
$total = 0;
foreach ($cart as $id => $item) {
    if (isset($products[$id])) {
        $qty = is_array($item) ? $item['quantity'] : $item;
        $price = is_array($item) ? $item['price'] : $products[$id]['price'];
        $order_items[] = [
            'id' => $id,
            'name' => is_array($item) ? $item['name'] : $products[$id]['name'],
            'price' => $price,
            'qty' => $qty,
        ];
        $total += $price * $qty;
    }
}
// Функция форматирования цены
function formatPrice($price, $currency) {
    $symbols = ['KZT' => 'KZT', 'RUB' => '₽', 'USD' => '$'];
    return number_format($price, 0, '.', ' ') . ' ' . $symbols[$currency];
}

$order = [
    'name' => $name,
    'phone' => $phone,
    'items' => $order_items,
    'total' => $total,
    'currency' => $currency,
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
$message = "Новая заявка:\nИмя: $name\nТелефон: $phone\nВалюта: $currency\n";
foreach ($order_items as $item) {
    $item_name = is_array($item['name']) ? ($item['name'][$lang] ?? $item['name']['ru'] ?? '') : $item['name'];
    $message .= "$item_name x {$item['qty']} = " . formatPrice($item['price'] * $item['qty'], 'USD') . "\n";
}
$message .= "Итого: " . formatPrice($total, 'USD');
if ($chat_id) {
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message));
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Заявка отправлена — rusEFI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --rusefi-orange: #ff7a1a;
            --rusefi-dark: #232629;
            --rusefi-card: #282b2f;
            --rusefi-text: #fff;
            --rusefi-accent: #ff7a1a;
        }
        body {
            background: var(--rusefi-dark);
            color: var(--rusefi-text);
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        .rusefi-header {
            background: var(--rusefi-orange);
            color: #181818;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
        }
        .rusefi-logo {
            font-size: 2.1rem;
            font-weight: 900;
            letter-spacing: -2px;
            color: #181818;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
        }
        .rusefi-header-menu {
            display: flex;
            align-items: center;
            gap: 32px;
        }
        .rusefi-header-menu a {
            color: #181818;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            padding: 0 8px;
            transition: color 0.18s;
            cursor: pointer;
        }
        .rusefi-header-menu a:hover {
            color: #fff;
        }
        .rusefi-cart {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #181818;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            position: relative;
        }
        .rusefi-cart svg {
            width: 28px;
            height: 28px;
            fill: none;
            stroke: #181818;
            stroke-width: 2;
        }
        .rusefi-cart-count {
            background: #fff;
            color: #181818;
            border-radius: 50%;
            font-size: 0.95em;
            font-weight: 700;
            min-width: 22px;
            min-height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -8px;
            right: -12px;
            border: 2px solid var(--rusefi-orange);
        }
        .rusefi-main {
            max-width: 900px;
            margin: 0 auto;
            padding: 36px 16px 32px 16px;
        }
        .rusefi-title {
            text-align: center;
            font-size: 2.6rem;
            font-weight: 900;
            margin-bottom: 32px;
            color: #fff;
        }
        .success-block {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            padding: 48px 32px 32px 32px;
            max-width: 480px;
            margin: 48px auto 0 auto;
            text-align: center;
            border: 1px solid #333;
        }
        .success-block h1 { color: var(--rusefi-accent); font-weight: 800; margin-bottom: 18px; }
        .success-block p { font-size: 1.15rem; margin-bottom: 24px; color: #fff; }
        .success-block .btn-primary {
            background: var(--rusefi-orange);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.15rem;
            padding: 14px 0;
            color: #181818;
            width: 100%;
            margin-top: 18px;
            transition: background 0.18s, color 0.18s;
        }
        .success-block .btn-primary:hover { background: #ff9a4d; color: #fff; }
        @media (max-width: 600px) {
            .rusefi-header { padding: 0 10px; height: 48px; }
            .rusefi-logo { font-size: 1.3rem; }
            .rusefi-title { font-size: 1.5rem; margin-bottom: 18px; }
            .rusefi-main { padding: 18px 2vw 18px 2vw; }
            .success-block { padding: 18px 8px 12px 8px; }
        }
    </style>
</head>
<body>
    <header class="rusefi-header">
        <span class="rusefi-logo">rusEFI</span>
        <nav class="rusefi-header-menu">
            <a href="index.php"><?= $texts[$lang]['shop'] ?></a>
            <a href="#footer-contacts" id="contacts-link"><?= $texts[$lang]['contacts'] ?></a>
        </nav>
        <div style="display:flex;align-items:center;gap:8px;">
            <form method="get" style="margin:0;padding:0;">
                <button type="submit" name="lang" value="<?= $lang==='ru'?'kz':'ru' ?>" style="background:none;border:none;color:#181818;font-size:1.1rem;cursor:pointer;text-decoration:none;outline:none;box-shadow:none;"> <?= $lang==='ru'?'Рус':'Қаз' ?> </button>
            </form>
            <a href="cart.php" class="rusefi-cart">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span id="cart-count" class="rusefi-cart-count">0</span>
            </a>
        </div>
    </header>
    <main class="rusefi-main">
        <div class="success-block">
            <h1><?= $texts[$lang]['order_sent'] ?></h1>
            <p><?= $texts[$lang]['thanks'] ?></p>
            <a href="index.php" class="btn btn-primary">
                <?= $texts[$lang]['back_to_shop'] ?>
            </a>
        </div>
    </main>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77001234567" style="color:#ff7a1a;">+7 (700) 123-45-67</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id]?.quantity || 0;
        document.getElementById('cart-count').textContent = count;
    }
    document.addEventListener('DOMContentLoaded', function() {
        updateCartUI();
        document.getElementById('contacts-link').onclick = function(e) {
            e.preventDefault();
            document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
        };
        localStorage.removeItem('cart');
    });
    window.addEventListener('storage', function() { updateCartUI(); });
    </script>
</body>
</html>
