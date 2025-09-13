<?php
session_start();
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: forum.php');
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';

// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: forum.php');
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
        'forum_title' => 'Форум rusEFI',
        'create_topic' => 'Создать тему',
        'login' => 'Войти',
        'register' => 'Регистрация',
        'logout' => 'Выйти',
        'welcome' => 'Добро пожаловать',
        'topics' => 'Темы',
        'posts' => 'Сообщений',
        'last_post' => 'Последнее сообщение',
        'author' => 'Автор',
        'replies' => 'Ответов',
        'views' => 'Просмотров',
        'no_topics' => 'Темы не найдены',
        'search' => 'Поиск',
        'categories' => 'Категории',
        'general_discussion' => 'Общее обсуждение',
        'technical_support' => 'Техническая поддержка',
        'showcase' => 'Показать',
        'off_topic' => 'Офф-топик',
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
        'forum_title' => 'rusEFI Форумы',
        'create_topic' => 'Тақырып жасау',
        'login' => 'Кіру',
        'register' => 'Тіркелу',
        'logout' => 'Шығу',
        'welcome' => 'Қош келдіңіз',
        'topics' => 'Тақырыптар',
        'posts' => 'Хабарламалар',
        'last_post' => 'Соңғы хабарлама',
        'author' => 'Автор',
        'replies' => 'Жауаптар',
        'views' => 'Көрулер',
        'no_topics' => 'Тақырыптар табылмады',
        'search' => 'Іздеу',
        'categories' => 'Санаттар',
        'general_discussion' => 'Жалпы талқылау',
        'technical_support' => 'Техникалық қолдау',
        'showcase' => 'Көрсету',
        'off_topic' => 'Офф-топик',
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
        'forum_title' => 'rusEFI Forum',
        'create_topic' => 'Create Topic',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'welcome' => 'Welcome',
        'topics' => 'Topics',
        'posts' => 'Posts',
        'last_post' => 'Last Post',
        'author' => 'Author',
        'replies' => 'Replies',
        'views' => 'Views',
        'no_topics' => 'No topics found',
        'search' => 'Search',
        'categories' => 'Categories',
        'general_discussion' => 'General Discussion',
        'technical_support' => 'Technical Support',
        'showcase' => 'Showcase',
        'off_topic' => 'Off Topic',
    ]
];

// Загрузка тем форума
$topics_file = __DIR__ . '/forum_topics.json';
$topics = [];
if (file_exists($topics_file)) {
    $topics = json_decode(file_get_contents($topics_file), true) ?: [];
}

// Загрузка пользователей
$users_file = __DIR__ . '/forum_users.json';
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

// Проверка авторизации
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = $users[$_SESSION['user_id']] ?? null;
    // Проверка на бан
    if ($user && isset($user['banned']) && $user['banned']) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Категории форума
$categories = [
    'general' => $texts[$lang]['general_discussion'],
    'technical' => $texts[$lang]['technical_support'],
    'offtopic' => $texts[$lang]['off_topic']
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — <?= $texts[$lang]['forum_title'] ?></title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <script>
    window.toggleMobileMenu = function() {
        console.log('toggleMobileMenu called');
        const menu = document.getElementById('mobile-menu');
        console.log('menu element:', menu);
        menu.classList.toggle('open');
        console.log('menu classList:', menu.classList);
    }
    </script>
    <style>
        :root {
            --header-bg: #ff6600;
            --main-bg: #f8f9fa;
            --text-white: #212529;
            --text-orange: #ff6600;
            --text-black: #000000;
            --border-color: #dee2e6;
            --card-bg: #ffffff;
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
        .logo-link {
            text-decoration: none;
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
            color: #ffffff;
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
            color: #ffffff;
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
        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .forum-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .btn {
            background: var(--text-orange);
            color: var(--text-black);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #e55a00;
        }
        .btn-secondary {
            background: var(--card-bg);
            color: var(--text-white);
            border: 2px solid var(--text-orange);
        }
        .btn-secondary:hover {
            background: var(--text-orange);
            color: var(--text-black);
        }
        .forum-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .forum-table th,
        .forum-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .forum-table th {
            background: var(--header-bg);
            color: var(--text-black);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .forum-table td {
            color: var(--text-white);
        }
        .topic-title {
            font-weight: 600;
            color: var(--text-orange);
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
        }
        .topic-title:hover {
            text-decoration: underline;
        }
        .topic-meta {
            font-size: 0.85rem;
            color: #ccc;
        }
        .topic-stats {
            text-align: center;
            font-weight: bold;
        }
        .last-post {
            font-size: 0.85rem;
            color: #ccc;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--text-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-black);
            font-weight: bold;
            font-size: 0.9rem;
        }
        .user-name {
            font-weight: 500;
            color: var(--text-orange);
        }
        .no-topics {
            text-align: center;
            padding: 50px;
            color: #ccc;
            font-size: 1.1rem;
        }
        .forum-filters {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }
        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            background: var(--card-bg);
            color: var(--text-white);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--text-orange);
        }
        .categories {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .category-btn {
            background: var(--card-bg);
            color: var(--text-white);
            border: 2px solid transparent;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .category-btn:hover,
        .category-btn.active {
            border-color: var(--text-orange);
            background: var(--text-orange);
            color: var(--text-black);
        }
        /* Tablet styles */
        @media (max-width: 1024px) {
            .rusefi-main { padding: 32px 24px; }
            .forum-table th,
            .forum-table td { padding: 12px; }
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
            .forum-header { flex-direction: column; align-items: stretch; }
            .forum-actions { justify-content: center; }
            .forum-table { font-size: 0.9rem; }
            .forum-table th,
            .forum-table td { padding: 10px; }
            .categories { justify-content: center; }
        }
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
            .forum-table { font-size: 0.8rem; }
            .forum-table th,
            .forum-table td { padding: 8px; }
            .forum-table th:nth-child(3),
            .forum-table th:nth-child(4),
            .forum-table td:nth-child(3),
            .forum-table td:nth-child(4) { display: none; }
            .forum-actions { flex-direction: column; gap: 10px; }
            .categories { flex-direction: column; align-items: center; }
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
            .forum-table { font-size: 0.75rem; }
            .forum-table th,
            .forum-table td { padding: 6px; }
            .forum-table th:nth-child(3),
            .forum-table th:nth-child(4),
            .forum-table td:nth-child(3),
            .forum-table td:nth-child(4) { display: none; }
            .forum-actions { flex-direction: column; gap: 8px; }
            .categories { flex-direction: column; align-items: center; gap: 10px; }
        }
    </style>
</head>
<body>
    <header class="rusefi-header">
        <div class="header-content">
            <a href="index.php" class="logo-link">
                <div class="logo">
                    <span class="rusefi-text">rus</span><span class="efi-text">EFI</span>
                </div>
            </a>
            <nav class="main-nav">
                <a href="index.php" class="nav-link"> <?= $texts[$lang]['shop_nav'] ?></a>
                <a href="index.php#footer-contacts" class="nav-link"> <?= $texts[$lang]['contact_nav'] ?></a>
                <a href="forum.php" class="nav-link active"> <?= $texts[$lang]['forum_nav'] ?></a>
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
                <a href="index.php#footer-contacts" class="mobile-nav-link"> <?= $texts[$lang]['contact_nav'] ?></a>
                <a href="forum.php" class="mobile-nav-link active"> <?= $texts[$lang]['forum_nav'] ?></a>
            </nav>
        </div>
    </header>

    <main class="rusefi-main">
        <h1 class="rusefi-title">
            <?= $texts[$lang]['forum_title'] ?>
        </h1>

        <div class="forum-header">
            <div class="user-info">
                <?php if ($user): ?>
                    <div class="user-info">
                        <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                        <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                        <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                            <a href="admin_users.php" class="btn btn-secondary" style="margin-right: 10px;">Админ-панель</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-secondary"><?= $texts[$lang]['logout'] ?></a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary"><?= $texts[$lang]['login'] ?></a>
                    <a href="register.php" class="btn btn-secondary"><?= $texts[$lang]['register'] ?></a>
                <?php endif; ?>
            </div>
            <div class="forum-actions">
                <?php if ($user): ?>
                    <a href="create_topic.php" class="btn"><?= $texts[$lang]['create_topic'] ?></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="forum-filters">
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Поиск по темам..." class="search-input">
                <button id="search-btn" class="btn">Поиск</button>
                <button id="reset-btn" class="btn btn-secondary">Сбросить</button>
            </div>
            <div class="categories">
                <button class="category-btn active" data-category="all">Все</button>
                <?php foreach ($categories as $key => $name): if ($key !== 'showcase'): ?>
                    <button class="category-btn" data-category="<?= $key ?>"><?= $name ?></button>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <table class="forum-table">
            <thead>
                <tr>
                    <th>Тема</th>
                    <th>Автор</th>
                    <th>Ответов</th>
                    <th>Просмотров</th>
                    <th>Последнее сообщение</th>
                </tr>
            </thead>
            <tbody id="topics-list">
                <?php if (empty($topics)): ?>
                    <tr>
                        <td colspan="5" class="no-topics">
                            <?= $texts[$lang]['no_topics'] ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_reverse($topics) as $topic_id => $topic): ?>
                        <tr>
                            <td>
                                <a href="topic.php?id=<?= $topic_id ?>" class="topic-title">
                                    <?= htmlspecialchars($topic['title']) ?>
                                </a>
                                <div class="topic-meta">
                                    Категория: <?= $categories[$topic['category']] ?? $topic['category'] ?>
                                </div>
                                <?php if ($user && isset($user['role']) && $user['role'] === 'admin'): ?>
                                    <div class="admin-actions" style="margin-top: 5px;">
                                        <button class="btn btn-secondary" style="font-size: 0.8rem; padding: 4px 8px;" onclick="deleteTopic('<?= $topic_id ?>')">Удалить тему</button>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($users[$topic['author_id']]['username'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <a href="profile.php?id=<?= $topic['author_id'] ?>" class="user-name">
                                        <?= htmlspecialchars($users[$topic['author_id']]['username'] ?? 'Unknown') ?>
                                    </a>
                                </div>
                            </td>
                            <td class="topic-stats">
                                <?= count($topic['posts'] ?? []) - 1 ?>
                            </td>
                            <td class="topic-stats">
                                <?= $topic['views'] ?? 0 ?>
                            </td>
                            <td class="last-post">
                                <?php
                                $posts = $topic['posts'] ?? [];
                                $last_post = end($posts);
                                if ($last_post) {
                                    $post_time = date('d.m.Y H:i', $last_post['created_at']);
                                    $post_author = $users[$last_post['author_id']]['username'] ?? 'Unknown';
                                    echo "$post_time<br>от $post_author";
                                } else {
                                    echo 'Нет сообщений';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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

    function getCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        for (let id in cart) {
            if (typeof cart[id] === 'number') {
                cart[id] = {quantity: cart[id], price: 0, name: ''};
            }
        }
        return cart;
    }

    function testCart() {
        // Добавим тестовый товар в корзину
        const cart = getCart();
        cart['test'] = {quantity: 5, price: 100, name: 'Test Item'};
        setCart(cart);
        updateCartUI();
        alert('Test item added to cart');
    }
    function setCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }
    function updateCartUI() {
        const cart = getCart();
        let count = 0;
        for (let id in cart) count += cart[id].quantity;
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }

    window.toggleMobileMenu = function() {
        console.log('toggleMobileMenu called');
        const menu = document.getElementById('mobile-menu');
        console.log('menu element:', menu);
        menu.classList.toggle('open');
        console.log('menu classList:', menu.classList);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCartUI();

        // Обработчик для бургер меню
        const burgerMenu = document.querySelector('.burger-menu');
        console.log('burgerMenu element:', burgerMenu);
        if (burgerMenu) {
            console.log('Adding event listeners to burgerMenu');
            burgerMenu.addEventListener('click', toggleMobileMenu);
            burgerMenu.addEventListener('touchstart', toggleMobileMenu);
        } else {
            console.log('burgerMenu not found');
        }

        // Обработчик для контактов в основной навигации
        var contacts = document.querySelector('.main-nav a[href="#"]');
        if (contacts) {
            contacts.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
            });
        }

        // Обработчик для контактов в мобильной навигации
        var mobileContacts = document.querySelector('.mobile-nav a[href="#"]');
        if (mobileContacts) {
            mobileContacts.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
                toggleMobileMenu();
            });
        }

        // Функция фильтрации тем
        function filterTopics(searchQuery = '', categoryFilter = 'all') {
            const rows = document.querySelectorAll('#topics-list tr');
            rows.forEach(row => {
                const title = row.querySelector('.topic-title').textContent.toLowerCase();
                const meta = row.querySelector('.topic-meta').textContent.toLowerCase();

                const matchesSearch = searchQuery === '' || title.includes(searchQuery.toLowerCase());
                const matchesCategory = categoryFilter === 'all' || meta.includes(categoryFilter.toLowerCase());

                row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
            });
        }

        // Фильтрация по категориям
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.onclick = function() {
                const category = this.getAttribute('data-category');
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const searchQuery = document.getElementById('search-input').value;
                filterTopics(searchQuery, category);
            };
        });

        // Поиск по темам
        document.getElementById('search-btn').onclick = function() {
            const searchQuery = document.getElementById('search-input').value;
            const activeCategory = document.querySelector('.category-btn.active').getAttribute('data-category');
            filterTopics(searchQuery, activeCategory);
        };

        // Поиск при нажатии Enter
        document.getElementById('search-input').onkeypress = function(e) {
            if (e.key === 'Enter') {
                document.getElementById('search-btn').click();
            }
        };

        // Сброс поиска и фильтров
        document.getElementById('reset-btn').onclick = function() {
            document.getElementById('search-input').value = '';
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.category-btn[data-category="all"]').classList.add('active');
            filterTopics('', 'all');
        };

        // Удаление темы (для админа)
        window.deleteTopic = function(topicId) {
            if (confirm('Вы уверены, что хотите удалить эту тему?')) {
                fetch('delete_topic.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'topic_id=' + encodeURIComponent(topicId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка при удалении темы: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при удалении темы');
                });
            }
        };
    });

    window.addEventListener('storage', function() { updateCartUI(); });

    // Периодическое обновление счетчика корзины
    setInterval(updateCartUI, 1000);
    </script>
</body>
</html>
