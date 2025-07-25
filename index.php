<?php
session_start();
// --- Язык ---
if (isset($_GET['lang'])) {
    setcookie('lang', $_GET['lang'], time() + 3600*24*30, '/');
    $_COOKIE['lang'] = $_GET['lang'];
    header('Location: index.php');
    exit;
}
$lang = $_COOKIE['lang'] ?? 'ru';
$texts = [
    'ru' => [
        'shop' => 'Магазин',
        'contacts' => 'Контакты',
        'cart' => 'Корзина',
        'add_to_cart' => 'В корзину',
        'phone_label' => 'Телефон для связи:',
        'back_to_shop' => 'Вернуться в магазин',
    ],
    'kz' => [
        'shop' => 'Дүкен',
        'contacts' => 'Байланыс',
        'cart' => 'Себет',
        'add_to_cart' => 'Себетке',
        'phone_label' => 'Байланыс телефоны:',
        'back_to_shop' => 'Дүкенге оралу',
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
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>rusEFI — Магазин</title>
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
            gap: 32px;
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
        .rusefi-cart {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #181818;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            position: relative;
        }
        .rusefi-cart svg {
            width: 28px;
            height: 28px;
            fill: none;
            stroke: #181818;
            stroke-width: 2;
        }
        .rusefi-cart-count {
            background: #fff;
            color: #181818;
            border-radius: 50%;
            font-size: 0.95em;
            font-weight: 700;
            min-width: 22px;
            min-height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -8px;
            right: -12px;
            border: 2px solid var(--rusefi-orange);
        }
        .rusefi-main {
            max-width: 1200px;
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
        .rusefi-products {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
        }
        .rusefi-card {
            background: var(--rusefi-card);
            border-radius: 12px;
            box-shadow: 0 2px 8px #18181822;
            width: 320px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            overflow: hidden;
            margin-bottom: 0;
            position: relative;
        }
        .rusefi-card-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            background: #232323;
            display: block;
            margin: 0 auto 12px auto;
            border-radius: 10px;
        }
        .rusefi-card-body {
            padding: 18px 22px 18px 22px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1 1 auto;
            min-height: 0;
        }
        .rusefi-card-title {
            font-size: 1.18rem;
            font-weight: 700;
            color: var(--rusefi-accent);
        }
        .rusefi-card-price {
            color: var(--rusefi-accent);
            font-size: 1.1rem;
            font-weight: 700;
        }
        .rusefi-card-desc {
            color: #e0e0e0;
            font-size: 1rem;
            margin-bottom: 8px;
            padding-bottom: 48px;
        }
        .rusefi-card-btns {
            display: flex;
            align-items: center;
            gap: 16px;
            width: 100%;
            margin-top: auto;
            margin-bottom: 0;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 0 22px 18px 22px;
            background: linear-gradient(0deg, var(--rusefi-card) 90%, transparent 100%);
        }
        .rusefi-card-btn, .rusefi-card-minus, .rusefi-card-plus {
            background: var(--rusefi-orange);
            color: #181818;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            padding: 14px 28px;
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
            min-width: 48px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rusefi-card-btn:hover, .rusefi-card-minus:hover, .rusefi-card-plus:hover {
            background: #ff9a4d;
            color: #fff;
        }
        .rusefi-card-qty {
            min-width: 38px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 900px) {
            .rusefi-products { gap: 18px; }
            .rusefi-card { width: 98vw; max-width: 340px; }
            .rusefi-card-img { height: 160px; }
        }
        @media (max-width: 600px) {
            .rusefi-header { padding: 0 10px; height: 48px; }
            .rusefi-logo { font-size: 1.3rem; }
            .rusefi-title { font-size: 1.5rem; margin-bottom: 18px; }
            .rusefi-main { padding: 18px 2vw 18px 2vw; }
            .rusefi-card { width: 98vw; max-width: 98vw; }
            .rusefi-card-img { height: 120px; }
            .rusefi-card-btn, .rusefi-card-minus, .rusefi-card-plus { font-size: 1.1rem; padding: 10px 0; min-width: 38px; min-height: 38px; }
        }
    </style>
</head>
<body>
    <header class="rusefi-header">
        <span class="rusefi-logo">rusEFI</span>
        <nav class="rusefi-header-menu">
            <a href="index.php"><?= $texts[$lang]['shop'] ?></a>
            <a href="#footer-contacts" id="contacts-link"><?= $texts[$lang]['contacts'] ?></a>
        </nav>
        <div style="display:flex;align-items:center;gap:8px;">
            <form method="get" style="margin:0;padding:0;">
                <button type="submit" name="lang" value="<?= $lang==='ru'?'kz':'ru' ?>" style="background:none;border:none;color:#181818;font-size:1.1rem;cursor:pointer;text-decoration:none;outline:none;box-shadow:none;"> <?= $lang==='ru'?'Рус':'Қаз' ?> </button>
            </form>
            <a href="cart.php" class="rusefi-cart">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span id="cart-count" class="rusefi-cart-count">0</span>
            </a>
        </div>
    </header>
    <main class="rusefi-main">
        <h1 class="rusefi-title">
            <?= $texts[$lang]['shop'] ?>
        </h1>
        <div class="rusefi-products" id="products-list"></div>
    </main>
    <div id="productModal" style="display:none;position:fixed;z-index:3000;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
  <div style="background:#232323;color:#fff;border-radius:14px;max-width:480px;width:98vw;padding:32px 24px 24px 24px;box-shadow:0 4px 32px #0003;position:relative;">
    <button type="button" id="closeProductModal" style="position:absolute;top:10px;right:16px;font-size:1.5rem;color:#888;background:none;border:none;cursor:pointer;">&times;</button>
    <div id="productModalContent"></div>
  </div>
</div>
    <footer id="footer-contacts" style="background:#232629;color:#bbb;text-align:center;padding:24px 0 12px 0;font-size:1rem;border-top:1px solid #333;">
        <div style="margin-bottom:8px;font-size:1.15em;">
            <?= $texts[$lang]['phone_label'] ?> <a href="tel:+77001234567" style="color:#ff7a1a;">+7 (700) 123-45-67</a>
        </div>
        &copy; <?php echo date('Y'); ?> rusEFI — <a href="https://www.shop.rusefi.com" style="color:#ff7a1a;">rusefi.com</a>
    </footer>
    <script>
    const lang = "<?= $lang ?>";
    const texts = {
        ru: { add_to_cart: "В корзину" },
        kz: { add_to_cart: "Себетке" }
    };
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
    function renderProducts() {
        let html = '';
        const cart = getCart();
        products.forEach(p => {
            const qty = cart[p.id] || 0;
            // Мультиязычность для name/description
            let name = typeof p.name === 'object' ? (p.name[lang] || p.name['ru'] || '') : p.name;
            let desc = typeof p.description === 'object' ? (p.description[lang] || p.description['ru'] || '') : (p.description || '');
            html += `<div class='rusefi-card' data-id='${p.id}'>
                <img src='${p.img}' alt='${name}' class='rusefi-card-img'>
                <div class='rusefi-card-body'>
                    <div class='rusefi-card-title'>${name}</div>
                    <div class='rusefi-card-price'>${formatPrice(p.price)}</div>
                    <div class='rusefi-card-desc'>${desc}</div>
                    <div class='rusefi-card-btns'>` +
                        (qty > 0 ?
                        `<button class='rusefi-card-minus' data-id='${p.id}'>-</button>
                         <span class='rusefi-card-qty'>${qty}</span>
                         <button class='rusefi-card-plus' data-id='${p.id}'>+</button>`
                        : `<button class='rusefi-card-btn' data-id='${p.id}'>${texts[lang].add_to_cart}</button>`)
                    + `</div>
                </div>
            </div>`;
        });
        document.getElementById('products-list').innerHTML = html;
        document.querySelectorAll('.rusefi-card-btn').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                cart[id] = (cart[id] || 0) + 1;
                setCart(cart);
                updateCartUI();
                renderProducts();
            };
        });
        document.querySelectorAll('.rusefi-card-plus').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const cart = getCart();
                cart[id] = (cart[id] || 0) + 1;
                setCart(cart);
                updateCartUI();
                renderProducts();
            };
        });
        document.querySelectorAll('.rusefi-card-minus').forEach(btn => {
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
    }
    document.addEventListener('DOMContentLoaded', function() {
        renderProducts();
        updateCartUI();
        document.getElementById('contacts-link').onclick = function(e) {
            e.preventDefault();
            document.getElementById('footer-contacts').scrollIntoView({behavior: 'smooth'});
        };
    });
    window.addEventListener('storage', function() { updateCartUI(); });
    // --- Модалка товара ---
    (function(){
      const modal = document.getElementById('productModal');
      const modalContent = document.getElementById('productModalContent');
      document.getElementById('closeProductModal').onclick = function(){ modal.style.display = 'none'; };
      window.addEventListener('click', function(e){ if(e.target === modal) modal.style.display = 'none'; });
      document.addEventListener('click', function(e){
        let card = e.target.closest('.rusefi-card');
        if(card && card.dataset.id){
          const id = card.dataset.id;
          const product = products.find(p => p.id == id);
          if(product){
            let name = typeof product.name === 'object' ? (product.name[lang] || product.name['ru'] || '') : product.name;
            let desc = typeof product.description === 'object' ? (product.description[lang] || product.description['ru'] || '') : (product.description || '');
            modalContent.innerHTML = `
              <img src='${product.img}' alt='${name}' style='width:100%;max-width:340px;max-height:320px;object-fit:contain;display:block;margin:0 auto 18px auto;border-radius:12px;background:#232323;'>
              <div style='font-size:1.3rem;font-weight:700;margin-bottom:8px;color:#ff7a1a;'>${name}</div>
              <div style='font-size:1.1rem;font-weight:600;margin-bottom:12px;'>${formatPrice(product.price)}</div>
              <div style='font-size:1rem;color:#e0e0e0;margin-bottom:8px;'>${desc}</div>
            `;
            modal.style.display = 'flex';
          }
        }
      });
    })();
    </script>
</body>
</html>