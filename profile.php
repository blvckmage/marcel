<?php
session_start();
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: profile.php?id=' . ($_GET['id'] ?? ''));
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';

// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: profile.php?id=' . ($_GET['id'] ?? ''));
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
        'back_to_forum' => 'Вернуться к форуму',
        'profile_title' => 'Профиль пользователя',
        'member_since' => 'Участник с',
        'last_login' => 'Последний вход',
        'total_posts' => 'Всего сообщений',
        'total_topics' => 'Создано тем',
        'recent_posts' => 'Последние сообщения',
        'no_posts' => 'Сообщений нет',
        'view_topic' => 'Посмотреть тему',
        'user_not_found' => 'Пользователь не найден',
        'topics_created' => 'Созданные темы',
        'posts_made' => 'Сообщения',
        'never' => 'никогда',
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
        'back_to_forum' => 'Форумға оралу',
        'profile_title' => 'Пайдаланушы профилі',
        'member_since' => 'Қатысушы',
        'last_login' => 'Соңғы кіру',
        'total_posts' => 'Барлық хабарламалар',
        'total_topics' => 'Жасалған тақырыптар',
        'recent_posts' => 'Соңғы хабарламалар',
        'no_posts' => 'Хабарламалар жоқ',
        'view_topic' => 'Тақырыпты қарау',
        'user_not_found' => 'Пайдаланушы табылмады',
        'topics_created' => 'Жасалған тақырыптар',
        'posts_made' => 'Хабарламалар',
        'never' => 'ешқашан',
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
        'back_to_forum' => 'Back to Forum',
        'profile_title' => 'User Profile',
        'member_since' => 'Member since',
        'last_login' => 'Last login',
        'total_posts' => 'Total posts',
        'total_topics' => 'Topics created',
        'recent_posts' => 'Recent posts',
        'no_posts' => 'No posts',
        'view_topic' => 'View topic',
        'user_not_found' => 'User not found',
        'topics_created' => 'Topics created',
        'posts_made' => 'Posts made',
        'never' => 'never',
    ]
];

// Загрузка пользователей
$users_file = __DIR__ . '/forum_users.json';
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

// Загрузка тем форума
$topics_file = __DIR__ . '/forum_topics.json';
$topics = [];
if (file_exists($topics_file)) {
    $topics = json_decode(file_get_contents($topics_file), true) ?: [];
}

// Проверка авторизации
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = $users[$_SESSION['user_id']] ?? null;
}

// Получаем пользователя по ID
$user_id = $_GET['id'] ?? '';
$profile_user = isset($users[$user_id]) ? $users[$user_id] : null;

if (!$profile_user) {
    // Если ID не указан, показываем профиль текущего пользователя
    if ($current_user) {
        $profile_user = $current_user;
        $user_id = $_SESSION['user_id'];
    } else {
        // Пользователь не найден и не авторизован
        header('Location: forum.php');
        exit;
    }
}

// Получаем статистику пользователя
$user_stats = [
    'total_posts' => 0,
    'total_topics' => 0,
    'recent_posts' => []
];

// Подсчитываем статистику
foreach ($topics as $topic_id => $topic) {
    $is_author = $topic['author_id'] === $user_id;
    $post_count = count($topic['posts']);

    if ($is_author) {
        $user_stats['total_topics']++;
        $user_stats['total_posts'] += $post_count;
    } else {
        // Проверяем посты пользователя в этой теме
        foreach ($topic['posts'] as $post) {
            if ($post['author_id'] === $user_id) {
                $user_stats['total_posts']++;
                $user_stats['recent_posts'][] = [
                    'topic_id' => $topic_id,
                    'topic_title' => $topic['title'],
                    'content' => substr($post['content'], 0, 100) . (strlen($post['content']) > 100 ? '...' : ''),
                    'created_at' => $post['created_at']
                ];
            }
        }
    }
}

// Ограничиваем количество недавних постов
$user_stats['recent_posts'] = array_slice(array_reverse($user_stats['recent_posts']), 0, 10);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — <?= $texts[$lang]['profile_title'] ?> - <?= htmlspecialchars($profile_user['username']) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
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
        .currency-switcher, .lang-switcher {
            position: relative;
        }
        .currency-switcher select, .lang-switcher select {
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
        .currency-switcher select:hover, .lang-switcher select:hover {
            background-color: #333;
            border-color: var(--text-orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .currency-switcher select:focus, .lang-switcher select:focus {
            outline: none;
            border-color: var(--text-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
        }
        .currency-switcher select option, .lang-switcher select option {
            background: var(--text-black);
            color: var(--text-white);
            padding: 8px;
        }
        .rusefi-main {
            max-width: 1000px;
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
        .profile-header {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--text-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-black);
            font-weight: bold;
            font-size: 2.5rem;
            margin: 0 auto 20px;
        }
        .user-name {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-orange);
            margin-bottom: 10px;
        }
        .user-email {
            color: #666;
            margin-bottom: 20px;
        }
        .user-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-orange);
            display: block;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        .user-info {
            color: #666;
            font-size: 0.9rem;
        }
        .content-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-orange);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--text-orange);
            padding-bottom: 10px;
        }
        .recent-post {
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
        }
        .recent-post:last-child {
            border-bottom: none;
        }
        .post-topic {
            font-weight: 600;
            color: var(--text-orange);
            margin-bottom: 5px;
        }
        .post-content {
            color: var(--text-white);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .post-date {
            color: #666;
            font-size: 0.8rem;
        }
        .topic-link {
            color: var(--text-orange);
            text-decoration: none;
            font-weight: 500;
        }
        .topic-link:hover {
            text-decoration: underline;
        }
        .no-posts {
            text-align: center;
            color: #666;
            padding: 30px;
            font-style: italic;
        }
        .back-link {
            color: var(--text-orange);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* Tablet styles */
        @media (max-width: 1024px) {
            .rusefi-main { padding: 32px 24px; }
        }
        @media (max-width: 768px) {
            .rusefi-header { padding: 16px 20px; }
            .main-nav { gap: 20px; }
            .header-right { gap: 15px; }
            .rusefi-main { padding: 32px 20px; }
            .rusefi-title { font-size: 2.2rem; margin-bottom: 32px; }
            .profile-header { padding: 25px; }
            .user-avatar { width: 80px; height: 80px; font-size: 2rem; }
            .user-name { font-size: 1.8rem; }
            .user-stats { gap: 20px; }
        }
        @media (max-width: 480px) {
            .rusefi-header { padding: 12px 16px; }
            .rusefi-logo { font-size: 1.5rem; }
            .main-nav { gap: 15px; }
            .nav-link { font-size: 0.9rem; }
            .header-right { gap: 10px; }
            .currency-switcher select,
            .lang-switcher select {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            .rusefi-main { padding: 24px 16px; }
            .rusefi-title { font-size: 1.8rem; margin-bottom: 24px; }
            .profile-header { padding: 20px; }
            .user-avatar { width: 70px; height: 70px; font-size: 1.8rem; }
            .user-name { font-size: 1.5rem; }
            .user-stats { flex-direction: column; gap: 15px; }
            .stat-item { display: flex; justify-content: space-between; text-align: left; }
            .content-section { padding: 15px; }
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
                <a href="forum.php" class="nav-link active"> <?= $texts[$lang]['forum_nav'] ?></a>
            </nav>
            <div class="header-right">
                <div class="currency-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($user_id) ?>">
                        <select name="currency" onchange="this.form.submit()">
                            <option value="KZT" <?= $currency == 'KZT' ? 'selected' : '' ?>>KZT</option>
                            <option value="RUB" <?= $currency == 'RUB' ? 'selected' : '' ?>>RUB</option>
                            <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </form>
                </div>
                <div class="lang-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($user_id) ?>">
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
            </div>
        </div>
    </header>

    <main class="rusefi-main">
        <a href="forum.php" class="back-link">← <?= $texts[$lang]['back_to_forum'] ?></a>

        <h1 class="rusefi-title">
            <?= $texts[$lang]['profile_title'] ?>
        </h1>

        <div class="profile-header">
            <div class="user-avatar">
                <?= strtoupper(substr($profile_user['username'], 0, 1)) ?>
            </div>
            <h2 class="user-name"><?= htmlspecialchars($profile_user['username']) ?></h2>
            <div class="user-email"><?= htmlspecialchars($profile_user['email']) ?></div>

            <div class="user-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= $user_stats['total_topics'] ?></span>
                    <span class="stat-label"><?= $texts[$lang]['total_topics'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $user_stats['total_posts'] ?></span>
                    <span class="stat-label"><?= $texts[$lang]['total_posts'] ?></span>
                </div>
            </div>

            <div class="user-info">
                <div><?= $texts[$lang]['member_since'] ?>: <?= date('d.m.Y', $profile_user['created_at']) ?></div>
                <div><?= $texts[$lang]['last_login'] ?>: <?= $profile_user['last_login'] ? date('d.m.Y H:i', $profile_user['last_login']) : $texts[$lang]['never'] ?></div>
            </div>
        </div>

        <div class="content-section">
            <h3 class="section-title"><?= $texts[$lang]['recent_posts'] ?></h3>

            <?php if (empty($user_stats['recent_posts'])): ?>
                <div class="no-posts">
                    <?= $texts[$lang]['no_posts'] ?>
                </div>
            <?php else: ?>
                <?php foreach ($user_stats['recent_posts'] as $post): ?>
                    <div class="recent-post">
                        <div class="post-topic">
                            <a href="topic.php?id=<?= $post['topic_id'] ?>" class="topic-link">
                                <?= htmlspecialchars($post['topic_title']) ?>
                            </a>
                        </div>
                        <div class="post-content">
                            <?= htmlspecialchars($post['content']) ?>
                        </div>
                        <div class="post-date">
                            <?= date('d.m.Y H:i', $post['created_at']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

    function getCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
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

    document.addEventListener('DOMContentLoaded', function() {
        updateCartUI();

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
