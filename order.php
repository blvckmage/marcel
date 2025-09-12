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
// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: order.php');
    exit;
}
$currency = $_COOKIE['currency'] ?? 'KZT';

// Функция для получения актуальных курсов валют
function getExchangeRates() {
    $cache_file = __DIR__ . '/exchange_rates_cache.json';
    $cache_time = 24 * 60 * 60; // 24 часа

    // Проверяем кэш
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $cached_data = json_decode(file_get_contents($cache_file), true);
        if ($cached_data && isset($cached_data['rates'])) {
            return $cached_data['rates'];
        }
    }

    // Запасные курсы на случай недоступности API
    $fallback_rates = [
        'KZT' => 450,  // 1 USD = 450 KZT
        'RUB' => 90,   // 1 USD = 90 RUB
        'USD' => 1     // Базовая валюта
    ];

    // Попытка получить актуальные курсы из API
    $api_url = 'https://api.exchangerate-api.com/v4/latest/USD';

    $context = stream_context_create([
        'http' => [
            'timeout' => 10, // 10 секунд таймаут
            'user_agent' => 'rusEFI Shop/1.0'
        ]
    ]);

    $api_response = @file_get_contents($api_url, false, $context);

    if ($api_response !== false) {
        $data = json_decode($api_response, true);
        if ($data && isset($data['rates'])) {
            // Извлекаем нужные нам валюты
            $rates = [
                'USD' => 1,
                'RUB' => $data['rates']['RUB'] ?? $fallback_rates['RUB'],
                'KZT' => $data['rates']['KZT'] ?? $fallback_rates['KZT']
            ];

            // Сохраняем в кэш с метаданными
            $cache_data = [
                'rates' => $rates,
                'timestamp' => time(),
                'source' => 'exchangerate-api.com'
            ];
            file_put_contents($cache_file, json_encode($cache_data));

            return $rates;
        }
    }

    // Если API недоступен, используем запасные значения
    // Сохраняем в кэш с метаданными
    $cache_data = [
        'rates' => $fallback_rates,
        'timestamp' => time(),
        'source' => 'fallback'
    ];
    file_put_contents($cache_file, json_encode($cache_data));

    return $fallback_rates;
}

// Получаем актуальные курсы
$exchange_rates = getExchangeRates();

// Функция конвертации цены (цены хранятся в USD)
function convertPrice($price_usd, $target_currency, $rates) {
    if ($target_currency === 'USD') return $price_usd;
    return round($price_usd * $rates[$target_currency], 2);
}

// Функция форматирования цены
function formatPrice($price, $currency) {
    $symbols = ['KZT' => 'KZT', 'RUB' => '₽', 'USD' => '$'];
    return number_format($price, 0, '.', ' ') . ' ' . $symbols[$currency];
}

// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: order.php');
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';
$texts = [
    'ru' => [
        'shop' => 'Магазин',
        'contacts' => 'Контакты',
        'cart' => 'Корзина',
        'order_title' => 'Оформление заказа',
        'name' => 'Имя',
        'phone' => 'Телефон',
        'send_order' => 'Отправить заявку',
        'back_to_cart' => '← Вернуться в корзину',
        'empty_cart' => 'Корзина пуста.',
        'qty' => 'Кол-во:',
        'total' => 'Итого:',
        'phone_label' => 'Телефон для связи:',
    ],
    'kz' => [
        'shop' => 'Дүкен',
        'contacts' => 'Байланыс',
        'cart' => 'Себет',
        'order_title' => 'Тапсырыс рәсімдеу',
        'name' => 'Аты',
        'phone' => 'Телефон',
        'send_order' => 'Өтінімді жіберу',
        'back_to_cart' => '← Себетке оралу',
        'empty_cart' => 'Себет бос.',
        'qty' => 'Саны:',
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
            <?= $texts[$lang]['order_title'] ?>
        </h1>
        <div class="order-cart-list" id="order-cart"></div>
        <form action="send.php" method="post" class="order-form mt-4" id="order-form">
            <input type="hidden" name="cart_json" id="cart_json">
            <input type="hidden" name="currency" value="<?= $currency ?>">
            <div class="mb-3">
                <label for="name" class="form-label">
                    <?= $texts[$lang]['name'] ?>
                </label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">
                    <?= $texts[$lang]['phone'] ?>
                </label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <?= $texts[$lang]['send_order'] ?>
            </button>
        </form>
        <a href="cart.php" class="order-back-btn">
            <?= $texts[$lang]['back_to_cart'] ?>
        </a>
    </main>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77001234567" style="color:#ff7a1a;">+7 (700) 123-45-67</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    const lang = "<?= $lang ?>";
    const currency = "<?= $currency ?>";
    const exchangeRates = <?php echo json_encode($exchange_rates); ?>;
    const texts = {
        ru: { empty_cart: "Корзина пуста.", qty: "Кол-во:", total: "Итого:" },
        kz: { empty_cart: "Себет бос.", qty: "Саны:", total: "Жалпы:" }
    };
    const products = <?php echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE); ?>;
    function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
    function renderOrderCart() {
        const cart = getCart();
        let html = '';
        let total = 0;
        let hasItems = false;
        products.forEach(p => {
            const item = cart[p.id];
            const qty = item?.quantity || 0;
            if (qty > 0) {
                hasItems = true;
                const priceUSD = item?.price || p.price;
                const price = convertPrice(priceUSD, currency);
                const sum = price * qty;
                total += sum;
                // Мультиязычность для name/description
                let name = item?.name || (typeof p.name === 'object' ? (p.name[lang] || p.name['ru'] || '') : p.name);
                let desc = typeof p.description === 'object' ? (p.description[lang] || p.description['ru'] || '') : (p.description || '');
                html += `<div class='order-cart-card'>
                    <img src='${p.img}' alt='${name}'>
                    <div class='order-cart-info'>
                        <div class='order-cart-title'>${name}</div>
                        <div class='order-cart-price'>${formatPrice(price, currency)}</div>
                        <div class='order-cart-desc'>${desc}</div>
                        <div class='order-cart-qty'>${texts[lang].qty} ${qty}</div>
                    </div>
                </div>`;
            }
        });
        if (hasItems) {
            html += `<div class='order-summary'>${texts[lang].total} <span class='order-total'>${formatPrice(total, currency)}</span></div>`;
        } else {
            html = `<p style=\"text-align:center;font-size:1.2em;\">${texts[lang].empty_cart}</p>`;
            document.getElementById('order-form').style.display = 'none';
        }
        document.getElementById('order-cart').innerHTML = html;
    }
    function prepareOrderForm() {
        const cart = getCart();
        document.getElementById('cart_json').value = JSON.stringify(cart);
    }
    function convertPrice(priceUSD, targetCurrency) {
        if (targetCurrency === 'USD') return priceUSD;
        return Math.round(priceUSD * exchangeRates[targetCurrency] * 100) / 100;
    }

    function formatPrice(price, currency) {
        const symbols = { 'KZT': 'KZT', 'RUB': '₽', 'USD': '$' };
        return new Intl.NumberFormat('ru-RU').format(price) + ' ' + symbols[currency];
    }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id]?.quantity || 0;
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
