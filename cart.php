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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Корзина — 3D Маркетплейс</title>
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
        .cart-list-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #eee; padding: 18px 24px; margin-bottom: 18px; display: flex; align-items: center; gap: 18px; border: 1px solid #e5e5e5; }
        .cart-list-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; background: #fafafa; }
        .cart-list-info { flex: 1 1 auto; }
        .cart-list-title { font-weight: 700; font-size: 1.1rem; color: #222; margin-bottom: 4px; }
        .cart-list-price { color: #ff6a00; font-size: 1.1rem; font-weight: 700; }
        .cart-list-desc { color: #333; font-size: 1rem; }
        .cart-list-qty { font-weight: 600; font-size: 1.08rem; margin: 0 8px; }
        .cart-list-remove { background: #ff6a00 !important; color: #fff !important; border-radius: 8px; font-weight: 600; padding: 8px 22px; border: none; transition: background .2s; }
        .cart-list-remove:hover { background: #e55a00 !important; color: #fff !important; }
        .cart-summary { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #eee; padding: 18px 24px; font-size: 1.15rem; font-weight: 600; color: #222; margin-bottom: 24px; border: 1px solid #e5e5e5; }
        .cart-summary .cart-total { color: #ff6a00; font-weight: 700; margin-left: 8px; }
        .cart-actions { display: flex; gap: 16px; }
        .cart-order-btn { background: #ff6a00 !important; color: #fff !important; border-radius: 8px; font-weight: 600; border: none; padding: 10px 32px; font-size: 1.1rem; transition: background .2s; }
        .cart-order-btn:hover { background: #e55a00 !important; color: #fff !important; }
        .btn-outline-primary { border-color: #ff6a00; color: #ff6a00; background: #fff; border-radius: 8px; }
        .btn-outline-primary:hover { background: #ff6a00; color: #fff; }
        ::selection { background: #ff6a00; color: #fff; }
        footer { background: #f7f7f9; color: #888; text-align: center; padding: 24px 0 12px 0; font-size: 1rem; border-top: 1px solid #e5e5e5; }
        footer a { color: #ff6a00; text-decoration: none; }
        @media (max-width: 600px) {
          .cart-list-card { flex-direction: column; align-items: flex-start; padding: 12px 8px; gap: 10px; }
          .cart-list-info { width: 100%; }
          .cart-list-remove { width: 100%; margin-top: 8px; }
          .cart-summary { padding: 12px 8px; font-size: 1.05rem; }
          .cart-actions { flex-direction: column; gap: 10px; }
          .navbar .container-fluid { flex-direction: row; align-items: center; justify-content: space-between; }
          .navbar .navbar-brand { font-size: 1.2rem; margin-bottom: 0; }
          .navbar-nav { flex-direction: column; align-items: flex-start; gap: 0; }
          .nav-item { margin-bottom: 0; }
          .nav-link.catalog { font-size: 1.1rem; margin-left: 0; }
          .wb-cart-btn { margin-left: 0 !important; }
          .footer-contacts { font-size: 0.95em; }
          .footer-socials a { font-size: 1.2em; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid" style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
    <div style="display:flex;align-items:center;gap:18px;">
      <a class="navbar-brand" href="index.php">3D Print</a>
      <a class="nav-link catalog active" href="index.php" style="font-weight:800;color:#ff6a00;">Каталог</a>
    </div>
    <a href="cart.php" class="wb-cart-btn position-relative ms-auto" id="cart-btn" style="margin-left:auto;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6a00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span id="cart-count" class="badge bg-light text-dark ms-1">0</span>
    </a>
  </div>
</nav>
<div class="container">
    <a href="index.php" class="btn btn-outline-primary mb-3" style="border-radius:50px;font-weight:600;">← Назад в каталог</a>
    <h2 class="mb-4">Корзина</h2>
    <div id="cart-list"></div>
    <div id="cart-summary"></div>
    <div class="cart-actions" id="cart-actions" style="display:none;">
        <a href="order.php" class="cart-order-btn">Оформить заявку</a>
    </div>
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
function getCart() { return JSON.parse(localStorage.getItem('cart') || '{}'); }
function setCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }
function updateCartUI() {
    const cart = getCart();
    let count = 0;
    for (let id in cart) count += cart[id];
    document.getElementById('cart-count').textContent = count;
}
function formatPrice(num) { return Number(num).toLocaleString('ru-RU') + ' KZT'; }
function renderCart() {
    const cart = getCart();
    let html = '';
    let total = 0;
    let hasItems = false;
    products.forEach(p => {
        const qty = cart[p.id] || 0;
        if (qty > 0) {
            hasItems = true;
            const sum = p.price * qty;
            total += sum;
            html += `<div class='cart-list-card d-flex align-items-center gap-3'>
                <img src='${p.img}' alt='${p.name}'>
                <div class='cart-list-info flex-grow-1'>
                    <div class='cart-list-title'>${p.name}</div>
                    <div class='cart-list-price'>${formatPrice(p.price)}</div>
                    <div class='d-flex align-items-center gap-2 mt-2'>
                        <button class='btn btn-outline-secondary btn-sm minus' data-id='${p.id}'>-</button>
                        <span style='min-width:24px;display:inline-block;text-align:center;font-weight:600;'>${qty}</span>
                        <button class='btn btn-outline-secondary btn-sm plus' data-id='${p.id}'>+</button>
                    </div>
                </div>
                <button class='cart-list-remove' data-id='${p.id}'>Удалить</button>
            </div>`;
        }
    });
    if (hasItems) {
        document.getElementById('cart-summary').innerHTML = `<div class='cart-summary'>Итого: ${formatPrice(total)}</div>`;
        document.getElementById('cart-actions').style.display = '';
    } else {
        html = '<p>Корзина пуста.</p>';
        document.getElementById('cart-summary').innerHTML = '';
        document.getElementById('cart-actions').style.display = 'none';
    }
    document.getElementById('cart-list').innerHTML = html;
    updateCartUI();
    // Вешаем обработчики на + и -
    document.querySelectorAll('.plus').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const cart = getCart();
            cart[id] = (cart[id] || 0) + 1;
            setCart(cart);
            renderCart();
        };
    });
    document.querySelectorAll('.minus').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const cart = getCart();
            cart[id] = (cart[id] || 0) - 1;
            if (cart[id] <= 0) delete cart[id];
            setCart(cart);
            renderCart();
        };
    });
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cart-list-remove')) {
        const id = e.target.getAttribute('data-id');
        const cart = getCart();
        delete cart[id];
        setCart(cart);
        renderCart();
    }
});
document.addEventListener('DOMContentLoaded', function() {
    renderCart();
    updateCartUI();
});
window.addEventListener('storage', function() { renderCart(); updateCartUI(); });
</script>
</body>
</html> 