<?php
session_start();
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: topic.php?id=' . ($_GET['id'] ?? ''));
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';

// --- Валюта ---
if (isset($_GET['currency'])) {
    setcookie('currency', $_GET['currency'], time() + 3600*24*30, '/');
    $_COOKIE['currency'] = $_GET['currency'];
    header('Location: topic.php?id=' . ($_GET['id'] ?? ''));
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
        'reply' => 'Ответить',
        'login_required' => 'Необходимо войти в систему',
        'post_reply' => 'Отправить ответ',
        'reply_content' => 'Текст ответа',
        'fill_reply' => 'Введите текст ответа',
        'reply_added' => 'Ответ добавлен!',
        'general_discussion' => 'Общее обсуждение',
        'technical_support' => 'Техническая поддержка',
        'showcase' => 'Показать',
        'off_topic' => 'Офф-топик',
        'posted_on' => 'опубликовано',
        'by' => '',
        'replies' => 'Ответов',
        'views' => 'Просмотров',
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
        'reply' => 'Жауап беру',
        'login_required' => 'Жүйеге кіру қажет',
        'post_reply' => 'Жауап жіберу',
        'reply_content' => 'Жауап мәтіні',
        'fill_reply' => 'Жауап мәтінін енгізіңіз',
        'reply_added' => 'Жауап қосылды!',
        'general_discussion' => 'Жалпы талқылау',
        'technical_support' => 'Техникалық қолдау',
        'showcase' => 'Көрсету',
        'off_topic' => 'Офф-топик',
        'posted_on' => 'жарияланған',
        'by' => '',
        'replies' => 'Жауаптар',
        'views' => 'Көрулер',
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
        'reply' => 'Reply',
        'login_required' => 'Login required',
        'post_reply' => 'Post Reply',
        'reply_content' => 'Reply content',
        'fill_reply' => 'Please enter reply content',
        'reply_added' => 'Reply added!',
        'general_discussion' => 'General Discussion',
        'technical_support' => 'Technical Support',
        'showcase' => 'Showcase',
        'off_topic' => 'Off Topic',
        'posted_on' => 'posted on',
        'by' => 'by',
        'replies' => 'Replies',
        'views' => 'Views',
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
}

// Получаем тему по ID
$topic_id = $_GET['id'] ?? '';
$topic = isset($topics[$topic_id]) ? $topics[$topic_id] : null;
if (!$topic) {
    header('Location: forum.php');
    exit;
}

// Категории форума
$categories = [
    'general' => $texts[$lang]['general_discussion'],
    'technical' => $texts[$lang]['technical_support'],
    'showcase' => $texts[$lang]['showcase'],
    'offtopic' => $texts[$lang]['off_topic']
];

// Увеличиваем счетчик просмотров
$topic['views'] = ($topic['views'] ?? 0) + 1;
$topics[$topic_id] = $topic;
file_put_contents($topics_file, json_encode($topics, JSON_UNESCAPED_UNICODE));

// Обработка ответа
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'])) {
    if (!$user) {
        $message = $texts[$lang]['login_required'];
    } else {
        $reply_content = trim($_POST['reply_content'] ?? '');
        if (empty($reply_content)) {
            $message = $texts[$lang]['fill_reply'];
        } else {
            // Обработка загруженных изображений
            $uploaded_images = [];
            if (isset($_FILES['reply_images']) && !empty($_FILES['reply_images']['name'][0])) {
                $upload_dir = __DIR__ . '/forum_images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB

                foreach ($_FILES['reply_images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $file_name = $_FILES['reply_images']['name'][$key];
                        $file_size = $_FILES['reply_images']['size'][$key];
                        $file_type = $_FILES['reply_images']['type'][$key];

                        // Проверка типа файла
                        if (!in_array($file_type, $allowed_types)) {
                            $message = 'Недопустимый тип файла. Разрешены только изображения.';
                            break;
                        }

                        // Проверка размера файла
                        if ($file_size > $max_size) {
                            $message = 'Файл слишком большой. Максимальный размер: 5MB.';
                            break;
                        }

                        // Генерация уникального имени файла
                        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                        $unique_name = uniqid('img_', true) . '.' . $file_extension;
                        $file_path = $upload_dir . $unique_name;

                        // Перемещение файла
                        if (move_uploaded_file($tmp_name, $file_path)) {
                            $uploaded_images[] = 'forum_images/' . $unique_name;
                        } else {
                            $message = 'Ошибка при загрузке файла.';
                            break;
                        }
                    }
                }
            }

            if (empty($message)) {
                // Добавляем ответ
                $topic['posts'][] = [
                    'author_id' => $_SESSION['user_id'],
                    'content' => $reply_content,
                    'images' => $uploaded_images,
                    'created_at' => time()
                ];

                // Сохраняем изменения
                $topics[$topic_id] = $topic;
                file_put_contents($topics_file, json_encode($topics, JSON_UNESCAPED_UNICODE));

                // Перенаправление для предотвращения повторной отправки
                header('Location: topic.php?id=' . $topic_id);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — <?= htmlspecialchars($topic['title']) ?></title>
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
        .topic-header {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .topic-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--text-orange);
            margin-bottom: 10px;
        }
        .topic-meta {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .topic-stats {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: #ccc;
        }
        .posts-container {
            margin-bottom: 30px;
        }
        .post {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--text-orange);
        }
        .post-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--text-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-black);
            font-weight: bold;
            font-size: 1rem;
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            color: var(--text-orange);
            margin-bottom: 2px;
        }
        .post-date {
            font-size: 0.85rem;
            color: #ccc;
        }
        .post-content {
            color: var(--text-white);
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .reply-form {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-orange);
            margin-bottom: 8px;
        }
        .form-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--main-bg);
            color: var(--text-white);
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.2s;
        }
        .form-textarea:focus {
            outline: none;
            border-color: var(--text-orange);
        }
        .btn {
            background: var(--text-orange);
            color: var(--text-black);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
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
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        .message {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.error {
            background: #dc3545;
            color: white;
        }
        .message.success {
            background: #28a745;
            color: white;
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
            .topic-title { font-size: 1.5rem; }
            .post-header { flex-direction: column; align-items: flex-start; gap: 10px; }
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
            .topic-header { padding: 15px; }
            .topic-title { font-size: 1.3rem; }
            .post { padding: 15px; }
            .reply-form { padding: 15px; }
            .form-actions { flex-direction: column; }
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
                        <input type="hidden" name="id" value="<?= htmlspecialchars($topic_id) ?>">
                        <select name="currency" onchange="this.form.submit()">
                            <option value="KZT" <?= $currency == 'KZT' ? 'selected' : '' ?>>KZT</option>
                            <option value="RUB" <?= $currency == 'RUB' ? 'selected' : '' ?>>RUB</option>
                            <option value="USD" <?= $currency == 'USD' ? 'selected' : '' ?>>USD</option>
                        </select>
                    </form>
                </div>
                <div class="lang-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($topic_id) ?>">
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

        <div class="topic-header">
            <h1 class="topic-title"><?= htmlspecialchars($topic['title']) ?></h1>
            <div class="topic-meta">
                Категория: <?= $categories[$topic['category']] ?? $topic['category'] ?> |
                Автор: <?= htmlspecialchars($users[$topic['author_id']]['username'] ?? 'Unknown') ?> |
                Создано: <?= date('d.m.Y H:i', $topic['created_at']) ?>
            </div>
            <div class="topic-stats">
                <span><?= $texts[$lang]['replies'] ?>: <?= count($topic['posts']) - 1 ?></span>
                <span><?= $texts[$lang]['views'] ?>: <?= $topic['views'] ?? 0 ?></span>
            </div>
        </div>

        <div class="posts-container">
            <?php foreach ($topic['posts'] as $index => $post): ?>
                <div class="post">
                    <div class="post-header">
                        <div class="user-avatar">
                            <?= strtoupper(substr($users[$post['author_id']]['username'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name">
                                <a href="profile.php?id=<?= $post['author_id'] ?>" style="color: var(--text-orange); text-decoration: none;">
                                    <?= htmlspecialchars($users[$post['author_id']]['username'] ?? 'Unknown') ?>
                                </a>
                                <?php if ($index === 0): ?>
                                    <span style="font-size: 0.8rem; color: #ccc;">(<?= $texts[$lang]['posted_on'] ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="post-date">
                                <?= date('d.m.Y H:i', $post['created_at']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                        <?php if (isset($post['images']) && is_array($post['images']) && !empty($post['images'])): ?>
                            <div class="post-images" style="margin-top: 15px;">
                                <?php foreach ($post['images'] as $image): ?>
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Изображение" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 10px; display: block;">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($user): ?>
            <div class="reply-form">
                <h3 style="color: var(--text-orange); margin-bottom: 20px;"><?= $texts[$lang]['reply'] ?></h3>

                <?php if ($message): ?>
                    <div class="message error">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="reply_content" class="form-label"><?= $texts[$lang]['reply_content'] ?></label>
                        <textarea id="reply_content" name="reply_content" class="form-textarea" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="reply_images" class="form-label">Изображения (необязательно)</label>
                        <input type="file" id="reply_images" name="reply_images[]" class="form-input" multiple accept="image/*" onchange="previewReplyImages(this)">
                        <small style="color: #666; font-size: 0.9rem;">Можно выбрать несколько изображений (макс. 5 МБ каждое)</small>
                        <div id="reply-image-preview" class="image-preview" style="margin-top: 10px;"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn"><?= $texts[$lang]['post_reply'] ?></button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="message error">
                <?= $texts[$lang]['login_required'] ?> <a href="login.php" style="color: var(--text-orange);">Войти</a>
            </div>
        <?php endif; ?>
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

    function previewReplyImages(input) {
        const preview = document.getElementById('reply-image-preview');
        preview.innerHTML = '';

        if (input.files) {
            // Store files in a custom property to maintain correct order
            input._selectedFiles = Array.from(input.files);

            Array.from(input.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = document.createElement('div');
                        container.className = 'image-preview-item';
                        container.style.cssText = 'display: inline-block; position: relative; margin: 5px; border: 2px solid #ddd; border-radius: 8px; overflow: hidden;';
                        container.dataset.index = index;

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.cssText = 'width: 100px; height: 100px; object-fit: cover; display: block;';

                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '×';
                        removeBtn.style.cssText = 'position: absolute; top: 2px; right: 2px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 14px; line-height: 1;';
                        removeBtn.onclick = function() {
                            const removeIndex = parseInt(container.dataset.index);
                            container.remove();

                            // Remove from our stored files array
                            if (input._selectedFiles) {
                                input._selectedFiles.splice(removeIndex, 1);
                            }

                            // Update indices for remaining containers
                            const remainingContainers = preview.querySelectorAll('.image-preview-item');
                            remainingContainers.forEach((cont, idx) => {
                                cont.dataset.index = idx;
                            });

                            // Re-trigger preview to maintain correct order
                            previewReplyImages(input);
                        };

                        container.appendChild(img);
                        container.appendChild(removeBtn);
                        preview.appendChild(container);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
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
