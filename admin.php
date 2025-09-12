<?php
session_start();
$admin_password = 'admin123'; // Задайте свой пароль
// Авторизация
if (!isset($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['admin'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Неверный пароль';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Вход в админ-панель — rusEFI</title>
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
                gap: 8px;
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
            .admin-login-box {
                background: var(--rusefi-card);
                border-radius: 12px;
                box-shadow: 0 2px 8px #18181822;
                max-width: 400px;
                margin: 80px auto 0 auto;
                padding: 36px 32px 28px 32px;
                border: 1px solid #333;
            }
            .admin-login-box h2 {
                color: #fff;
                font-weight: 800;
                margin-bottom: 24px;
                text-align: center;
            }
            .admin-login-box label {
                color: #fff;
                font-weight: 500;
                margin-bottom: 6px;
            }
            .admin-login-box input[type="password"] {
                background: #232323;
                color: #fff;
                border: 1.5px solid #333;
                border-radius: 8px;
                padding: 12px 16px;
                font-size: 1.1rem;
                margin-bottom: 18px;
                width: 100%;
                transition: border .2s, background .2s;
            }
            .admin-login-box input[type="password"]:focus {
                border-color: #ff7a1a;
                outline: none;
                background: #181818;
            }
            .admin-login-box .btn-primary, .admin-login-box .btn-secondary {
                background: var(--rusefi-orange);
                color: #181818;
                border: none;
                border-radius: 10px;
                font-weight: 700;
                font-size: 1.1rem;
                padding: 14px 0;
                width: 100%;
                margin: 0 0 10px 0;
                transition: background 0.18s, color 0.18s;
            }
            .admin-login-box .btn-primary:hover, .admin-login-box .btn-secondary:hover {
                background: #ff9a4d;
                color: #fff;
            }
            .admin-login-box .btn-secondary {
                background: #232323;
                color: #ff7a1a;
                border: 2px solid #ff7a1a;
            }
            .admin-login-box .btn-secondary:hover {
                background: #ff7a1a;
                color: #232323;
            }
            .admin-login-box .alert {
                border-radius: 10px;
                font-size: 1rem;
                margin-bottom: 18px;
            }
            @media (max-width: 600px) {
                .rusefi-header { padding: 0 10px; height: 48px; }
                .rusefi-logo { font-size: 1.3rem; }
                .admin-login-box { max-width: 98vw; margin: 24px auto 0 auto; padding: 18px 6vw 18px 6vw; }
                .admin-login-box h2 { font-size: 1.2rem; margin-bottom: 18px; }
                .admin-login-box input[type="password"] { font-size: 1rem; padding: 8px 10px; }
            }
        </style>
    </head>
    <body>
    <header class="rusefi-header">
        <div style="display: flex; align-items: center;">
            <span class="rusefi-logo">rusEFI</span>
            <nav class="rusefi-header-menu">
                <a href="index.php">Магазин</a>
                <a href="index.php#contacts">Контакты</a>
                <a href="forum.php">Форум</a>
            </nav>
        </div>
        <a href="index.php" class="btn btn-primary admin-back-btn" style="min-width:110px;text-align:center;">Назад</a>
    </header>
    <div class="admin-login-box">
        <h2>Вход в админ-панель</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="password" class="form-label">Пароль администратора</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Войти</button>
        </form>
    </div>

    </body>
    </html>
    <?php
    exit;
}
// Выход
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: admin.php');
    exit;
}
// --- Каталог товаров ---
$products_file = __DIR__ . '/products.json';
$products = [];
if (file_exists($products_file)) {
    $products = json_decode(file_get_contents($products_file), true) ?: [];
}
// Добавление товара с загрузкой файла
if (isset($_POST['add_product'])) {
    $new = [
        'id' => time(),
        'name' => [
            'ru' => trim($_POST['name_ru'] ?? ''),
            'en' => trim($_POST['name_en'] ?? ''),
            'kz' => trim($_POST['name_kz'] ?? '')
        ],
        'price' => (int)($_POST['price'] ?? 0),
        'images' => [],
        'description' => [
            'ru' => trim($_POST['desc_ru'] ?? ''),
            'en' => trim($_POST['desc_en'] ?? ''),
            'kz' => trim($_POST['desc_kz'] ?? '')
        ],
        'options' => json_decode(trim($_POST['options'] ?? ''), true) ?: []
    ];
    // Обработка загрузки файлов
    if (isset($_FILES['img_files'])) {
        foreach ($_FILES['img_files']['name'] as $key => $name) {
            if ($_FILES['img_files']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $fname = 'images/' . uniqid('img_', true) . '.' . $ext;
                    if (move_uploaded_file($_FILES['img_files']['tmp_name'][$key], $fname)) {
                        $new['images'][] = $fname;
                    }
                }
            }
        }
    }
    if ($new['name']['ru'] && $new['name']['kz'] && $new['price'] > 0 && !empty($new['images'])) {
        $products[] = $new;
        file_put_contents($products_file, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header('Location: admin.php?tab=catalog');
    exit;
}
// Удаление товара
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $id = (int)$_POST['product_id'];
    // Удаляем файл, если это локальное изображение
    foreach ($products as $p) {
        if ($p['id'] === $id && isset($p['img']) && strpos($p['img'], 'images/') === 0 && file_exists($p['img'])) {
            @unlink($p['img']);
        }
    }
    $products = array_values(array_filter($products, fn($p) => $p['id'] !== $id));
    file_put_contents($products_file, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: admin.php?tab=catalog');
    exit;
}
// Обновление только названия или цены товара (AJAX)
if (isset($_POST['product_id']) && (!empty($_POST['name_ru']) || !empty($_POST['name_en']) || !empty($_POST['name_kz']) || !empty($_POST['desc_ru']) || !empty($_POST['desc_en']) || !empty($_POST['desc_kz']) || isset($_POST['price'])) && !isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    foreach ($products as &$p) {
        if ($p['id'] === $id) {
            if (!is_array($p['name'])) $p['name'] = ['ru'=>$p['name'],'en'=>'','kz'=>''];
            if (!is_array($p['description'])) $p['description'] = ['ru'=>$p['description'],'en'=>'','kz'=>''];
            if (isset($_POST['name_ru'])) $p['name']['ru'] = trim($_POST['name_ru']);
            if (isset($_POST['name_en'])) $p['name']['en'] = trim($_POST['name_en']);
            if (isset($_POST['name_kz'])) $p['name']['kz'] = trim($_POST['name_kz']);
            if (isset($_POST['desc_ru'])) $p['description']['ru'] = trim($_POST['desc_ru']);
            if (isset($_POST['desc_en'])) $p['description']['en'] = trim($_POST['desc_en']);
            if (isset($_POST['desc_kz'])) $p['description']['kz'] = trim($_POST['desc_kz']);
            if (isset($_POST['price'])) $p['price'] = (int)$_POST['price'];
        }
    }
    unset($p);
    file_put_contents($products_file, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    exit;
}
// Редактирование товара с загрузкой файла и удалением старого
if (isset($_POST['edit_product']) && isset($_POST['product_id'])) {
    $id = (int)$_POST['product_id'];
    foreach ($products as &$p) {
        if ($p['id'] === $id) {
            // Миграция старых товаров
            if (!is_array($p['name'])) $p['name'] = ['ru'=>$p['name'],'en'=>'','kz'=>''];
            if (!is_array($p['description'])) $p['description'] = ['ru'=>$p['description'],'en'=>'','kz'=>''];
            $p['name']['ru'] = trim($_POST['name_ru'] ?? $p['name']['ru']);
            $p['name']['en'] = trim($_POST['name_en'] ?? $p['name']['en']);
            $p['name']['kz'] = trim($_POST['name_kz'] ?? $p['name']['kz']);
            $p['price'] = (int)($_POST['price'] ?? $p['price']);
            $p['description']['ru'] = trim($_POST['desc_ru'] ?? $p['description']['ru']);
            $p['description']['en'] = trim($_POST['desc_en'] ?? $p['description']['en']);
            $p['description']['kz'] = trim($_POST['desc_kz'] ?? $p['description']['kz']);
            $p['options'] = json_decode(trim($_POST['options'] ?? ''), true) ?: $p['options'];
            // Обработка загрузки файла
            if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed)) {
                    $fname = 'images/' . uniqid('img_', true) . '.' . $ext;
                    if (move_uploaded_file($_FILES['img_file']['tmp_name'], $fname)) {
                        // Удаляем старый файл, если это локальное изображение
                        if (isset($p['img']) && strpos($p['img'], 'images/') === 0 && file_exists($p['img'])) {
                            @unlink($p['img']);
                        }
                        $p['img'] = $fname;
                    }
                }
            }
            // Если не загружали файл — используем текстовое поле
            if (!$p['img'] && isset($_POST['img'])) {
                $p['img'] = trim($_POST['img']);
            }
        }
    }
    unset($p);
    file_put_contents($products_file, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: admin.php?tab=catalog');
    exit;
}
// --- Заявки ---
$file = __DIR__ . '/orders.json';
$orders = [];
if (file_exists($file)) {
    $orders = json_decode(file_get_contents($file), true) ?: [];
}
// Удаление заявки
if (isset($_POST['delete']) && isset($_POST['order_id'])) {
    $del_id = (int)$_POST['order_id'];
    if (isset($orders[$del_id])) {
        array_splice($orders, $del_id, 1);
        file_put_contents($file, json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header('Location: admin.php');
    exit;
}
// Добавление/редактирование комментария
if (isset($_POST['comment']) && isset($_POST['order_id'])) {
    $comment_id = (int)$_POST['order_id'];
    $comment = trim($_POST['comment_text'] ?? '');
    if (isset($orders[$comment_id])) {
        $orders[$comment_id]['admin_comment'] = $comment;
        file_put_contents($file, json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header('Location: admin.php');
    exit;
}
// Поиск и фильтрация по дате
$search = trim($_GET['search'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$filtered_orders = $orders;
if ($search !== '' || $date_from !== '' || $date_to !== '') {
    $filtered_orders = array_filter($orders, function($order) use ($search, $date_from, $date_to) {
        $ok = true;
        if ($search !== '') {
            $ok = false;
            if (stripos($order['name'], $search) !== false) $ok = true;
            if (stripos($order['phone'], $search) !== false) $ok = true;
            foreach ($order['items'] as $item) {
                if (stripos($item['name'], $search) !== false) $ok = true;
            }
        }
        if ($ok && $date_from !== '') {
            $order_time = strtotime($order['date']);
            $from_time = strtotime($date_from . ' 00:00:00');
            if ($order_time < $from_time) $ok = false;
        }
        if ($ok && $date_to !== '') {
            $order_time = strtotime($order['date']);
            $to_time = strtotime($date_to . ' 23:59:59');
            if ($order_time > $to_time) $ok = false;
        }
        return $ok;
    });
}
// Определяем активный таб
$tab = $_GET['tab'] ?? 'orders';
// --- Сортировка каталога ---
$sort = $_GET['sort'] ?? '';
$order = $_GET['order'] ?? 'asc';
if ($tab === 'catalog' && $sort) {
    usort($products, function($a, $b) use ($sort, $order) {
        if ($sort === 'name') {
            $res = strcmp(mb_strtolower($a['name']), mb_strtolower($b['name']));
        } elseif ($sort === 'price') {
            $res = $a['price'] <=> $b['price'];
        } else {
            $res = 0;
        }
        return $order === 'asc' ? $res : -$res;
    });
}
function sort_link($label, $field, $tab, $sort, $order) {
    $new_order = ($sort === $field && $order === 'asc') ? 'desc' : 'asc';
    $arrow = '';
    if ($sort === $field) $arrow = $order === 'asc' ? ' ▲' : ' ▼';
    return '<a href="admin.php?tab=' . $tab . '&sort=' . $field . '&order=' . $new_order . '">' . $label . $arrow . '</a>';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель — rusEFI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --header-bg: #ff6600;
            --main-bg: #2a2a2a;
            --text-white: #ffffff;
            --text-orange: #ff6600;
            --text-black: #000000;
            --border-color: #444444;
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
            gap: 8px;
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
        .rusefi-main {
            max-width: 1100px;
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
        .admin-toggle-group {
            display: flex;
            justify-content: center;
            margin: 0 auto 24px auto;
            background: var(--rusefi-card);
            border-radius: 8px;
            border: 1.5px solid var(--rusefi-orange);
            box-shadow: 0 1px 4px #18181822;
            width: fit-content;
            overflow: hidden;
        }
        .admin-toggle-btn {
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 32px;
            border: none;
            background: transparent;
            color: var(--rusefi-accent);
            transition: background .18s, color .18s;
            outline: none;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-toggle-btn.active {
            background: var(--rusefi-orange);
            color: #fff;
            box-shadow: 0 1px 4px #ff7a1a33 inset;
        }
        .admin-toggle-btn:not(:last-child) {
            border-right: 1.5px solid #ffb366;
        }
        .admin-add-form, .admin-form, .admin-block, .admin-table, .admin-card, .admin-panel, .admin-section, .admin-content, .admin-modal {
            background: var(--rusefi-card);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            border: 1px solid #333;
        }
        .admin-add-form {
            border: 1.5px solid var(--rusefi-orange);
            padding: 18px 24px 10px 24px;
            margin-bottom: 24px;
        }
        .admin-add-form input, .admin-add-form textarea {
            background: #232323;
            color: #fff;
            border: 1.5px solid #333;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1.1rem;
            margin-bottom: 12px;
            width: 100%;
        }
        .admin-add-form label { color: #fff; font-weight: 500; margin-bottom: 6px; }
        .input-file-label {
            display: inline-block;
            padding: 10px 22px;
            background: var(--rusefi-orange);
            color: #181818;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background .18s;
            margin-bottom: 0;
        }
        .input-file-label:hover {
            background: #ff9a4d;
            color: #fff;
        }
        .input-file-name {
            margin-left: 16px;
            font-size: 1rem;
            color: #fff;
            vertical-align: middle;
        }
        .admin-table {
            background: var(--rusefi-card);
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            border: 1px solid #333;
            margin-bottom: 24px;
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: #fff;
            background: var(--rusefi-card);
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #333;
            background: var(--rusefi-card);
            vertical-align: middle;
        }
        th {
            background: #232323;
            color: var(--rusefi-accent);
            font-weight: 700;
        }
        tr:last-child td { border-bottom: none; }
        td:first-child, th:first-child { border-radius: 12px 0 0 12px; }
        td:last-child, th:last-child { border-radius: 0 12px 12px 0; }
        input[type="text"], textarea {
            background: #fff;
            color: #444;
            border: 1.5px solid #bbb;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 1rem;
            width: 100%;
            transition: border .2s, background .2s, color .2s;
        }
        input[type="text"]::placeholder, textarea::placeholder {
            color: #888;
            opacity: 1;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: #ff7a1a;
            outline: none;
            background: #fff;
            color: #222;
        }
        .btn, .btn-primary, .btn-outline-primary, .btn-danger, .btn-outline-danger, .btn-outline-secondary {
            background: var(--rusefi-orange) !important;
            color: #181818 !important;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 12px 28px;
            margin-right: 8px;
            transition: background 0.18s, color 0.18s;
        }
        .btn:hover, .btn-primary:hover, .btn-outline-primary:hover, .btn-danger:hover, .btn-outline-danger:hover, .btn-outline-secondary:hover {
            background: #ff9a4d !important;
            color: #fff !important;
        }
        .btn-outline-danger, .btn-danger {
            background: #232323 !important;
            color: #ff7a1a !important;
            border: 2px solid #ff7a1a !important;
        }
        .btn-outline-danger:hover, .btn-danger:hover {
            background: #ff7a1a !important;
            color: #232323 !important;
        }
        .admin-login-box {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            max-width: 400px;
            margin: 80px auto 0 auto;
            padding: 36px 32px 28px 32px;
            border: 1px solid #333;
        }
        .admin-login-box h2 {
            color: #fff;
            font-weight: 800;
            margin-bottom: 24px;
            text-align: center;
        }
        .admin-login-box label {
            color: #fff;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .admin-login-box input[type="password"] {
            background: #232323;
            color: #fff;
            border: 1.5px solid #333;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1.1rem;
            margin-bottom: 18px;
            width: 100%;
            transition: border .2s, background .2s;
        }
        .admin-login-box input[type="password"]:focus {
            border-color: #ff7a1a;
            outline: none;
            background: #181818;
        }
        .admin-login-box .btn-primary, .admin-login-box .btn-secondary {
            width: 100%;
            margin: 0 0 10px 0;
        }
        .admin-login-box .alert {
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 18px;
        }
        .admin-login-btns {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 8px;
        }
        .admin-back-btn {
            background: var(--rusefi-orange) !important;
            color: #181818 !important;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px 0;
            transition: background 0.18s, color 0.18s;
            display: inline-block;
            text-align: center;
        }
        .admin-back-btn:hover {
            background: #ff9a4d !important;
            color: #fff !important;
        }
        .admin-logout-btn {
            background: #232323 !important;
            color: #ff7a1a !important;
            border: 2px solid #ff7a1a !important;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px 0;
            transition: background 0.18s, color 0.18s;
            display: inline-block;
            text-align: center;
        }
        .admin-logout-btn:hover {
            background: #ff7a1a !important;
            color: #232323 !important;
        }
        .admin-catalog-btns {
            display: flex;
            flex-direction: row;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .admin-catalog-btns .btn {
            min-width: 110px;
            font-size: 1rem;
            padding: 10px 0;
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
            .rusefi-title { font-size: 2.2rem; margin-bottom: 32px; }
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
            }
            .rusefi-main { padding: 24px 16px; }
            .rusefi-title { font-size: 1.8rem; margin-bottom: 24px; }
        }
        @media (max-width: 600px) {
            .rusefi-header { padding: 0 10px; height: 48px; }
            .rusefi-logo { font-size: 1.3rem; }
            .rusefi-title { font-size: 1.5rem; margin-bottom: 18px; }
            .admin-login-box { max-width: 98vw; margin: 24px auto 0 auto; padding: 18px 6vw 18px 6vw; }
        }
        @media (max-width: 900px) {
            .admin-add-form { flex-direction: column; gap: 10px; }
            .admin-add-form input, .admin-add-form textarea { font-size: 1rem; }
            .admin-catalog-btns { flex-direction: column; gap: 10px; }
        }
        input[type="number"] {
            background: #fff;
            color: #444;
            border: 1.5px solid #bbb;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 1rem;
            width: 100%;
            transition: border .2s, background .2s, color .2s;
        }
        input[type="number"]::placeholder {
            color: #888;
            opacity: 1;
        }
        input[type="number"]:focus {
            border-color: #ff7a1a;
            outline: none;
            background: #fff;
            color: #222;
        }
        input[type="file"] {
            background: #fff;
            color: #444;
            border: 1.5px solid #bbb;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 1rem;
            width: 100%;
            height: 42px;
            box-sizing: border-box;
            transition: border .2s, background .2s, color .2s;
        }
        input[type="file"]:focus {
            border-color: #ff7a1a;
            outline: none;
            background: #fff;
            color: #222;
        }
        .admin-table, .admin-table th, .admin-table td, table, th, td {
            background: #fff !important;
            color: #222 !important;
        }
        .admin-table th {
            font-weight: 700;
            color: #ff7a1a !important;
        }
        .admin-table th.name-col, .admin-table td.name-col {
            min-width: 180px;
        }
        .admin-table th.desc-col, .admin-table td.desc-col {
            min-width: 260px;
        }
        .admin-table textarea {
            min-width: 220px;
            max-width: 100%;
        }
        @media (max-width: 900px) {
            .admin-table th.name-col, .admin-table td.name-col,
            .admin-table th.desc-col, .admin-table td.desc-col,
            .admin-table textarea {
                min-width: 100px;
                width: 100%;
            }
        }
        .desc-link {
            display: inline-block;
            color: #ff7a1a;
            background: #fff;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-size: 1rem;
            border: 1.5px solid #bbb;
            transition: background 0.18s, color 0.18s, border 0.18s;
            min-width: 120px;
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .desc-link:hover {
            background: #ff7a1a;
            color: #fff;
            border-color: #ff7a1a;
        }
        .editable-link {
            display: inline-block;
            color: #ff7a1a;
            background: #fff;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-size: 1rem;
            border: 1.5px solid #bbb;
            transition: background 0.18s, color 0.18s, border 0.18s;
            min-width: 80px;
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .editable-link:hover {
            background: #ff7a1a;
            color: #fff;
            border-color: #ff7a1a;
        }
        .desc-modal-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .desc-modal {
            background: #fff;
            color: #222;
            border-radius: 14px;
            padding: 32px 24px 24px 24px;
            min-width: 320px;
            max-width: 98vw;
            box-shadow: 0 4px 32px #0003;
            position: relative;
        }
        .desc-modal textarea {
            width: 100%;
            min-height: 90px;
            border-radius: 8px;
            border: 1.5px solid #bbb;
            padding: 10px;
            font-size: 1rem;
            color: #222;
            background: #fff;
            margin-bottom: 18px;
        }
        .desc-modal .desc-modal-close {
            position: absolute;
            top: 10px; right: 16px;
            font-size: 1.5rem;
            color: #888;
            background: none;
            border: none;
            cursor: pointer;
        }
        .desc-modal .btn {
            min-width: 120px;
        }
        #desc-modal-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }
        .admin-table td, .admin-catalog-btns {
            vertical-align: middle !important;
        }
        .admin-table img,
        .admin-catalog-table img {
            max-width: 120px;
            max-height: 120px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px #0001;
        }
        .preview-img {
            max-width: 120px;
            max-height: 120px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px #0001;
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
                <a href="#" class="nav-link" id="contacts-link">Контакты</a>
                <a href="forum.php" class="nav-link">Форум</a>
            </nav>
            <div class="header-right">
                <div class="currency-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <select name="currency" onchange="this.form.submit()">
                            <option value="KZT" selected>KZT</option>
                            <option value="RUB">RUB</option>
                            <option value="USD">USD</option>
                        </select>
                    </form>
                </div>
                <div class="lang-switcher">
                    <form method="get" style="margin:0;padding:0;display:inline;">
                        <select name="lang" onchange="this.form.submit()">
                            <option value="ru" selected>RU</option>
                            <option value="kz">KZ</option>
                            <option value="en">EN</option>
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
                <a href="index.php" class="mobile-nav-link">Магазин</a>
                <a href="#" class="mobile-nav-link" onclick="document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'}); toggleMobileMenu();">Контакты</a>
                <a href="forum.php" class="mobile-nav-link">Форум</a>
            </nav>
        </div>
    </header>
    <main class="rusefi-main">
    <div class="admin-toggle-group mb-4">
        <a href="admin.php?tab=orders" class="admin-toggle-btn<?php if ($tab === 'orders') echo ' active'; ?>">Заявки</a>
        <a href="admin.php?tab=catalog" class="admin-toggle-btn<?php if ($tab === 'catalog') echo ' active'; ?>">Каталог</a>
    </div>
    <?php if ($tab === 'orders'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Заявки</h1>
        </div>
        <form class="mb-3" method="get" style="max-width: 700px;">
            <input type="hidden" name="tab" value="orders">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Поиск по имени, телефону или товару" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" for="date_from">Дата от</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0" for="date_to">Дата до</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100" type="submit">Найти</button>
                    <?php if ($search !== '' || $date_from !== '' || $date_to !== ''): ?>
                        <a href="admin.php?tab=orders" class="btn btn-outline-secondary w-100">Сбросить</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <?php if ($filtered_orders): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Дата</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Товары</th>
                    <th>Сумма</th>
                    <th>Комментарий</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (array_reverse($filtered_orders, true) as $i => $order): 
                    $real_id = array_search($order, $orders, true); ?>
                    <tr>
                        <td><?php echo $real_id + 1; ?></td>
                        <td><?php echo htmlspecialchars($order['date']); ?></td>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>
                            <ul class="mb-0">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li><?php
$name = $item['name'];
if (is_array($name)) $name = $name['ru'];
echo htmlspecialchars($name);
?> x <?php echo $item['qty']; ?> (<?php echo number_format($item['price'], 0, '', ' '); ?> USD)</li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?php echo number_format($order['total'], 0, '', ' '); ?> USD</td>
                        <td>
                            <form method="post" class="d-flex flex-column gap-2">
                                <input type="hidden" name="order_id" value="<?php echo $real_id; ?>">
                                <textarea name="comment_text" class="form-control" rows="2" placeholder="Комментарий администратора..." style="min-width:120px;max-width:200px;resize:vertical"><?php echo htmlspecialchars($order['admin_comment'] ?? ''); ?></textarea>
                                <button type="submit" name="comment" class="btn btn-sm btn-outline-primary">Сохранить</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('Удалить заявку?');">
                                <input type="hidden" name="order_id" value="<?php echo $real_id; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">Заявок пока нет.</div>
        <?php endif; ?>
    <?php elseif ($tab === 'catalog'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Каталог товаров</h1>
        </div>
        <div class="d-flex justify-content-center mb-3">
            <button type="button" class="btn btn-success" id="openAddModal">Добавить товар</button>
        </div>
    <div id="addProductModal" style="display:none;position:fixed;z-index:2000;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
      <div style="background:#232323;color:#fff;border-radius:14px;max-width:520px;width:98vw;padding:32px 24px 24px 24px;box-shadow:0 4px 32px #0003;position:relative;">
        <button type="button" id="closeAddModal" style="position:absolute;top:10px;right:16px;font-size:1.5rem;color:#888;background:none;border:none;cursor:pointer;">&times;</button>
        <form class="admin-add-form" method="post" enctype="multipart/form-data">
          <div style="display:flex;flex-direction:column;gap:10px;">
            <input type="text" class="form-control" name="name_ru" placeholder="Название (рус)" required>
            <input type="text" class="form-control" name="name_en" placeholder="Name (en)">
            <input type="text" class="form-control" name="name_kz" placeholder="Атауы (қаз)" required>
            <input type="number" class="form-control" name="price" placeholder="Цена" min="1" required>
            <input type="file" class="input-file" name="img_files[]" accept="image/*" multiple>
            <input type="text" class="form-control" name="desc_ru" placeholder="Описание (рус)">
            <input type="text" class="form-control" name="desc_en" placeholder="Description (en)">
            <input type="text" class="form-control" name="desc_kz" placeholder="Сипаттамасы (қаз)">
            <div style="margin-bottom: 12px;">
                <label style="color: #fff; font-weight: 500; margin-bottom: 6px; display: block;">Опции модификации</label>
                <textarea class="form-control" name="options" placeholder='Пример: [{"name": "Цвет", "values": ["Красный", "Синий"]}, {"name": "Размер", "values": ["S", "M", "L"]}]' style="min-height: 150px; font-family: 'Courier New', monospace; font-size: 0.9rem; background: #1a1a1a; border: 2px solid #555; border-radius: 12px; color: #fff; padding: 16px; resize: vertical; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: border-color 0.3s, box-shadow 0.3s;"></textarea>
                <small style="color: #ccc; font-size: 0.8rem;">Введите JSON массив с опциями товара</small>
            </div>
          </div>
          <button type="submit" name="add_product" class="btn btn-success w-100 mt-3">Добавить</button>
        </form>
      </div>
    </div>
    <div id="inputModal" style="display:none;position:fixed;z-index:3000;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
      <div style="background:#232323;color:#fff;border-radius:14px;max-width:420px;width:98vw;padding:32px 24px 24px 24px;box-shadow:0 4px 32px #0003;position:relative;">
        <button type="button" id="closeInputModal" style="position:absolute;top:10px;right:16px;font-size:1.5rem;color:#888;background:none;border:none;cursor:pointer;">&times;</button>
        <form id="inputModalForm">
          <label id="inputModalLabel" style="font-weight:600;margin-bottom:8px;display:block;"></label>
          <textarea id="inputModalTextarea" class="form-control" style="min-height:90px;"></textarea>
          <button type="submit" class="btn btn-success w-100 mt-3">Сохранить</button>
        </form>
      </div>
    </div>
    <style>
    @media (min-width: 700px) {
      .admin-add-form:not(#addProductModal .admin-add-form) { display: none !important; }
    }
    </style>
        <form class="admin-add-form mb-4" method="post" enctype="multipart/form-data">
            <div class="row g-2 align-items-start" style="display:flex;flex-wrap:wrap;gap:0;">
                <div style="display:flex;flex-direction:column;min-width:180px;flex:1;">
                    <input type="text" class="form-control mb-1" name="name_ru" placeholder="Название (рус)" required>
                    <input type="text" class="form-control" name="name_kz" placeholder="Атауы (қаз)" required>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:110px;max-width:120px;margin:0 12px;">
                    <input type="number" class="form-control" name="price" placeholder="Цена" min="1" required>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:140px;max-width:160px;margin:0 12px;">
                    <input type="file" class="input-file" name="img_file" accept="image/*" id="add-img-file">
                </div>
                <div style="display:flex;flex-direction:column;min-width:220px;flex:2;">
                    <input type="text" class="form-control mb-1" name="desc_ru" placeholder="Описание (рус)">
                    <input type="text" class="form-control" name="desc_kz" placeholder="Сипаттамасы (қаз)">
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button type="submit" name="add_product" class="btn btn-success px-5 py-2">Добавить</button>
            </div>
        </form>
        <script>
        document.getElementById('add-img-file').addEventListener('change', function(e) {
            const fileName = this.files[0] ? this.files[0].name : 'фото не выбрано';
            // document.getElementById('add-file-name').textContent = fileName; // Удалено
        });
        </script>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Фото</th>
                    <th class="name-col">Название</th>
                    <th><?php echo sort_link('Цена', 'price', $tab, $sort, $order); ?></th>
                    <th>Картинка</th>
                    <th class="desc-col">Описание</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $i => $p): ?>
                    <tr>
                        <form method="post" enctype="multipart/form-data" class="align-middle">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <td><?php echo $i + 1; ?></td>
                            <td><?php if (!empty($p['images']) && is_array($p['images'])): ?><img src="<?php echo htmlspecialchars($p['images'][0]); ?>" class="preview-img" alt="img"><?php endif; ?></td>
                            <td class="name-col">
                                <?php $curLang = $_COOKIE['lang'] ?? 'ru'; ?>
                                <span class="editable-link" data-id="<?php echo $p['id']; ?>" data-type="name_ru" data-value="<?php echo htmlspecialchars(is_array($p['name'])?$p['name']['ru']:$p['name']); ?>">RU: <?php echo htmlspecialchars(is_array($p['name'])?$p['name']['ru']:$p['name']); ?></span>
                                <span class="editable-link" data-id="<?php echo $p['id']; ?>" data-type="name_en" data-value="<?php echo htmlspecialchars(is_array($p['name'])?$p['name']['en']:''); ?>">EN: <?php echo htmlspecialchars(is_array($p['name'])?$p['name']['en']:''); ?></span>
                                <span class="editable-link" data-id="<?php echo $p['id']; ?>" data-type="name_kz" data-value="<?php echo htmlspecialchars(is_array($p['name'])?$p['name']['kz']:''); ?>">KZ: <?php echo htmlspecialchars(is_array($p['name'])?$p['name']['kz']:''); ?></span>
                            </td>
                            <td>
                                <span class="editable-link" data-id="<?php echo $p['id']; ?>" data-type="price" data-value="<?php echo htmlspecialchars($p['price'], ENT_QUOTES); ?>">
                                    <?php echo number_format($p['price'], 0, '', ' '); ?>
                                </span>
                            </td>
                            <td>
                                <input type="file" name="img_file" class="form-control mb-1" accept="image/*">
                                <!-- убираю кастомную кнопку и подпись, оставляю только input type='file' -->
                            </td>
                            <td class="desc-col">
                                <span class="desc-link" data-id="<?php echo $p['id']; ?>" data-type="desc_ru" data-desc="<?php echo htmlspecialchars(is_array($p['description'])?$p['description']['ru']:$p['description']); ?>">RU: <?php echo htmlspecialchars(is_array($p['description'])?$p['description']['ru']:$p['description']); ?></span>
                                <span class="desc-link" data-id="<?php echo $p['id']; ?>" data-type="desc_en" data-desc="<?php echo htmlspecialchars(is_array($p['description'])?$p['description']['en']:''); ?>">EN: <?php echo htmlspecialchars(is_array($p['description'])?$p['description']['en']:''); ?></span>
                                <span class="desc-link" data-id="<?php echo $p['id']; ?>" data-type="desc_kz" data-desc="<?php echo htmlspecialchars(is_array($p['description'])?$p['description']['kz']:''); ?>">KZ: <?php echo htmlspecialchars(is_array($p['description'])?$p['description']['kz']:''); ?></span>
                            </td>
                            <td>
                                <div class="admin-catalog-btns">
                                    <button type="submit" name="edit_product" class="btn btn-sm btn-outline-primary">Сохранить</button>
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger" onclick="return confirm('Удалить товар?');">Удалить</button>
                                </div>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</div>
    <div id="desc-modal-bg" style="display:none;"></div>
    <script>
    document.addEventListener('click', function(e) {
        // Описание RU/EN/KZ
        if (e.target.classList.contains('desc-link')) {
            const id = e.target.getAttribute('data-id');
            const type = e.target.getAttribute('data-type');
            const current = e.target.getAttribute('data-desc') || '';
            const modalBg = document.getElementById('desc-modal-bg');
            let label = '';
            if (type === 'desc_kz') label = 'Сипаттамасы (қаз)';
            else if (type === 'desc_en') label = 'Description (en)';
            else label = 'Описание (рус)';
            modalBg.innerHTML = `<div class='desc-modal'>
                <button class='desc-modal-close'>&times;</button>
                <form class='desc-modal-form'>
                    <textarea name='${type}' placeholder='${label}'>${current.replace(/</g, '<').replace(/>/g, '>')}</textarea>
                    <input type='hidden' name='product_id' value='${id}'>
                    <button type='submit' class='btn btn-primary'>Сохранить</button>
                </form>
            </div>`;
            modalBg.style.display = 'flex';
            modalBg.querySelector('.desc-modal-close').onclick = function() {
                modalBg.style.display = 'none';
            };
            modalBg.querySelector('.desc-modal-form').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                }).then(r => r.text()).then(() => { location.reload(); });
            };
        }
        // Название RU/EN/KZ
        if (e.target.classList.contains('editable-link')) {
            const id = e.target.getAttribute('data-id');
            const type = e.target.getAttribute('data-type');
            const current = e.target.getAttribute('data-value') || '';
            const modalBg = document.getElementById('desc-modal-bg');
            let label = '';
            if (type === 'name_kz') label = 'Атауы (қаз)';
            else if (type === 'name_en') label = 'Name (en)';
            else label = 'Название (рус)';
            modalBg.innerHTML = `<div class='desc-modal'>
                <button class='desc-modal-close'>&times;</button>
                <form class='desc-modal-form'>
                    <input type='text' name='${type}' placeholder='${label}' value="${current.replace(/"/g, '"').replace(/</g, '<').replace(/>/g, '>')}">
                    <input type='hidden' name='product_id' value='${id}'>
                    <button type='submit' class='btn btn-primary'>Сохранить</button>
                </form>
            </div>`;
            modalBg.style.display = 'flex';
            modalBg.querySelector('.desc-modal-close').onclick = function() {
                modalBg.style.display = 'none';
            };
            modalBg.querySelector('.desc-modal-form').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                }).then(r => r.text()).then(() => { location.reload(); });
            };
        }
    });
    </script>
    <script>
    document.getElementById('openAddModal').onclick = function() {
      document.getElementById('addProductModal').style.display = 'flex';
    };
    document.getElementById('closeAddModal').onclick = function() {
      document.getElementById('addProductModal').style.display = 'none';
    };
    window.addEventListener('click', function(e) {
      const modal = document.getElementById('addProductModal');
      if (e.target === modal) modal.style.display = 'none';
    });
    </script>
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');
    }

    function updateCartUI() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        let count = 0;
        for (let id in cart) count += cart[id]?.quantity || 0;
        document.getElementById('cart-count').textContent = count;
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCartUI();
        document.getElementById('contacts-link').onclick = function(e) {
            e.preventDefault();
            document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
        };
    });

    window.addEventListener('storage', function() { updateCartUI(); });

    (function(){
      // Универсальная модалка для ввода текста
      const addModal = document.getElementById('addProductModal');
      const inputModal = document.getElementById('inputModal');
      let currentInput = null;
      let currentLabel = '';
      // Открытие модалки по клику на input/textarea
      addModal.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(function(inp){
        inp.addEventListener('focus', function(e){
          e.preventDefault();
          currentInput = this;
          currentLabel = this.getAttribute('placeholder') || '';
          document.getElementById('inputModalLabel').textContent = currentLabel;
          document.getElementById('inputModalTextarea').value = this.value;
          inputModal.style.display = 'flex';
          setTimeout(()=>document.getElementById('inputModalTextarea').focus(), 100);
        });
        inp.addEventListener('click', function(e){
          this.blur(); // чтобы не было двойного фокуса
          this.focus();
        });
      });
      // Сохранение значения
      document.getElementById('inputModalForm').onsubmit = function(e){
        e.preventDefault();
        if(currentInput) currentInput.value = document.getElementById('inputModalTextarea').value;
        inputModal.style.display = 'none';
      };
      document.getElementById('closeInputModal').onclick = function(){
        inputModal.style.display = 'none';
      };
      window.addEventListener('click', function(e){
        if(e.target === inputModal) inputModal.style.display = 'none';
      });
    })();
    </script>
</body>
</html>
