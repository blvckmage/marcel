<?php
session_start();
$products = [];
$products_file = __DIR__ . '/products.json';
if (file_exists($products_file)) {
    $products_arr = json_decode(file_get_contents($products_file), true);
    foreach ($products_arr as $item) {
        $products[$item['id']] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>3D Маркетплейс</title>
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
        .wb-search { width: 340px; max-width: 100%; border-radius: 8px; border: 1.5px solid #e5e5e5; padding: 8px 16px; font-size: 1.1rem; background: #fff; color: #222; }
        .wb-search::placeholder { color: #888; }
        .wb-cart-btn { background: #fff; color: #ff6a00; border-radius: 50px; font-weight: 600; box-shadow: 0 2px 8px #eee; border: 2px solid #ff6a00; display: flex; align-items: center; gap: 8px; padding: 8px 22px; font-size: 1.1rem; transition: background .2s, color .2s, border .2s; }
        .wb-cart-btn:hover { background: #ff6a00; color: #fff; border-color: #ff6a00; }
        .wb-cart-btn svg { width: 22px; height: 22px; }
        .wb-filters { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .wb-chip, .wb-view-toggle, .btn, .btn-primary, .btn-outline-primary, .add-to-cart {
            background: #ff6a00 !important;
            color: #fff !important;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            padding: 10px 24px;
            transition: background .2s;
        }
        .wb-chip.active, .wb-chip:hover, .wb-view-toggle.active, .wb-view-toggle:hover, .btn:hover, .btn-primary:hover, .btn-outline-primary:hover, .add-to-cart:hover {
            background: #e55a00 !important;
            color: #fff !important;
        }
        .form-select {
            border-radius: 8px;
            border: 1.5px solid #e5e5e5;
            background: #fff;
            color: #222;
        }
        .wb-products { display: flex; flex-wrap: wrap; gap: 24px; }
        .wb-card, .product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px #eee;
            border: 1px solid #e5e5e5;
            padding: 0;
            transition: box-shadow .2s;
            width: 270px;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .wb-card:hover, .product-card:hover { box-shadow: 0 8px 24px #e5e5e5; }
        .wb-card-img, .product-card img { border-radius: 12px 12px 0 0; border-bottom: 1px solid #eee; background: #fafafa; width: 100%; height: 210px; object-fit: cover; }
        .wb-card-body { padding: 18px 18px 12px 18px; flex: 1 1 auto; display: flex; flex-direction: column; }
        .wb-card-title { font-weight: 700; font-size: 1.1rem; color: #222; }
        .wb-card-price { color: #ff6a00; font-size: 1.15rem; font-weight: 700; }
        .wb-card-desc { color: #333; font-size: 1rem; }
        .wb-card-btns { margin-top: auto; }
        .wb-list { display: flex; flex-direction: column; gap: 18px; }
        .wb-list-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #eee; display: flex; align-items: center; padding: 18px 24px; gap: 24px; width: 100%; border: 1px solid #e5e5e5; }
        .wb-list-img { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; background: #fafafa; }
        .wb-list-title { font-weight: 700; font-size: 1.1rem; color: #222; }
        .wb-list-price { color: #ff6a00; font-size: 1.15rem; font-weight: 700; }
        .wb-list-desc { color: #333; font-size: 1rem; }
        .wb-list-btns { margin-left: 24px; }
        @media (max-width: 900px) { .wb-products { justify-content: center; } }
        @media (max-width: 600px) {
            .wb-header .container { flex-direction: column; gap: 12px; }
            .wb-products { gap: 12px; }
            .wb-card { width: 98vw; max-width: 340px; }
            .wb-card-img { height: 160px; }
            .wb-list-card { flex-direction: column; align-items: flex-start; padding: 12px 8px; gap: 10px; }
            .wb-list-btns { margin-left: 0; margin-top: 10px; }
        }
        ::selection {
          background: #ff6a00;
          color: #fff;
        }
        footer {
          background: #f7f7f9;
          color: #888;
          text-align: center;
          padding: 24px 0 12px 0;
          font-size: 1rem;
          border-top: 1px solid #e5e5e5;
        }
        footer a {
          color: #ff6a00;
          text-decoration: none;
          margin: 0 8px;
          font-weight: 600;
          font-size: 1.1em;
        }
        footer .footer-contacts {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 8px;
        }
        footer .footer-socials {
          display: flex;
          gap: 18px;
          justify-content: center;
          margin-top: 6px;
        }
        footer .footer-socials a {
          color: #ff6a00;
          font-size: 1.5em;
          transition: color .18s;
        }
        footer .footer-socials a:hover {
          color: #e55a00;
        }
        .view-toggle-group {
            display: inline-flex;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 4px #eee;
            border: 1.5px solid #ff6a00;
            height: 38px;
        }
        .view-toggle-btn {
            font-weight: 600;
            font-size: 1rem;
            padding: 5px 18px;
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
        .view-toggle-btn.active {
            background: #ff6a00;
            color: #fff;
            box-shadow: 0 1px 4px #ff6a0033 inset;
        }
        .view-toggle-btn:not(:last-child) {
            border-right: 1.5px solid #ffb366;
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
    <a href="cart.php" class="wb-cart-btn position-relative ms-auto" id="cart-btn" style="margin-left:auto;">
        <!-- Новая иконка корзины -->
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6a00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span id="cart-count" class="badge bg-light text-dark ms-1">0</span>
    </a>
  </div>
</nav>
<div class="wb-header" style="padding:0;margin-bottom:32px;box-shadow:none;background:transparent;">
    <!-- убираем wb-logo и wb-header внутренний контейнер -->
</div>
<div class="container">
    <div class="wb-filters mb-4" style="gap:12px;flex-wrap:wrap;align-items:center;">
        <div class="view-toggle-group me-2">
            <button class="view-toggle-btn" id="viewGrid">Значки</button>
            <button class="view-toggle-btn" id="viewList">Список</button>
        </div>
        <select class="form-select ms-2" id="sortSelect" style="max-width:180px;">
            <option value="">Сортировка</option>
            <option value="name-asc">Название (А-Я)</option>
            <option value="name-desc">Название (Я-А)</option>
            <option value="price-asc">Цена (по возрастанию)</option>
            <option value="price-desc">Цена (по убыванию)</option>
        </select>
        <button type="button" class="btn btn-outline-secondary ms-2" id="resetBtn">Сбросить</button>
        <input type="text" class="wb-search ms-2" id="searchInput" placeholder="Поиск по товарам..." style="width:320px;max-width:100%;">
    </div>
    <div id="product-list"></div>
    <div id="toast" class="toast alert alert-success"></div>
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
<script>
const products = <?php echo json_encode(array_values($products), JSON_UNESCAPED_UNICODE); ?>;
let view = 'grid';
let search = '';
let sort = '';
function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
function setCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }
function updateCartUI() {
    const cart = getCart();
    let count = 0;
    for (let id in cart) count += cart[id];
    document.getElementById('cart-count').textContent = count;
}
function formatPrice(num) { return Number(num).toLocaleString('ru-RU') + ' KZT'; }
function renderProducts() {
    let list = document.getElementById('product-list');
    let val = document.getElementById('searchInput').value.trim().toLowerCase();
    let sortVal = document.getElementById('sortSelect').value;
    let filtered = products.filter(p => p.name.toLowerCase().includes(val));
    if (sortVal) {
        let [field, dir] = sortVal.split('-');
        filtered.sort((a, b) => {
            let va = field === 'name' ? a.name.toLowerCase() : a.price;
            let vb = field === 'name' ? b.name.toLowerCase() : b.price;
            if (va < vb) return dir === 'asc' ? -1 : 1;
            if (va > vb) return dir === 'asc' ? 1 : -1;
            return 0;
        });
    }
    let cart = getCart();
    let html = '';
    if (view === 'grid') {
        html += '<div class="wb-products">';
        for (let p of filtered) {
            let qty = cart[p.id] || 0;
            html += `<div class="wb-card product-card">
                <img src="${p.img}" class="wb-card-img" alt="${p.name}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/1170/1170678.png'">
                <div class="wb-card-body">
                    <div class="wb-card-title">${p.name}</div>
                    <div class="wb-card-price">${formatPrice(p.price)}</div>
                    <div class="wb-card-desc">${p.description}</div>
                    <div class="wb-card-btns mt-2">` +
                        (qty > 0 ?
                        `<div class='d-flex align-items-center gap-2 justify-content-center'>
                            <button class='btn btn-outline-secondary btn-sm minus' data-id='${p.id}'>-</button>
                            <span style='min-width:24px;display:inline-block;text-align:center;font-weight:600;'>${qty}</span>
                            <button class='btn btn-outline-secondary btn-sm plus' data-id='${p.id}'>+</button>
                        </div>`
                        : `<button class="add-to-cart btn btn-primary w-100" data-id="${p.id}">В корзину</button>`)
                    + `</div>
                </div>
            </div>`;
        }
        html += '</div>';
    } else {
        html += '<div class="wb-list">';
        for (let p of filtered) {
            let qty = cart[p.id] || 0;
            html += `<div class="wb-list-card d-flex align-items-center" style="gap:24px;">\
                <img src="${p.img}" class="wb-list-img" alt="${p.name}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/1170/1170678.png'">
                <div class="wb-list-info flex-grow-1">
                    <div class="wb-list-title">${p.name}</div>
                    <div class="wb-list-price">${formatPrice(p.price)}</div>
                    <div class="wb-list-desc">${p.description}</div>
                </div>
                <div class="wb-list-btns ms-auto">` +
                    (qty > 0 ?
                    `<div class='d-flex align-items-center gap-2'>
                        <button class='btn btn-outline-secondary btn-sm minus' data-id='${p.id}'>-</button>
                        <span style='min-width:24px;display:inline-block;text-align:center;font-weight:600;'>${qty}</span>
                        <button class='btn btn-outline-secondary btn-sm plus' data-id='${p.id}'>+</button>
                    </div>`
                    : `<button class="add-to-cart btn btn-primary" data-id="${p.id}">В корзину</button>`)
                + `</div>
            </div>`;
        }
        html += '</div>';
    }
    list.innerHTML = html;
    // Вешаем обработчики на кнопки корзины и счетчика
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const cart = getCart();
            cart[id] = (cart[id] || 0) + 1;
            setCart(cart);
            updateCartUI();
            renderProducts();
            showToast('Товар добавлен в корзину!');
        };
    });
    document.querySelectorAll('.plus').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const cart = getCart();
            cart[id] = (cart[id] || 0) + 1;
            setCart(cart);
            updateCartUI();
            renderProducts();
        };
    });
    document.querySelectorAll('.minus').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const cart = getCart();
            cart[id] = (cart[id] || 0) - 1;
            if (cart[id] <= 0) delete cart[id];
            setCart(cart);
            updateCartUI();
            renderProducts();
        };
    });
    // Подсветка активного вида
    document.getElementById('viewGrid').classList.toggle('active', view==='grid');
    document.getElementById('viewList').classList.toggle('active', view==='list');
}
document.addEventListener('DOMContentLoaded', function() {
    renderProducts();
    updateCartUI();
    document.getElementById('searchInput').addEventListener('input', renderProducts);
    document.getElementById('sortSelect').addEventListener('change', renderProducts);
    document.getElementById('viewGrid').addEventListener('click', function() { view = 'grid'; renderProducts(); });
    document.getElementById('viewList').addEventListener('click', function() { view = 'list'; renderProducts(); });
    document.getElementById('resetBtn').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('sortSelect').value = '';
        renderProducts();
    });
});
window.addEventListener('storage', updateCartUI);
</script>
</body>
</html>