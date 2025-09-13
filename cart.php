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

// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: cart.php');
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
        'all' => 'Все',
        'superseal_adapters' => 'Superseal адаптеры',
        'fully_crimped_pigtails' => 'Полностью обжатые пигтейлы',
        'plug_play' => 'Plug&Play',
        'universal_control_unit' => 'Универсальный блок управления',
        'shop_nav' => 'Магазин',
        'contact_nav' => 'Контакты',
        'forum_nav' => 'Форум',
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
        'all' => 'Барлығы',
        'superseal_adapters' => 'Superseal адаптерлері',
        'fully_crimped_pigtails' => 'Толық қысылған пигтейлдер',
        'plug_play' => 'Plug&Play',
        'universal_control_unit' => 'Универсалды басқару блогы',
        'shop_nav' => 'Дүкен',
        'contact_nav' => 'Байланыс',
        'forum_nav' => 'Форум',
    ],
    'en' => [
        'shop' => 'Shop',
        'contacts' => 'Contacts',
        'cart' => 'Cart',
        'order' => 'Place Order',
        'empty_cart' => 'Cart is empty.',
        'remove' => 'Remove',
        'total' => 'Total:',
        'phone_label' => 'Contact Phone:',
        'all' => 'All',
        'superseal_adapters' => 'Superseal Adapters',
        'fully_crimped_pigtails' => 'Fully crimped pigtails',
        'plug_play' => 'Plug&Play',
        'universal_control_unit' => 'Universal Control Unit',
        'shop_nav' => 'Shop',
        'contact_nav' => 'Contact',
        'forum_nav' => 'Forum',
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
            --header-bg: #ff6600;
            --main-bg: #2a2a2a;
            --text-white: #ffffff;
            --text-orange: #ff6600;
            --text-black: #000000;
            --border-color: #444444;
        }
        body {
            background: var(--main-bg);
            color: var(--text-white);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .rusefi-header {
            background: var(--header-bg);
            padding: 15px 36px;
            position: relative;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .burger-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }
        .burger-line {
            width: 24px;
            height: 2px;
            background: var(--text-black);
            transition: 0.3s;
        }
        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--header-bg);
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .mobile-menu.open {
            display: block;
        }
        .mobile-nav {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .mobile-nav-link {
            color: var(--text-black);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .mobile-nav-link:hover {
            color: var(--text-orange);
        }
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .rusefi-text {
            color: var(--text-black);
        }
        .efi-text {
            color: var(--text-orange);
            font-weight: bold;
            -webkit-text-stroke: 1px var(--text-black);
            text-stroke: 1px var(--text-black);
        }
        .main-nav {
            display: flex;
            gap: 30px;
        }
        .nav-link {
            color: var(--text-black);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding-bottom: 5px;
        }
        .nav-link:hover,
        .nav-link.active {
            color: var(--text-black);
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--text-orange);
        }
        .cart-section {
            position: relative;
        }
        .cart-link {
            color: var(--text-black);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cart-icon {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: var(--text-black);
            stroke-width: 1.5;
        }
        .cart-count {
            background: var(--text-orange);
            color: var(--text-black);
            border-radius: 50%;
            font-size: 0.8rem;
            font-weight: bold;
            min-width: 18px;
            min-height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -8px;
            right: -8px;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .currency-switcher {
            position: relative;
        }
        .currency-switcher select {
            background: var(--text-black);
            color: var(--text-white);
            border: 2px solid var(--text-black);
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
            padding-right: 32px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .currency-switcher select:hover {
            background-color: #333;
            border-color: var(--text-orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .currency-switcher select:focus {
            outline: none;
            border-color: var(--text-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        }
        .currency-switcher select option {
            background: var(--text-black);
            color: var(--text-white);
            padding: 8px;
        }
        .lang-switcher {
            position: relative;
        }
        .lang-switcher select {
            background: var(--text-black);
            color: var(--text-white);
            border: 2px solid var(--text-black);
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
            padding-right: 32px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .lang-switcher select:hover {
            background-color: #333;
            border-color: var(--text-orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .lang-switcher select:focus {
            outline: none;
            border-color: var(--text-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        }
        .lang-switcher select option {
            background: var(--text-black);
            color: var(--text-white);
            padding: 8px;
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
            background: var(--main-bg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .cart-list-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            background: var(--main-bg);
        }
        .cart-list-info { flex: 1 1 auto; }
        .cart-list-title { font-weight: bold; font-size: 1rem; color: var(--text-orange); margin-bottom: 4px; }
        .cart-list-price { color: var(--text-white); font-size: 0.9rem; font-weight: normal; }
        .cart-list-desc { color: var(--text-white); font-size: 0.85rem; }
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
            background: var(--main-bg);
            padding: 20px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 24px;
            text-align: right;
            border-top: 1px solid var(--border-color);
        }
        .cart-summary .cart-total { color: var(--text-orange); font-weight: 700; margin-left: 8px; }
        .cart-actions { display: flex; gap: 16px; justify-content: flex-end; }
        .cart-order-btn {
            background: var(--text-orange);
            color: var(--text-black);
            border-radius: 6px;
            font-weight: 600;
            border: none;
            padding: 12px 24px;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .cart-order-btn:hover { background: #e55a00; }
        /* Tablet styles */
        @media (max-width: 1024px) {
            .rusefi-main { padding: 32px 24px; }
            .cart-list-card { padding: 18px; gap: 18px; }
        }

        @media (max-width: 768px) {
            .rusefi-header { padding: 16px 20px; }
            .main-nav { display: none; }
            .burger-menu { display: flex; }
            .header-right { gap: 15px; }
            .cart-icon { width: 20px; height: 20px; }
            .currency-switcher select,
            .lang-switcher select {
                padding: 6px 12px;
                font-size: 0.8rem;
                background-image: none;
                padding-right: 12px;
            }
            .rusefi-main { padding: 32px 20px; }
            .rusefi-title { font-size: 2.2rem; margin-bottom: 32px; }
            .cart-list-card { padding: 16px; gap: 16px; }
            .cart-list-card img { width: 70px; height: 70px; }
            .cart-list-title { font-size: 0.95rem; }
            .cart-list-price { font-size: 0.85rem; }
            .cart-list-qty-block { gap: 12px; }
            .cart-list-minus, .cart-list-plus { padding: 12px 24px; min-width: 44px; min-height: 44px; }
            .cart-list-qty { font-size: 1.1rem; }
            .cart-summary { padding: 16px; font-size: 1.1rem; }
            .cart-order-btn { padding: 10px 20px; font-size: 0.95rem; }
        }

        /* Mobile styles */
        @media (max-width: 480px) {
            .rusefi-header { padding: 12px 16px; }
            .rusefi-logo { font-size: 1.5rem; }
            .main-nav { display: none; }
            .nav-link { font-size: 0.9rem; }
            .header-right { gap: 10px; }
            .cart-icon { width: 18px; height: 18px; }
            .currency-switcher select,
            .lang-switcher select {
                padding: 5px 10px;
                font-size: 0.75rem;
                background-image: none;
                padding-right: 10px;
            }
            .rusefi-main { padding: 24px 16px; }
            .rusefi-title { font-size: 1.8rem; margin-bottom: 24px; }
            .cart-list { gap: 20px; }
            .cart-list-card {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px;
                gap: 12px;
                border-radius: 8px;
            }
            .cart-list-card img { width: 80px; height: 80px; align-self: center; }
            .cart-list-info { width: 100%; text-align: center; }
            .cart-list-title { font-size: 0.9rem; margin-bottom: 6px; }
            .cart-list-price { font-size: 0.8rem; margin-bottom: 8px; }
            .cart-list-qty-block {
                justify-content: center;
                gap: 16px;
                margin-top: 8px;
            }
            .cart-list-minus, .cart-list-plus {
                padding: 10px 20px;
                min-width: 40px;
                min-height: 40px;
                font-size: 1rem;
            }
            .cart-list-qty { font-size: 1rem; min-width: 32px; }
            .cart-list-remove {
                width: 100%;
                margin-top: 12px;
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            .cart-summary {
                padding: 16px;
                font-size: 1rem;
                text-align: center;
                margin-bottom: 20px;
            }
            .cart-actions {
                flex-direction: column;
                gap: 12px;
                width: 100%;
            }
            .cart-order-btn {
                width: 100%;
                padding: 12px 20px;
                font-size: 1rem;
            }
        }

        /* Small mobile styles */
        @media (max-width: 360px) {
            .rusefi-header { padding: 10px 12px; }
            .rusefi-logo { font-size: 1.3rem; }
            .main-nav { gap: 10px; }
            .nav-link { font-size: 0.8rem; }
            .header-right { gap: 8px; }
            .currency-switcher select,
            .lang-switcher select {
                padding: 5px 10px;
                font-size: 0.75rem;
                background-image: none;
                padding-right: 10px;
            }
            .rusefi-main { padding: 20px 12px; }
            .rusefi-title { font-size: 1.6rem; margin-bottom: 20px; }
            .cart-list-card { padding: 12px; }
            .cart-list-card img { width: 70px; height: 70px; }
            .cart-list-title { font-size: 0.85rem; }
            .cart-list-price { font-size: 0.75rem; }
            .cart-list-qty-block { gap: 12px; }
            .cart-list-minus, .cart-list-plus {
                padding: 8px 16px;
                min-width: 36px;
                min-height: 36px;
                font-size: 0.9rem;
            }
            .cart-list-qty { font-size: 0.9rem; min-width: 28px; }
            .cart-list-remove {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
            .cart-summary { padding: 12px; font-size: 0.95rem; }
            .cart-order-btn { padding: 10px 16px; font-size: 0.95rem; }
        }

        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .cart-list-minus, .cart-list-plus { min-width: 48px; min-height: 48px; }
            .cart-list-remove { min-height: 48px; }
            .cart-order-btn { min-height: 48px; }
        }

        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .rusefi-header { padding: 8px 16px; }
            .rusefi-main { padding: 16px; }
            .cart-list-card {
                flex-direction: row;
                align-items: center;
                padding: 12px;
            }
            .cart-list-card img { width: 60px; height: 60px; align-self: auto; }
            .cart-list-info { width: auto; flex: 1; text-align: left; }
            .cart-list-qty-block { margin-top: 0; }
            .cart-list-remove { width: auto; margin-top: 0; margin-left: 12px; }
        }
    </style>
</head>
<body>
    <header class="rusefi-header">
        <div class="header-content">
            <div class="logo">
                <span class="rusefi-text">rus</span><span class="efi-text">EFI</span>
            </div>
        <nav class="main-nav">
            <a href="index.php" class="nav-link"> <?= $texts[$lang]['shop_nav'] ?></a>
            <a href="#" class="nav-link"> <?= $texts[$lang]['contact_nav'] ?></a>
            <a href="forum.php" class="nav-link"> <?= $texts[$lang]['forum_nav'] ?></a>
        </nav>
            <div class="header-right">
                <div class="currency-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <select name="currency" onchange="this.form.submit()">
                            <option value="KZT" <?= $currency == 'KZT' ? 'selected' : '' ?>>KZT</option>
                            <option value="RUB" <?= $currency == 'RUB' ? 'selected' : '' ?>>RUB</option>
                            <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </form>
                </div>
                <div class="lang-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <select name="lang" onchange="this.form.submit()">
                            <option value="ru" <?= $lang == 'ru' ? 'selected' : '' ?>>RU</option>
                            <option value="kz" <?= $lang == 'kz' ? 'selected' : '' ?>>KZ</option>
                            <option value="en" <?= $lang == 'en' ? 'selected' : '' ?>>EN</option>
                        </select>
                    </form>
                </div>
                <div class="cart-section">
                    <a href="cart.php" class="cart-link">
                        <svg viewBox="0 0 24 24" class="cart-icon">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span id="cart-count" class="cart-count">0</span>
                    </a>
                </div>
                <div class="burger-menu" onclick="toggleMobileMenu()">
                    <div class="burger-line"></div>
                    <div class="burger-line"></div>
                    <div class="burger-line"></div>
                </div>
            </div>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <nav class="mobile-nav">
                <a href="index.php" class="mobile-nav-link"> <?= $texts[$lang]['shop_nav'] ?></a>
                <a href="#" class="mobile-nav-link" onclick="document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'}); toggleMobileMenu();"> <?= $texts[$lang]['contact_nav'] ?></a>
                <a href="forum.php" class="mobile-nav-link"> <?= $texts[$lang]['forum_nav'] ?></a>
            </nav>
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
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77054111122" style="color:#ff7a1a;">+7 (705) 411-11-22</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    const lang = "<?= $lang ?>";
    const currency = "<?= $currency ?>";
    const exchangeRates = <?php echo json_encode($exchange_rates); ?>;
    const texts = {
        ru: { remove: "Удалить", empty_cart: "Корзина пуста.", order: "Оформить заказ", total: "Итого:" },
        kz: { remove: "Жою", empty_cart: "Себет бос.", order: "Тапсырыс беру", total: "Жалпы:" },
        en: { remove: "Remove", empty_cart: "Cart is empty.", order: "Place Order", total: "Total:" }
    };
    const products = <?php echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE); ?>;

    function getCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        // Convert old format
        for (let id in cart) {
            if (typeof cart[id] === 'number') {
                cart[id] = {quantity: cart[id], price: 0, name: ''};
            }
        }
        return cart;
    }
    function setCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id].quantity;
        document.getElementById('cart-count').textContent = count;
    }

    function convertPrice(priceUSD, targetCurrency) {
        if (targetCurrency === 'USD') return priceUSD;
        return Math.round(priceUSD * exchangeRates[targetCurrency] * 100) / 100;
    }

    function formatPrice(price, currency) {
        const symbols = { 'KZT': 'KZT', 'RUB': '₽', 'USD': '$' };
        return new Intl.NumberFormat('ru-RU').format(price) + ' ' + symbols[currency];
    }
    function renderCart() {
        const cart = getCart();
        let html = '';
        let total = 0;
        let hasItems = false;
        products.forEach(p => {
            const item = cart[p.id];
            const qty = item?.quantity || 0;
            if (qty > 0) {
                hasItems = true;
                const itemPrice = item?.price || p.price;
                const convertedPrice = convertPrice(itemPrice, currency);
                const sum = convertedPrice * qty;
                total += sum;
                // Мультиязычность для name/description
                let name = item?.name || (typeof p.name === 'object' ? (p.name[lang] || p.name['ru'] || '') : p.name);
                let desc = typeof p.description === 'object' ? (p.description[lang] || p.description['ru'] || '') : (p.description || '');
                // Get first image from gallery
                const firstImage = Array.isArray(p.images) && p.images.length > 0 ? p.images[0] : (p.img || '');
                html += `<div class='cart-list-card'>
                    <img src='${firstImage}' alt='${name}'>
                    <div class='cart-list-info'>
                        <div class='cart-list-title'>${name}</div>
                        <div class='cart-list-price'>${formatPrice(convertedPrice, currency)}</div>
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
            document.getElementById('cart-summary').innerHTML = `<div class='cart-summary'>${texts[lang].total} <span class='cart-total'>${formatPrice(total, currency)}</span></div>`;
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
                const p = products.find(prod => prod.id == id);
                cart[id] = {quantity: (cart[id]?.quantity || 0) + 1, price: cart[id]?.price || p.price, name: cart[id]?.name || p.name[lang]};
                setCart(cart);
                renderCart();
            };
        });
        document.querySelectorAll('.cart-list-minus').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                cart[id].quantity = (cart[id]?.quantity || 0) - 1;
                if (cart[id].quantity <= 0) delete cart[id];
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
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderCart();
        updateCartUI();
        var contacts = document.querySelector('.main-nav a[href="#"]');
        if (contacts) {
            contacts.onclick = function(e) {
                e.preventDefault();
                document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
            };
        }
    });
    window.addEventListener('storage', function() { renderCart(); updateCartUI(); });
    </script>
</body>
</html>
