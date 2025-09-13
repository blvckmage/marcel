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

// Обработка бана/разбана пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'] ?? '';
    $action = $_POST['action'];

    if (isset($users[$user_id])) {
        if ($action === 'ban') {
            $users[$user_id]['banned'] = true;
        } elseif ($action === 'unban') {
            unset($users[$user_id]['banned']);
        }

        file_put_contents($users_file, json_encode($users, JSON_UNESCAPED_UNICODE));
        $message = $action === 'ban' ? 'Пользователь забанен' : 'Пользователь разбанен';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — Управление пользователями</title>
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
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .users-table th {
            background: var(--header-bg);
            color: var(--text-black);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .users-table td {
            color: var(--text-white);
        }
        .user-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active {
            background: #28a745;
            color: white;
        }
        .status-banned {
            background: #dc3545;
            color: white;
        }
        .status-admin {
            background: var(--text-orange);
            color: var(--text-black);
        }
        .btn {
            background: var(--text-orange);
            color: var(--text-black);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #e55a00;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
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

        <h1 class="rusefi-title">Управление пользователями</h1>

        <?php if (isset($message)): ?>
            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-bottom: 20px;">
            <a href="admin_settings.php" class="btn" style="background: var(--card-bg); color: var(--text-white); border: 2px solid var(--text-orange);">Настройки форума</a>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя пользователя</th>
                    <th>Email</th>
                    <th>Статус</th>
                    <th>Дата регистрации</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user_id => $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user_id) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                                <span class="user-status status-admin">Админ</span>
                            <?php elseif (isset($user['banned']) && $user['banned']): ?>
                                <span class="user-status status-banned">Забанен</span>
                            <?php else: ?>
                                <span class="user-status status-active">Активен</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y H:i', $user['created_at']) ?></td>
                        <td>
                            <?php if ($user_id !== $_SESSION['user_id'] && (!isset($user['role']) || $user['role'] !== 'admin')): ?>
                                <?php if (isset($user['banned']) && $user['banned']): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                                        <input type="hidden" name="action" value="unban">
                                        <button type="submit" class="btn btn-success">Разбанить</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                                        <input type="hidden" name="action" value="ban">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите забанить этого пользователя?')">Забанить</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            Телефон для связи: <a href="tel:+77054111122" style="color:#e0e0e0;">+7 (705) 411-11-22</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#e0e0e0;">rusefi.com</a>
    </footer>
</body>
</html>
