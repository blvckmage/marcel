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
        <title>Вход в админ-панель</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
        <style>
            body {
                background: #f7f7f9;
                color: #222;
                font-family: 'Inter', Arial, sans-serif;
                min-height: 100vh;
            }
            .admin-login-box {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 8px #eee;
                max-width: 400px;
                margin: 80px auto 0 auto;
                padding: 36px 32px 28px 32px;
                border: 1px solid #e5e5e5;
            }
            .admin-login-box h2 {
                color: #222;
                font-weight: 800;
                margin-bottom: 24px;
                text-align: center;
            }
            .admin-login-box label {
                color: #222;
                font-weight: 500;
                margin-bottom: 6px;
            }
            .admin-login-box input[type="password"] {
                background: #fafafa;
                color: #222;
                border: 1.5px solid #e5e5e5;
                border-radius: 8px;
                padding: 10px 16px;
                font-size: 1.1rem;
                margin-bottom: 18px;
                width: 100%;
                transition: border .2s, background .2s;
            }
            .admin-login-box input[type="password"]:focus {
                border-color: #ff6a00;
                outline: none;
                background: #fff;
            }
            .admin-login-box .btn-primary {
                background: #ff6a00 !important;
                color: #fff !important;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1.1rem;
                padding: 10px 32px;
                margin-right: 8px;
                transition: background .2s;
            }
            .admin-login-box .btn-primary:hover {
                background: #e55a00 !important;
                color: #fff !important;
            }
            .admin-login-box .btn-secondary {
                background: #fff !important;
                color: #ff6a00 !important;
                border: 2px solid #ff6a00;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1.1rem;
                padding: 10px 32px;
                transition: background .2s, color .2s;
            }
            .admin-login-box .btn-secondary:hover {
                background: #fafafa !important;
                color: #ff6a00 !important;
            }
            .admin-login-box .alert {
                border-radius: 10px;
                font-size: 1rem;
                margin-bottom: 18px;
            }
            ::selection { background: #ff6a00; color: #fff; }
            footer { background: #f7f7f9; color: #888; text-align: center; padding: 24px 0 12px 0; font-size: 1rem; border-top: 1px solid #e5e5e5; position: fixed; left: 0; right: 0; bottom: 0; }
            footer a { color: #ff6a00; text-decoration: none; }
        </style>
    </head>
    <body>
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
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Войти</button>
                <a href="index.php" class="btn btn-secondary">Назад</a>
            </div>
        </form>
    </div>
    <footer>
      &copy; <?php echo date('Y'); ?> 3D Print &mdash; <a href="https://www.shop.rusefi.com">rusefi.com</a>
    </footer>
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
        'name' => trim($_POST['name'] ?? ''),
        'price' => (int)($_POST['price'] ?? 0),
        'img' => '',
        'description' => trim($_POST['description'] ?? ''),
    ];
    // Обработка загрузки файла
    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $fname = 'images/' . uniqid('img_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['img_file']['tmp_name'], $fname)) {
                $new['img'] = $fname;
            }
        }
    }
    // Если не загружали файл — используем текстовое поле
    if (!$new['img']) {
        $new['img'] = trim($_POST['img'] ?? '');
    }
    if ($new['name'] && $new['price'] > 0 && $new['img']) {
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
// Редактирование товара с загрузкой файла и удалением старого
if (isset($_POST['edit_product']) && isset($_POST['product_id'])) {
    $id = (int)$_POST['product_id'];
    foreach ($products as &$p) {
        if ($p['id'] === $id) {
            $p['name'] = trim($_POST['name'] ?? $p['name']);
            $p['price'] = (int)($_POST['price'] ?? $p['price']);
            $p['description'] = trim($_POST['description'] ?? $p['description']);
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
    <title>Админ-панель — 3D Маркетплейс</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f7f7f9;
            color: #222;
            font-family: 'Inter', Arial, sans-serif;
        }
        .navbar {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            box-shadow: 0 2px 8px #eee;
        }
        .navbar .navbar-brand {
            color: #ff6a00;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -1px;
        }
        .navbar .nav-link {
            color: #222;
            font-weight: 500;
            margin: 0 12px;
            border-bottom: 2px solid transparent;
            transition: border .2s, color .2s;
        }
        .navbar .nav-link:hover {
            color: #ff6a00;
            border-bottom: 2px solid #ff6a00;
        }
        .navbar .nav-link.active, .navbar .nav-link.catalog {
            color: #ff6a00 !important;
            font-weight: 800;
        }
        .wb-header {
            background: #fff;
            color: #222;
            padding: 18px 0 14px 0;
            border-radius: 0 0 18px 18px;
            margin-bottom: 32px;
            box-shadow: 0 2px 8px #eee;
            position: relative;
            overflow: hidden;
        }
        .wb-header .container { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; }
        .wb-logo { font-size: 2rem; font-weight: 800; letter-spacing: -2px; color: #ff6a00; text-shadow: none; display: flex; align-items: center; gap: 10px; }
        .preview-img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .admin-login-box, .admin-form, .admin-block, .admin-table, .admin-card, .admin-panel, .admin-section, .admin-content, .admin-modal {
            background: #fff;
            color: #222;
            border-radius: 12px;
            box-shadow: 0 2px 8px #eee;
            border: 1px solid #e5e5e5;
        }
        .admin-login-box {
            max-width: 400px;
            margin: 80px auto 0 auto;
            padding: 36px 32px 28px 32px;
        }
        .admin-login-box h2 {
            color: #222;
            font-weight: 800;
            margin-bottom: 24px;
            text-align: center;
        }
        .admin-login-box label {
            color: #222;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .admin-login-box input[type="password"] {
            background: #fafafa;
            color: #222;
            border: 1.5px solid #e5e5e5;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 1.1rem;
            margin-bottom: 18px;
            width: 100%;
            transition: border .2s, background .2s;
        }
        .admin-login-box input[type="password"]:focus {
            border-color: #ff6a00;
            outline: none;
            background: #fff;
        }
        .admin-login-box .btn, .admin-form .btn, .admin-block .btn, .admin-table .btn, .admin-card .btn, .admin-panel .btn, .admin-section .btn, .admin-content .btn, .admin-modal .btn {
            background: #ff6a00 !important;
            color: #fff !important;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 10px 32px;
            margin-right: 8px;
        }
        .admin-login-box .btn:hover, .admin-form .btn:hover, .admin-block .btn:hover, .admin-table .btn:hover, .admin-card .btn:hover, .admin-panel .btn:hover, .admin-section .btn:hover, .admin-content .btn:hover, .admin-modal .btn:hover {
            background: #e55a00 !important;
            color: #fff !important;
        }
        .admin-login-box .btn-secondary {
            background: #fff !important;
            color: #ff6a00 !important;
            border: 2px solid #ff6a00;
        }
        .admin-login-box .btn-secondary:hover {
            background: #fafafa !important;
            color: #ff6a00 !important;
        }
        .admin-login-box .alert {
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 18px;
        }
        ::selection { background: #ff6a00; color: #fff; }
        footer { background: #f7f7f9; color: #888; text-align: center; padding: 24px 0 12px 0; font-size: 1rem; border-top: 1px solid #e5e5e5; }
        footer a { color: #ff6a00; text-decoration: none; }
        .admin-toggle-group {
            display: flex;
            justify-content: center;
            margin: 0 auto 24px auto;
            background: #fff;
            border-radius: 8px;
            border: 1.5px solid #ff6a00;
            box-shadow: 0 1px 4px #eee;
            width: fit-content;
            overflow: hidden;
        }
        .admin-toggle-btn {
            font-weight: 600;
            font-size: 1rem;
            padding: 7px 32px;
            border: none;
            background: transparent;
            color: #ff6a00;
            transition: background .18s, color .18s;
            outline: none;
            cursor: pointer;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-toggle-btn.active {
            background: #ff6a00;
            color: #fff;
            box-shadow: 0 1px 4px #ff6a0033 inset;
        }
        .admin-toggle-btn:not(:last-child) {
            border-right: 1.5px solid #ffb366;
        }
        .admin-add-form {
            background: #fffbe7;
            border: 1.5px solid #ff6a00;
            border-radius: 10px;
            padding: 18px 24px 10px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px #ffe0b2;
        }
        .input-file {
            display: none;
        }
        .input-file-label {
            display: inline-block;
            padding: 8px 22px;
            background: #ff6a00;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background .18s;
            margin-bottom: 0;
        }
        .input-file-label:hover {
            background: #e55a00;
        }
        .input-file-name {
            margin-left: 16px;
            font-size: 1rem;
            color: #222;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid" style="max-width:1200px;margin:0 auto;">
    <a class="navbar-brand" href="index.php">3D Print</a>
    <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="gap:18px;">
      <li class="nav-item"><a class="nav-link catalog active" href="index.php">Каталог</a></li>
    </ul>
  </div>
</nav>
<div class="container mt-4">
    <div class="admin-toggle-group mb-4">
        <a href="admin.php?tab=orders" class="admin-toggle-btn<?php if ($tab === 'orders') echo ' active'; ?>">Заявки</a>
        <a href="admin.php?tab=catalog" class="admin-toggle-btn<?php if ($tab === 'catalog') echo ' active'; ?>">Каталог</a>
    </div>
    <?php if ($tab === 'orders'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Заявки</h1>
            <a href="?logout=1" class="btn btn-outline-danger">Выйти</a>
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
                                    <li><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?> (<?php echo number_format($item['price'], 0, '', ' '); ?> KZT)</li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?php echo number_format($order['total'], 0, '', ' '); ?> KZT</td>
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
            <a href="?logout=1" class="btn btn-outline-danger">Выйти</a>
        </div>
        <form class="admin-add-form mb-4" method="post" enctype="multipart/form-data">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="name" placeholder="Название" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="price" placeholder="Цена" min="1" required>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <input type="file" class="input-file" name="img_file" accept="image/*" id="add-img-file">
                    <label for="add-img-file" class="input-file-label mb-0">Выбрать фото</label>
                    <span class="input-file-name" id="add-img-file-name">фото не выбрано</span>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="description" placeholder="Описание">
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button type="submit" name="add_product" class="btn btn-success px-5 py-2">Добавить</button>
            </div>
        </form>
        <script>
        document.getElementById('add-img-file').addEventListener('change', function(e) {
            const fileName = this.files[0] ? this.files[0].name : 'фото не выбрано';
            document.getElementById('add-img-file-name').textContent = fileName;
        });
        </script>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Фото</th>
                <th><?php echo sort_link('Название', 'name', $tab, $sort, $order); ?></th>
                <th><?php echo sort_link('Цена', 'price', $tab, $sort, $order); ?></th>
                <th>Картинка</th>
                <th>Описание</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $i => $p): ?>
                <tr>
                    <form method="post" enctype="multipart/form-data" class="align-middle">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <td><?php echo $i + 1; ?></td>
                        <td><?php if ($p['img']): ?><img src="<?php echo htmlspecialchars($p['img']); ?>" class="preview-img" alt="img"><?php endif; ?></td>
                        <td><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($p['name']); ?>" required></td>
                        <td><input type="number" name="price" class="form-control" value="<?php echo number_format($p['price'], 0, '', ' '); ?>" min="1" required></td>
                        <td>
                            <input type="file" name="img_file" class="form-control mb-1" accept="image/*">
                            <input type="text" name="img" class="form-control mb-1" value="<?php echo htmlspecialchars($p['img']); ?>" placeholder="URL или путь (если не загружаете файл)">
                        </td>
                        <td><textarea name="description" class="form-control" rows="2" placeholder="Описание"><?php echo htmlspecialchars($p['description'] ?? ''); ?></textarea></td>
                        <td class="d-flex gap-2">
                            <button type="submit" name="edit_product" class="btn btn-sm btn-outline-primary">Сохранить</button>
                            <button type="submit" name="delete_product" class="btn btn-sm btn-danger" onclick="return confirm('Удалить товар?');">Удалить</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<footer>
  <div class="footer-contacts">
    <div>+7 (777) 123-45-67 &nbsp; | &nbsp; +7 (701) 987-65-43</div>
    <div class="footer-socials">
      <a href="https://wa.me/77011234567" target="_blank" title="WhatsApp"><svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.36 5.07L2 22l5.09-1.33A9.96 9.96 0 0 0 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2Zm0 18c-1.61 0-3.16-.39-4.5-1.13l-.32-.18-3.02.79.81-2.95-.21-.34A7.96 7.96 0 0 1 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8Zm4.29-5.18c-.23-.12-1.36-.67-1.57-.75-.21-.08-.36-.12-.51.12-.15.23-.58.75-.71.9-.13.15-.26.17-.49.06-.23-.12-.97-.36-1.85-1.13-.68-.6-1.14-1.34-1.28-1.57-.13-.23-.01-.35.1-.47.1-.1.23-.26.34-.39.12-.13.15-.23.23-.38.08-.15.04-.28-.02-.4-.06-.12-.51-1.23-.7-1.68-.18-.44-.37-.38-.51-.39-.13-.01-.28-.01-.43-.01-.15 0-.4.06-.61.28-.21.22-.8.78-.8 1.9 0 1.12.82 2.2.94 2.35.12.15 1.61 2.46 3.91 3.35.55.19.98.3 1.31.38.55.14 1.05.12 1.44.07.44-.07 1.36-.56 1.55-1.1.19-.54.19-1 .13-1.1-.06-.1-.21-.16-.44-.28Z" fill="#ff6a00"/></svg></a>
      <a href="https://t.me/yourtelegram" target="_blank" title="Telegram"><svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M9.04 16.62c-.31 0-.26-.12-.37-.42l-1.1-3.62 8.7-5.47c.38-.23.58-.1.47.33l-1.48 6.97c-.1.43-.36.54-.73.34l-2.04-1.5-1 .97c-.11.11-.2.2-.41.2Zm-1.3-4.41 1.01 3.1.26-.84c.08-.25.16-.34.34-.48l2.7-2.47c.15-.13.29-.4-.06-.4l-3.99.09c-.34 0-.41.16-.26.4Zm2.26 1.41 1.62 1.19c.16.12.32.18.37-.07l1.33-6.25c.05-.25-.09-.36-.32-.25l-5.7 3.59c-.23.15-.22.24.05.29l2.65.5c.27.05.36.18.3.41Zm1.99-11.62C6.48 2 2 6.48 2 12c0 5.52 4.48 10 10 10s10-4.48 10-10S17.52 2 12 2Z" fill="#ff6a00"/></svg></a>
      <a href="mailto:info@example.com" target="_blank" title="Email"><svg width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 2v.01L12 13 4 6.01V6h16ZM4 20v-9.99l7.29 6.41c.38.34.95.34 1.33 0L20 10.01V20H4Z" fill="#ff6a00"/></svg></a>
    </div>
  </div>
</footer>
</body>
</html> 