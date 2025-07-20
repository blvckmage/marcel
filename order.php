<?php
session_start();
$products = [];
$products_file = __DIR__ . '/products.json';
if (file_exists($products_file)) {
    $products_arr = json_decode(file_get_contents($products_file), true);
    foreach ($products_arr as $item) {
        $products[$item['id']] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — Оформление заказа</title>
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
        .order-cart-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .order-cart-card {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            padding: 24px 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            border: 1px solid #333;
        }
        .order-cart-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            background: #232323;
        }
        .order-cart-info { flex: 1 1 auto; }
        .order-cart-title { font-weight: 700; font-size: 1.18rem; color: var(--rusefi-accent); margin-bottom: 4px; }
        .order-cart-price { color: var(--rusefi-accent); font-size: 1.1rem; font-weight: 700; }
        .order-cart-desc { color: #e0e0e0; font-size: 1rem; }
        .order-cart-qty { font-size: 1.15rem; font-weight: 600; margin-top: 10px; color: #fff; }
        .order-summary {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            padding: 18px 24px;
            font-size: 1.15rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 24px;
            border: 1px solid #333;
            text-align: right;
        }
        .order-summary .order-total { color: var(--rusefi-accent); font-weight: 700; margin-left: 8px; }
        .order-form {
            background: var(--rusefi-card);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            padding: 32px 32px 24px 32px;
            max-width: 420px;
            margin: 0 auto 32px auto;
            border: 1px solid #333;
        }
        .order-form input[type="text"], .order-form input[type="tel"] {
            background: #232323;
            color: #fff;
            border: 1.5px solid #333;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 1.15rem;
            margin-bottom: 18px;
            width: 100%;
            transition: border .2s, background .2s;
        }
        .order-form input[type="text"]:focus, .order-form input[type="tel"]:focus {
            border-color: #ff7a1a;
            outline: none;
            background: #181818;
        }
        .order-form label { color: #fff; font-weight: 500; margin-bottom: 6px; }
        .order-form .btn-primary {
            background: var(--rusefi-orange);
            color: #181818;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.15rem;
            padding: 14px 0;
            margin-top: 8px;
            width: 100%;
            transition: background 0.18s, color 0.18s;
        }
        .order-form .btn-primary:hover { background: #ff9a4d; color: #fff; }
        .order-back-btn, .order-back-btn:visited {
            background: #232323;
            color: #ff7a1a;
            border: 2px solid #ff7a1a;
            border-radius: 10px;
            font-weight: 700;
            padding: 12px 0;
            text-decoration: none;
            display: block;
            text-align: center;
            margin: 0 auto 18px auto;
            max-width: 420px;
            transition: background 0.18s, color 0.18s;
        }
        .order-back-btn:hover { background: #ff7a1a; color: #232323; }
        @media (max-width: 900px) {
            .order-cart-card { flex-direction: column; align-items: flex-start; padding: 16px 8px; gap: 10px; }
            .order-cart-info { width: 100%; }
            .order-summary { padding: 12px 8px; font-size: 1.05rem; }
        }
        @media (max-width: 600px) {
            .rusefi-header { padding: 0 10px; height: 48px; }
            .rusefi-logo { font-size: 1.3rem; }
            .rusefi-title { font-size: 1.5rem; margin-bottom: 18px; }
            .rusefi-main { padding: 18px 2vw 18px 2vw; }
            .order-cart-card img { width: 70px; height: 70px; }
            .order-form { padding: 12px 8px; max-width: 98vw; }
            .order-form input, .order-form label { font-size: 1rem; }
            .order-back-btn { width: 100%; margin-top: 10px; }
        }
    </style>
</head>
<body>
    <header class="rusefi-header">
        <span class="rusefi-logo">rusEFI</span>
        <nav class="rusefi-header-menu">
            <a href="index.php">Магазин</a>
            <a href="#footer-contacts" id="contacts-link">Контакты</a>
        </nav>
        <a href="cart.php" class="rusefi-cart">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span id="cart-count" class="rusefi-cart-count">0</span>
        </a>
    </header>
    <main class="rusefi-main">
        <h1 class="rusefi-title">Оформление заказа</h1>
        <div class="order-cart-list" id="order-cart"></div>
        <form action="send.php" method="post" class="order-form mt-4" id="order-form">
            <input type="hidden" name="cart_json" id="cart_json">
            <div class="mb-3">
                <label for="name" class="form-label">Имя</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Телефон</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Отправить заявку</button>
        </form>
        <a href="cart.php" class="order-back-btn">← Вернуться в корзину</a>
    </main>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            Телефон для связи: <a href="tel:+77001234567" style="color:#ff7a1a;">+7 (700) 123-45-67</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    const products = <?php echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE); ?>;
    function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
    function renderOrderCart() {
        const cart = getCart();
        let html = '';
        let total = 0;
        let hasItems = false;
        products.forEach(p => {
            const qty = cart[p.id] || 0;
            if (qty > 0) {
                hasItems = true;
                const sum = p.price * qty;
                total += sum;
                html += `<div class='order-cart-card'>
                    <img src='${p.img}' alt='${p.name}'>
                    <div class='order-cart-info'>
                        <div class='order-cart-title'>${p.name}</div>
                        <div class='order-cart-price'>${formatPrice(p.price)}</div>
                        <div class='order-cart-desc'>${p.description || ''}</div>
                        <div class='order-cart-qty'>Кол-во: ${qty}</div>
                    </div>
                </div>`;
            }
        });
        if (hasItems) {
            html += `<div class='order-summary'>Итого: <span class='order-total'>${formatPrice(total)}</span></div>`;
        } else {
            html = '<p style="text-align:center;font-size:1.2em;">Корзина пуста.</p>';
            document.getElementById('order-form').style.display = 'none';
        }
        document.getElementById('order-cart').innerHTML = html;
    }
    function prepareOrderForm() {
        const cart = getCart();
        document.getElementById('cart_json').value = JSON.stringify(cart);
    }
    function formatPrice(num) { return Number(num).toLocaleString('ru-RU') + ' KZT'; }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id];
        document.getElementById('cart-count').textContent = count;
    }
    document.addEventListener('DOMContentLoaded', function() {
        renderOrderCart();
        document.getElementById('order-form').addEventListener('submit', prepareOrderForm);
        updateCartUI();
        document.getElementById('contacts-link').onclick = function(e) {
            e.preventDefault();
            document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
        };
    });
    window.addEventListener('storage', function() { renderOrderCart(); updateCartUI(); });
    </script>
</body>
</html> 