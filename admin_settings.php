<?php
session_start();

// Проверка авторизации и роли админа
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Загрузка пользователей
$users_file = __DIR__ . '/forum_users.json';
$users = [];
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true) ?: [];
}

$current_user = $users[$_SESSION['user_id']] ?? null;
if (!$current_user || !isset($current_user['role']) || $current_user['role'] !== 'admin') {
    header('Location: forum.php');
    exit;
}

// Дополнительная проверка на бан
if (isset($current_user['banned']) && $current_user['banned']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Загрузка настроек форума
$settings_file = __DIR__ . '/forum_settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true) ?: [];
}

// Установка значений по умолчанию
$settings = array_merge([
    'forum_name' => 'rusEFI Форум',
    'allow_registration' => true,
    'posts_per_page' => 20,
    'max_file_size' => 5,
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'maintenance_mode' => false,
    'maintenance_message' => 'Форум временно недоступен для технического обслуживания.'
], $settings);

// Обработка формы настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['forum_name'] = trim($_POST['forum_name'] ?? '');
    $settings['allow_registration'] = isset($_POST['allow_registration']);
    $settings['posts_per_page'] = (int)($_POST['posts_per_page'] ?? 20);
    $settings['max_file_size'] = (int)($_POST['max_file_size'] ?? 5);
    $settings['maintenance_mode'] = isset($_POST['maintenance_mode']);
    $settings['maintenance_message'] = trim($_POST['maintenance_message'] ?? '');

    file_put_contents($settings_file, json_encode($settings, JSON_UNESCAPED_UNICODE));
    $message = 'Настройки сохранены успешно!';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — Настройки форума</title>
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
        .rusefi-main {
            max-width: 800px;
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
        .settings-form {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-orange);
            margin-bottom: 8px;
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--main-bg);
            color: var(--text-white);
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--text-orange);
        }
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
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
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .message {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
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
    </style>
</head>
<body>
    <header class="rusefi-header">
        <div class="header-content">
            <div class="logo">
                <span class="rusefi-text">rus</span><span class="efi-text">EFI</span>
            </div>
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Магазин</a>
                <a href="index.php#footer-contacts" class="nav-link">Контакты</a>
                <a href="forum.php" class="nav-link active">Форум</a>
            </nav>
        </div>
    </header>

    <main class="rusefi-main">
        <a href="forum.php" class="back-link">← Вернуться к форуму</a>

        <h1 class="rusefi-title">Настройки форума</h1>

        <?php if (isset($message)): ?>
            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="settings-form">
            <form method="post">
                <div class="form-group">
                    <label for="forum_name" class="form-label">Название форума</label>
                    <input type="text" id="forum_name" name="forum_name" class="form-input" value="<?= htmlspecialchars($settings['forum_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" id="allow_registration" name="allow_registration" <?= $settings['allow_registration'] ? 'checked' : '' ?>>
                        <span>Разрешить регистрацию новых пользователей</span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="posts_per_page" class="form-label">Сообщений на страницу</label>
                    <input type="number" id="posts_per_page" name="posts_per_page" class="form-input" value="<?= htmlspecialchars($settings['posts_per_page']) ?>" min="5" max="100" required>
                </div>

                <div class="form-group">
                    <label for="max_file_size" class="form-label">Максимальный размер файла (МБ)</label>
                    <input type="number" id="max_file_size" name="max_file_size" class="form-input" value="<?= htmlspecialchars($settings['max_file_size']) ?>" min="1" max="50" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                        <span>Режим технического обслуживания</span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="maintenance_message" class="form-label">Сообщение о техническом обслуживании</label>
                    <textarea id="maintenance_message" name="maintenance_message" class="form-textarea" placeholder="Введите сообщение для пользователей..."><?= htmlspecialchars($settings['maintenance_message']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить настройки</button>
                    <a href="admin_users.php" class="btn" style="background: var(--card-bg); color: var(--text-white); border: 2px solid var(--text-orange);">Управление пользователями</a>
                </div>
            </form>
        </div>
    </main>

    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            Телефон для связи: <a href="tel:+77054111122" style="color:#e0e0e0;">+7 (705) 411-11-22</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#e0e0e0;">rusefi.com</a>
    </footer>
</body>
</html>
