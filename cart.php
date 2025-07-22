<?php
session_start();
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
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: cart.php');
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';
$texts = [
    'ru' => [
        'shop' => 'Магазин',
        'contacts' => 'Контакты',
        'cart' => 'Корзина',
        'order' => 'Оформить заказ',
        'empty_cart' => 'Корзина пуста.',
        'remove' => 'Удалить',
        'total' => 'Итого:',
        'phone_label' => 'Телефон для связи:',
    ],
    'kz' => [
        'shop' => 'Дүкен',
        'contacts' => 'Байланыс',
        'cart' => 'Себет',
        'order' => 'Тапсырыс беру',
        'empty_cart' => 'Себет бос.',
        'remove' => 'Жою',
        'total' => 'Жалпы:',
        'phone_label' => 'Байланыс телефоны:',
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — Корзина</title>
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
        .cart-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .cart-list-card {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            padding: 24px 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            border: 1px solid #333;
        }
        .cart-list-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            background: #232323;
        }
        .cart-list-info { flex: 1 1 auto; }
        .cart-list-title { font-weight: 700; font-size: 1.18rem; color: var(--rusefi-accent); margin-bottom: 4px; }
        .cart-list-price { color: var(--rusefi-accent); font-size: 1.1rem; font-weight: 700; }
        .cart-list-desc { color: #e0e0e0; font-size: 1rem; }
        .cart-list-qty-block {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 10px;
        }
        .cart-list-minus, .cart-list-plus {
            background: var(--rusefi-orange);
            color: #181818;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            padding: 14px 28px;
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
            min-width: 48px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-list-minus:hover, .cart-list-plus:hover {
            background: #ff9a4d;
            color: #fff;
        }
        .cart-list-qty {
            min-width: 38px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-list-remove {
            background: #232323;
            color: #ff7a1a;
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 22px;
            border: 2px solid #ff7a1a;
            margin-left: 24px;
            transition: background .2s, color .2s;
        }
        .cart-list-remove:hover {
            background: #ff7a1a;
            color: #232323;
        }
        .cart-summary {
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
        .cart-summary .cart-total { color: var(--rusefi-accent); font-weight: 700; margin-left: 8px; }
        .cart-actions { display: flex; gap: 16px; justify-content: flex-end; }
        .cart-order-btn {
            background: var(--rusefi-orange);
            color: #181818;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            padding: 14px 38px;
            font-size: 1.25rem;
            transition: background .2s;
        }
        .cart-order-btn:hover { background: #ff9a4d; color: #fff; }
        @media (max-width: 900px) {
            .cart-list-card { flex-direction: column; align-items: flex-start; padding: 16px 8px; gap: 10px; }
            .cart-list-info { width: 100%; }
            .cart-list-remove { width: 100%; margin-top: 8px; margin-left: 0; }
            .cart-summary { padding: 12px 8px; font-size: 1.05rem; }
            .cart-actions { flex-direction: column; gap: 10px; }
        }
        @media (max-width: 600px) {
            .rusefi-header { padding: 0 10px; height: 48px; }
            .rusefi-logo { font-size: 1.3rem; }
            .rusefi-title { font-size: 1.5rem; margin-bottom: 18px; }
            .rusefi-main { padding: 18px 2vw 18px 2vw; }
            .cart-list-card img { width: 70px; height: 70px; }
            .cart-list-minus, .cart-list-plus { font-size: 1.1rem; padding: 10px 0; min-width: 38px; min-height: 38px; }
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
        <h1 class="rusefi-title">
            <?= $texts[$lang]['cart'] ?>
        </h1>
        <div class="cart-list" id="cart-list"></div>
        <div id="cart-summary"></div>
        <div class="cart-actions" id="cart-actions" style="display:none;">
            <a href="order.php" class="cart-order-btn"><?= $texts[$lang]['order'] ?></a>
        </div>
    </main>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77001234567" style="color:#ff7a1a;">+7 (700) 123-45-67</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    const lang = "<?= $lang ?>";
    const texts = {
        ru: { remove: "Удалить", empty_cart: "Корзина пуста.", order: "Оформить заказ", total: "Итого:" },
        kz: { remove: "Жою", empty_cart: "Себет бос.", order: "Тапсырыс беру", total: "Жалпы:" }
    };
    const products = <?php echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE); ?>;
    function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
    function setCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id];
        document.getElementById('cart-count').textContent = count;
    }
    function formatPrice(num) { return Number(num).toLocaleString('ru-RU') + ' KZT'; }
    function renderCart() {
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
                // Мультиязычность для name/description
                let name = typeof p.name === 'object' ? (p.name[lang] || p.name['ru'] || '') : p.name;
                let desc = typeof p.description === 'object' ? (p.description[lang] || p.description['ru'] || '') : (p.description || '');
                html += `<div class='cart-list-card'>
                    <img src='${p.img}' alt='${name}'>
                    <div class='cart-list-info'>
                        <div class='cart-list-title'>${name}</div>
                        <div class='cart-list-price'>${formatPrice(p.price)}</div>
                        <div class='cart-list-desc'>${desc}</div>
                        <div class='cart-list-qty-block'>
                            <button class='cart-list-minus' data-id='${p.id}'>-</button>
                            <span class='cart-list-qty'>${qty}</span>
                            <button class='cart-list-plus' data-id='${p.id}'>+</button>
                        </div>
                    </div>
                    <button class='cart-list-remove' data-id='${p.id}'>${texts[lang].remove}</button>
                </div>`;
            }
        });
        if (hasItems) {
            document.getElementById('cart-summary').innerHTML = `<div class='cart-summary'>${texts[lang].total} <span class='cart-total'>${formatPrice(total)}</span></div>`;
            document.getElementById('cart-actions').style.display = '';
        } else {
            html = `<p style="text-align:center;font-size:1.2em;">${texts[lang].empty_cart}</p>`;
            document.getElementById('cart-summary').innerHTML = '';
            document.getElementById('cart-actions').style.display = 'none';
        }
        document.getElementById('cart-list').innerHTML = html;
        updateCartUI();
        document.querySelectorAll('.cart-list-plus').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                cart[id] = (cart[id] || 0) + 1;
                setCart(cart);
                renderCart();
            };
        });
        document.querySelectorAll('.cart-list-minus').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                cart[id] = (cart[id] || 0) - 1;
                if (cart[id] <= 0) delete cart[id];
                setCart(cart);
                renderCart();
            };
        });
        document.querySelectorAll('.cart-list-remove').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                delete cart[id];
                setCart(cart);
                renderCart();
            };
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        renderCart();
        updateCartUI();
        document.getElementById('contacts-link').onclick = function(e) {
            e.preventDefault();
            document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
        };
    });
    window.addEventListener('storage', function() { renderCart(); updateCartUI(); });
    </script>
</body>
</html> 