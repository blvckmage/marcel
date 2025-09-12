<?php
session_start();
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: product.php?id=' . ($_GET['id'] ?? ''));
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';

// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: product.php?id=' . ($_GET['id'] ?? ''));
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
        'add_to_cart' => 'В корзину',
        'phone_label' => 'Телефон для связи:',
        'back_to_shop' => 'Вернуться в магазин',
        'all' => 'Все',
        'hellen_boards' => 'Hellen платы',
        'uaefi_boards' => 'uaEFI платы',
        'shop_nav' => 'Магазин',
        'contact_nav' => 'Контакты',
        'forum_nav' => 'Форум',
    ],
    'kz' => [
        'shop' => 'Дүкен',
        'contacts' => 'Байланыс',
        'cart' => 'Себет',
        'add_to_cart' => 'Себетке',
        'phone_label' => 'Байланыс телефоны:',
        'back_to_shop' => 'Дүкенге оралу',
        'all' => 'Барлығы',
        'hellen_boards' => 'Hellen тақталары',
        'uaefi_boards' => 'uaEFI тақталары',
        'shop_nav' => 'Дүкен',
        'contact_nav' => 'Байланыс',
        'forum_nav' => 'Форум',
    ],
    'en' => [
        'shop' => 'Shop',
        'contacts' => 'Contacts',
        'cart' => 'Cart',
        'add_to_cart' => 'Add to Cart',
        'phone_label' => 'Contact Phone:',
        'back_to_shop' => 'Back to Shop',
        'all' => 'All',
        'hellen_boards' => 'Hellen Boards',
        'uaefi_boards' => 'uaEFI Boards',
        'shop_nav' => 'Shop',
        'contact_nav' => 'Contact',
        'forum_nav' => 'Forum',
    ]
];
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

// Получаем продукт по ID
$product_id = $_GET['id'] ?? '';
$product = isset($products[$product_id]) ? $products[$product_id] : null;
if (!$product) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — <?= htmlspecialchars(is_array($product['name']) ? ($product['name'][$lang] ?? $product['name']['ru'] ?? '') : $product['name']) ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 36px;
        }
        .rusefi-title {
            text-align: center;
            font-size: 2.4rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: var(--text-white);
        }
        .product-detail {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        .product-gallery {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .gallery-main {
            position: relative;
            display: inline-block;
        }
        .product-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            object-fit: contain;
            background: var(--main-bg);
            border-radius: 12px;
            transition: opacity 0.3s ease;
        }
        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        .gallery-nav:hover {
            background: rgba(255, 102, 0, 0.8);
        }
        .gallery-prev {
            left: 10px;
        }
        .gallery-next {
            right: 10px;
        }
        .gallery-thumbnails {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease, opacity 0.3s ease;
            opacity: 0.7;
        }
        .thumbnail:hover {
            opacity: 1;
        }
        .thumbnail.active {
            border-color: var(--text-orange);
            opacity: 1;
        }
        .product-info {
            text-align: center;
            max-width: 600px;
        }
        .product-name {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-orange);
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 1.5rem;
            color: var(--text-white);
            margin-bottom: 20px;
        }
        .product-description {
            font-size: 1rem;
            color: var(--text-white);
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .product-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .quantity-btn {
            background: var(--text-orange);
            color: var(--text-black);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .quantity-btn:hover {
            background: #e55a00;
        }
        .quantity-display {
            min-width: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--text-white);
        }
        .add-to-cart-btn {
            background: var(--text-orange);
            color: var(--text-black);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 24px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-to-cart-btn:hover {
            background: #e55a00;
        }
        .back-link {
            color: var(--text-orange);
            text-decoration: none;
            font-weight: 500;
            margin-top: 40px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* Tablet styles */
        @media (max-width: 1024px) {
            .rusefi-main { padding: 32px 24px; }
            .product-image { max-width: 350px; }
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
            }
            .rusefi-main { padding: 32px 20px; }
            .product-detail { gap: 25px; }
            .product-gallery { gap: 12px; }
            .product-image { max-width: 320px; }
            .gallery-nav { width: 35px; height: 35px; font-size: 16px; }
            .gallery-prev { left: 8px; }
            .gallery-next { right: 8px; }
            .gallery-thumbnails { gap: 8px; }
            .thumbnail { width: 50px; height: 50px; }
            .product-name { font-size: 1.8rem; }
            .product-price { font-size: 1.3rem; }
            .product-description { font-size: 0.95rem; }
            .product-options { margin-top: 20px; }
            .product-options h3 { font-size: 1.1rem; }
            .product-options h4 { font-size: 1rem; }
            .quantity-controls { gap: 10px; }
            .quantity-btn { width: 36px; height: 36px; }
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
            }
            .rusefi-main { padding: 24px 16px; }
            .product-detail { gap: 20px; }
            .product-gallery { gap: 10px; }
            .product-image { max-width: 280px; }
            .gallery-nav { width: 30px; height: 30px; font-size: 14px; }
            .gallery-prev { left: 5px; }
            .gallery-next { right: 5px; }
            .gallery-thumbnails { gap: 6px; flex-wrap: wrap; justify-content: center; }
            .thumbnail { width: 45px; height: 45px; }
            .product-info { max-width: 100%; text-align: center; }
            .product-name { font-size: 1.5rem; line-height: 1.2; }
            .product-price { font-size: 1.1rem; }
            .product-description { font-size: 0.9rem; line-height: 1.5; }
            .product-options { margin-top: 18px; }
            .product-options h3 { font-size: 1rem; }
            .product-options h4 { font-size: 0.95rem; }
            .product-options label { font-size: 0.9rem; }
            .quantity-controls { gap: 8px; }
            .quantity-btn { width: 32px; height: 32px; font-size: 1.1rem; }
            .add-to-cart-btn { font-size: 1rem; padding: 10px 20px; }
            .back-link { margin-top: 30px; font-size: 0.95rem; }
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
            }
            .rusefi-main { padding: 20px 12px; }
            .product-detail { gap: 18px; }
            .product-gallery { gap: 8px; }
            .product-image { max-width: 260px; }
            .gallery-nav { width: 28px; height: 28px; font-size: 12px; }
            .gallery-prev { left: 3px; }
            .gallery-next { right: 3px; }
            .gallery-thumbnails { gap: 5px; }
            .thumbnail { width: 40px; height: 40px; }
            .product-name { font-size: 1.3rem; }
            .product-price { font-size: 1rem; }
            .product-description { font-size: 0.85rem; }
            .product-options { margin-top: 15px; }
            .product-options h3 { font-size: 0.95rem; }
            .product-options h4 { font-size: 0.9rem; }
            .product-options label { font-size: 0.85rem; }
            .quantity-controls { gap: 6px; }
            .quantity-btn { width: 30px; height: 30px; font-size: 1rem; }
            .add-to-cart-btn { font-size: 0.95rem; padding: 8px 16px; }
            .back-link { margin-top: 25px; font-size: 0.9rem; }
        }

        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .gallery-nav { opacity: 0.8; }
            .gallery-nav:hover { opacity: 1; }
            .thumbnail { opacity: 0.8; }
            .thumbnail:hover { opacity: 1; }
            .quantity-btn { min-width: 44px; min-height: 44px; }
            .add-to-cart-btn { min-height: 44px; }
        }

        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .rusefi-header { padding: 8px 16px; }
            .rusefi-main { padding: 16px; }
            .product-detail { flex-direction: row; align-items: flex-start; gap: 20px; }
            .product-gallery { flex: 1; max-width: 300px; }
            .product-info { flex: 1; max-width: none; }
            .product-image { max-width: 100%; }
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
                <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><?= $texts[$lang]['shop_nav'] ?></a>
                <a href="#" class="nav-link"><?= $texts[$lang]['contact_nav'] ?></a>
                <a href="forum.php" class="nav-link"><?= $texts[$lang]['forum_nav'] ?></a>
            </nav>
            <div class="header-right">
                <div class="currency-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($product_id) ?>">
                        <select name="currency" onchange="this.form.submit()">
                            <option value="KZT" <?= $currency == 'KZT' ? 'selected' : '' ?>>KZT</option>
                            <option value="RUB" <?= $currency == 'RUB' ? 'selected' : '' ?>>RUB</option>
                            <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </form>
                </div>
                <div class="lang-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($product_id) ?>">
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
        <div class="product-detail">
            <div class="product-gallery">
                <div class="gallery-main">
                    <img id="main-image" src="<?= htmlspecialchars(is_array($product['images']) && count($product['images']) > 0 ? $product['images'][0] : ($product['img'] ?? '')) ?>" alt="<?= htmlspecialchars(is_array($product['name']) ? ($product['name'][$lang] ?? $product['name']['ru'] ?? '') : $product['name']) ?>" class="product-image">
                    <?php if (is_array($product['images']) && count($product['images']) > 1): ?>
                    <button class="gallery-nav gallery-prev" onclick="changeImage(-1)">&#10094;</button>
                    <button class="gallery-nav gallery-next" onclick="changeImage(1)">&#10095;</button>
                    <?php endif; ?>
                </div>
                <?php if (is_array($product['images']) && count($product['images']) > 1): ?>
                <div class="gallery-thumbnails">
                    <?php foreach ($product['images'] as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="Thumbnail <?= $index + 1 ?>" class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="setImage(<?= $index ?>)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
                <div class="product-info">
                <h1 class="product-name"><?= htmlspecialchars(is_array($product['name']) ? ($product['name'][$lang] ?? $product['name']['ru'] ?? '') : $product['name']) ?></h1>
                <?php
                $price = formatPrice(convertPrice($product['price'], $currency, $exchange_rates), $currency);
                if ($product_id === 'ultra-affordable-efi-121' || $product_id === 'uaefi-ultra-affordable') {
                    if ($lang === 'ru') $price = 'от ' . $price;
                    elseif ($lang === 'kz') $price = $price . ' бастап';
                    else $price = 'from ' . $price;
                }
                ?>
                <div class="product-price"><?= $price ?></div>
                <div class="product-description"><?= nl2br(htmlspecialchars(is_array($product['description']) ? ($product['description'][$lang] ?? $product['description']['ru'] ?? '') : $product['description'])) ?></div>
                <?php if ($product_id === 'ultra-affordable-efi-121'): ?>
                <div class="product-options">
                    <h3><?php echo $lang === 'ru' ? 'Опции:' : ($lang === 'kz' ? 'Опциялар:' : 'Options:'); ?></h3>
                    <label><input type="checkbox" id="crimp-kit"> <?php echo $lang === 'ru' ? 'С обжимным комплектом' : ($lang === 'kz' ? 'Обжимдік комплектпен' : 'With crimp kit'); ?></label><br>
                    <label><input type="checkbox" id="enclosure"> <?php echo $lang === 'ru' ? 'С металлическим корпусом и USB-B' : ($lang === 'kz' ? 'Металл корпус және USB-B бар' : 'With metal enclosure and USB-B'); ?></label>
                </div>
                <?php elseif ($product_id === 'uaefi-ultra-affordable'): ?>
                <div class="product-options">
                    <h3><?php echo $lang === 'ru' ? 'Опции:' : ($lang === 'kz' ? 'Опциялар:' : 'Options:'); ?></h3>
                    <h4><?php echo $lang === 'ru' ? 'Память:' : ($lang === 'kz' ? 'Жады:' : 'Memory:'); ?></h4>
                    <label><input type="radio" name="memory" value="normal" checked> <?php echo $lang === 'ru' ? 'Обычная память' : ($lang === 'kz' ? 'Қалыпты жады' : 'Normal memory'); ?></label><br>
                    <label><input type="radio" name="memory" value="pro"> <?php echo $lang === 'ru' ? 'Pro память' : ($lang === 'kz' ? 'Pro жады' : 'Pro memory'); ?></label><br>
                    <h4><?php echo $lang === 'ru' ? 'Корпус:' : ($lang === 'kz' ? 'Корпус:' : 'Enclosure:'); ?></h4>
                    <label><input type="radio" name="enclosure" value="none" checked> <?php echo $lang === 'ru' ? 'Без корпуса' : ($lang === 'kz' ? 'Корпуссыз' : 'Without enclosure'); ?></label><br>
                    <label><input type="radio" name="enclosure" value="orange"> <?php echo $lang === 'ru' ? 'С оранжевым корпусом' : ($lang === 'kz' ? 'Қызғылт сары корпуспен' : 'With orange enclosure'); ?></label><br>
                    <h4><?php echo $lang === 'ru' ? 'Molex:' : ($lang === 'kz' ? 'Molex:' : 'Molex:'); ?></h4>
                    <label><input type="radio" name="molex" value="none" checked> <?php echo $lang === 'ru' ? 'Без molex на плате - паяйте свои провода' : ($lang === 'kz' ? 'Платада molex жоқ - өз сымдарыңызды дән қойыңыз' : 'No molex on the board - solder your own wires'); ?></label><br>
                    <label><input type="radio" name="molex" value="soldered"> <?php echo $lang === 'ru' ? 'Molex припаяны - вам понадобятся соединительные заглушки' : ($lang === 'kz' ? 'Molex дән қойылған - сізге mating plugs қажет' : 'Molex soldered - you would need mating plugs'); ?></label><br>
                    <h4><?php echo $lang === 'ru' ? 'Жгут проводов:' : ($lang === 'kz' ? 'Harness:' : 'Harness:'); ?></h4>
                    <label><input type="checkbox" id="wbo-cable"> <?php echo $lang === 'ru' ? 'Соединительный кабель WBO' : ($lang === 'kz' ? 'Mating WBO кабель' : 'Mating WBO cable'); ?></label><br>
                    <label><input type="checkbox" id="pigtails"> <?php echo $lang === 'ru' ? 'Боковые косички для жгута проводов' : ($lang === 'kz' ? 'Harness side pigtails' : 'Harness side pigtails'); ?></label><br>
                    <label><input type="checkbox" id="crimp-kit-uaefi"> <?php echo $lang === 'ru' ? 'Комплект для обжима соединительных штекеров и клемм' : ($lang === 'kz' ? 'Mating plugs және terminals crimp kit' : 'Mating plugs and terminals crimp kit'); ?></label><br>
                </div>
                <?php endif; ?>
                <div class="product-controls" id="product-controls">
                    <!-- Controls will be rendered by JS -->
                </div>
                <a href="index.php" class="back-link"><?= $texts[$lang]['back_to_shop'] ?></a>
            </div>
        </div>
    </main>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77054111122" style="color:#e0e0e0;">+7 (705) 411-11-22</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#e0e0e0;">rusefi.com</a>
    </footer>
    <script>
    const lang = "<?= $lang ?>";
    const currency = "<?= $currency ?>";
    const exchangeRates = <?php echo json_encode($exchange_rates); ?>;
    const texts = {
        ru: { add_to_cart: "В корзину" },
        kz: { add_to_cart: "Себетке" },
        en: { add_to_cart: "Add to Cart" }
    };
    const product = <?php echo json_encode($product, JSON_UNESCAPED_UNICODE); ?>;

    // Gallery functionality
    let currentImageIndex = 0;
    const images = Array.isArray(product.images) ? product.images : [product.img || ''];

    function changeImage(direction) {
        currentImageIndex = (currentImageIndex + direction + images.length) % images.length;
        updateGallery();
    }

    function setImage(index) {
        currentImageIndex = index;
        updateGallery();
    }

    function updateGallery() {
        const mainImage = document.getElementById('main-image');
        const thumbnails = document.querySelectorAll('.thumbnail');

        if (mainImage) {
            mainImage.src = images[currentImageIndex];
        }

        thumbnails.forEach((thumb, index) => {
            if (index === currentImageIndex) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    function convertPrice(priceUSD, targetCurrency) {
        if (targetCurrency === 'USD') return priceUSD;
        return Math.round(priceUSD * exchangeRates[targetCurrency] * 100) / 100;
    }

    function formatPrice(price, currency) {
        const symbols = { 'KZT': 'KZT', 'RUB': '₽', 'USD': '$' };
        return new Intl.NumberFormat('ru-RU').format(price) + ' ' + symbols[currency];
    }

    function getCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        // Convert old format
        for (let id in cart) {
            if (typeof cart[id] === 'number') {
                cart[id] = {quantity: cart[id], price: product.price, name: product.name[lang]};
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

    function renderProductControls() {
        const cart = getCart();
        const quantity = cart[product.id]?.quantity || 0;
        let html = '';
        if (quantity > 0) {
            html = `
                <div class='quantity-controls'>
                    <button class='quantity-btn' data-action='decrease'>-</button>
                    <span class='quantity-display'>${quantity}</span>
                    <button class='quantity-btn' data-action='increase'>+</button>
                </div>
            `;
        } else {
            html = `<button class='add-to-cart-btn' id='add-to-cart-btn'>${texts[lang].add_to_cart}</button>`;
        }
        document.getElementById('product-controls').innerHTML = html;

        // Add event listeners
        const addBtn = document.getElementById('add-to-cart-btn');
        if (addBtn) {
            addBtn.onclick = function() {
                const cart = getCart();
                cart[product.id] = {quantity: (cart[product.id]?.quantity || 0) + 1, price: product.price, name: product.name[lang]};
                setCart(cart);
                updateCartUI();
                renderProductControls();
            };
        }

        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.onclick = function() {
                const action = this.getAttribute('data-action');
                const cart = getCart();
                if (action === 'increase') {
                    cart[product.id] = {quantity: (cart[product.id]?.quantity || 0) + 1, price: product.price, name: product.name[lang]};
                } else if (action === 'decrease') {
                    cart[product.id].quantity = (cart[product.id]?.quantity || 0) - 1;
                    if (cart[product.id].quantity <= 0) {
                        delete cart[product.id];
                    }
                }
                setCart(cart);
                updateCartUI();
                renderProductControls();
            };
        });
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderProductControls();
        updateCartUI();

        if (product.id === 'ultra-affordable-efi-121') {
            const originalName = product.name[lang];
            const basePrice = product.price;

            function updatePrice() {
                const crimp = document.getElementById('crimp-kit').checked;
                const enclosure = document.getElementById('enclosure').checked;
                let total = basePrice;
                if (crimp) total += 45;
                if (enclosure) total += 75;
                product.price = total;
                let optionsText = '';
                if (crimp) optionsText += lang === 'ru' ? ' с обжимным комплектом' : lang === 'kz' ? ' обжимдік комплектпен' : ' with crimp kit';
                if (enclosure) optionsText += lang === 'ru' ? ' с металлическим корпусом и USB-B' : lang === 'kz' ? ' металл корпус және USB-B бар' : ' with metal enclosure and USB-B';
                product.name[lang] = originalName + optionsText;
                document.querySelector('.product-name').textContent = product.name[lang];
                const convertedPrice = convertPrice(total, currency);
                const formattedPrice = formatPrice(convertedPrice, currency);
                document.querySelector('.product-price').textContent = formattedPrice;
            }

            document.getElementById('crimp-kit').addEventListener('change', updatePrice);
            document.getElementById('enclosure').addEventListener('change', updatePrice);
            updatePrice();
        } else if (product.id === 'uaefi-ultra-affordable') {
            const originalName = product.name[lang];
            const basePrice = product.price;

            function updatePrice() {
                const memory = document.querySelector('input[name="memory"]:checked').value;
                const enclosure = document.querySelector('input[name="enclosure"]:checked').value;
                const molex = document.querySelector('input[name="molex"]:checked').value;
                const wboCable = document.getElementById('wbo-cable').checked;
                const pigtails = document.getElementById('pigtails').checked;
                const crimpKit = document.getElementById('crimp-kit-uaefi').checked;
                let total = basePrice;
                if (memory === 'pro') total += 20;
                if (enclosure === 'orange') total += 30;
                if (molex === 'soldered') total += 10;
                if (wboCable) total += 15;
                if (pigtails) total += 25;
                if (crimpKit) total += 10;
                product.price = total;
                let optionsText = '';
                if (memory === 'pro') optionsText += lang === 'ru' ? ' Pro память' : lang === 'kz' ? ' Pro жады' : ' Pro memory';
                if (enclosure === 'orange') optionsText += lang === 'ru' ? ' с оранжевым корпусом' : lang === 'kz' ? ' қызғылт сары корпуспен' : ' with orange enclosure';
                if (molex === 'soldered') optionsText += lang === 'ru' ? ' molex припаяны' : lang === 'kz' ? ' molex дән қойылған' : ' molex soldered';
                if (wboCable) optionsText += lang === 'ru' ? ' с соединительным кабелем WBO' : lang === 'kz' ? ' Mating WBO кабельмен' : ' with Mating WBO cable';
                if (pigtails) optionsText += lang === 'ru' ? ' с боковыми косичками для жгута проводов' : lang === 'kz' ? ' Harness side pigtails бар' : ' with Harness side pigtails';
                if (crimpKit) optionsText += lang === 'ru' ? ' с комплектом для обжима соединительных штекеров и клемм' : lang === 'kz' ? ' Mating plugs және terminals crimp kit бар' : ' with Mating plugs and terminals crimp kit';
                product.name[lang] = originalName + optionsText;
                document.querySelector('.product-name').textContent = product.name[lang];
                const convertedPrice = convertPrice(total, currency);
                const formattedPrice = formatPrice(convertedPrice, currency);
                document.querySelector('.product-price').textContent = formattedPrice;
            }

            document.querySelectorAll('input[name="memory"]').forEach(radio => radio.addEventListener('change', updatePrice));
            document.querySelectorAll('input[name="enclosure"]').forEach(radio => radio.addEventListener('change', updatePrice));
            document.querySelectorAll('input[name="molex"]').forEach(radio => radio.addEventListener('change', updatePrice));
            document.getElementById('wbo-cable').addEventListener('change', updatePrice);
            document.getElementById('pigtails').addEventListener('change', updatePrice);
            document.getElementById('crimp-kit-uaefi').addEventListener('change', updatePrice);
            updatePrice();
        }

        // Обработчик для контактов
        var contacts = document.querySelector('.main-nav a[href="#"]');
        if (contacts) {
            contacts.onclick = function(e) {
                e.preventDefault();
                document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
            };
        }
    });

    window.addEventListener('storage', function() { updateCartUI(); });
    </script>
</body>
</html>
