<?php
session_start();
// إعدادات الاتصال بقاعدة البيانات
require_once __DIR__ . '/db_config.php'; $charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// --- منطق البحث المطور ---
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchTerm != '') {
    // إذا بحث المستخدم: نجلب المنتجات المطابقة فقط
    $stmt = $pdo->prepare("SELECT * FROM products WHERE ProductName = :search OR Category = :search");
    $stmt->execute([':search' => $searchTerm]);
    $all_products = $stmt->fetchAll();
} else {
    // الوضع الطبيعي: جلب كل المنتجات لعرض الصفحة الأصلية
    $stmt = $pdo->query("SELECT * FROM products");
    $all_products = $stmt->fetchAll();
}

$categories = [];
foreach ($all_products as $product) {
    $categories[$product['Category']][] = $product;
}

// إعدادات الأقسام
$cat_meta = [
    'Dairy'         => ['bg' => '#DBEAFE', 'ac' => '#2563EB', 'icon' => '🥛', 'desc' => 'Milk, cheese, eggs & more'],
    'Bakery'        => ['bg' => '#FEF3C7', 'ac' => '#D97706', 'icon' => '🍞', 'desc' => 'Breads, pastries & cookies'],
    'Beverages'     => ['bg' => '#D1FAE5', 'ac' => '#059669', 'icon' => '☕', 'desc' => 'Coffee, tea, juices & water'],
    'Pantry'        => ['bg' => '#FEF9C3', 'ac' => '#CA8A04', 'icon' => '🫙', 'desc' => 'Oils, grains, canned goods'],
    'Produce'       => ['bg' => '#DCFCE7', 'ac' => '#16A34A', 'icon' => '🥦', 'desc' => 'Fresh fruits & vegetables'],
    'Snacks'        => ['bg' => '#FFE4E6', 'ac' => '#E11D48', 'icon' => '🍿', 'desc' => 'Chips, nuts & sweets'],
    'Meat'          => ['bg' => '#FCE7F3', 'ac' => '#BE185D', 'icon' => '🥩', 'desc' => 'Chicken, beef & seafood'],
    'Cleaning'      => ['bg' => '#EDE9FE', 'ac' => '#7C3AED', 'icon' => '🧹', 'desc' => 'Detergents & household care'],
    'Personal Care' => ['bg' => '#FFF1F2', 'ac' => '#E11D48', 'icon' => '🧴', 'desc' => 'Shampoo, soap & more'],
    'Default'       => ['bg' => '#F3F4F6', 'ac' => '#374151', 'icon' => '📦', 'desc' => 'General Products']
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSH Supermarket | المتجر</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #F4F6F8; margin: 0; color: #1E293B; }
        header { background: #fff; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
        .logo { font-size: 1.9rem; font-weight: 900; color: #16A34A; text-decoration: none; }
        .cart-trigger { background: #16A34A; color: #fff; border: none; padding: 10px 22px; border-radius: 50px; cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        
        .search-area { background: #fff; padding: 20px; border-bottom: 1px solid #E2E8F0; text-align: center; }
        .search-form { display: flex; justify-content: center; gap: 10px; max-width: 600px; margin: 0 auto; }
        .search-input { flex: 1; padding: 12px; border: 2px solid #F1F5F9; border-radius: 12px; font-family: 'Cairo'; outline: none; }
        .search-input:focus { border-color: #16A34A; }
        .search-btn { background: #16A34A; color: white; border: none; padding: 0 25px; border-radius: 12px; cursor: pointer; font-weight: bold; }

        main { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .cat-block { background: #fff; border-radius: 18px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .cat-header { display: flex; align-items: center; padding: 18px 24px; cursor: pointer; }
        .cat-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin-left: 16px; }
        .cat-info { flex: 1; text-align: right; }
        .cat-name { font-size: 1.1rem; font-weight: 800; }
        .chevron { transition: transform 0.3s; font-size: 0.8rem; }
        
        <?php if($searchTerm != ''): ?>
        .products-panel { max-height: none !important; border-top: 1px solid #F1F5F9; }
        .chevron { display: none; }
        <?php else: ?>
        .products-panel { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; background: #FCFDFF; }
        .cat-block.open .products-panel { max-height: 2000px; border-top: 1px solid #F1F5F9; }
        .cat-block.open .chevron { transform: rotate(180deg); }
        <?php endif; ?>

        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 15px; padding: 22px; }
        .product-card { background: #fff; border-radius: 14px; border: 1.5px solid #E9EFF6; overflow: hidden; text-align: center; transition: 0.2s; }
        .card-top { height: 110px; display: flex; align-items: center; justify-content: center; font-size: 3.2rem; }
        .card-body { padding: 12px 15px 15px; }
        .p-price { font-weight: 800; color: #16A34A; margin: 8px 0; }
        .add-btn { width: 100%; padding: 10px; border: none; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; }

        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; backdrop-filter: blur(2px); }
        .overlay.open { display: block; }
        .drawer { position: fixed; top: 0; left: -400px; width: 380px; height: 100%; background: #fff; z-index: 201; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; }
        .drawer.open { left: 0; }
        .drawer-header { padding: 22px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; }
        #drawerItems { flex: 1; overflow-y: auto; padding: 20px; }
        .drawer-footer { padding: 22px; border-top: 2px solid #F1F5F9; background: #F8FAFC; }
        .cart-item { display: flex; align-items: center; gap: 12px; background: #fff; padding: 15px; border-radius: 15px; margin-bottom: 12px; border: 1px solid #F1F5F9; }
        .qty-controls { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
        .qty-btn { width: 28px; height: 28px; border-radius: 8px; border: 1.5px solid #E2E8F0; background: #fff; cursor: pointer; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .checkout-btn { width: 100%; padding: 16px; background: #16A34A; color: #fff; border: none; border-radius: 14px; font-weight: 800; cursor: pointer; font-size: 1.1rem; }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php" class="logo">HSH Supermarket</a>
    <div style="display: flex; gap: 20px; align-items: center;">
        <a href="profile.php" style="text-decoration: none; color: #475569; font-weight: bold; font-size: 0.9rem;">👤 حسابي الشخصي</a>
        <a href="my_orders.php" style="text-decoration: none; color: #475569; font-weight: bold; font-size: 0.9rem;">📦 طلباتي السابقة</a>
        <button class="cart-trigger" onclick="toggleCart()">🛒 السلة (<span id="cartCount">0</span>)</button>
    </div>
</header>

<div class="search-area">
    <form method="GET" action="dashboard.php" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="اكتب اسم المنتج بدقة..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit" class="search-btn">بحث</button>
        <?php if($searchTerm != ''): ?>
            <a href="dashboard.php" style="align-self:center; color: #EF4444; text-decoration:none; font-size:0.8rem; margin-right:10px;">إلغاء</a>
        <?php endif; ?>
    </form>
</div>

<main>
    <?php if(empty($categories)): ?>
        <div style="text-align:center; padding: 50px;">
            <h3 style="color: #64748B;">لا توجد نتائج مطابقة لـ "<?php echo htmlspecialchars($searchTerm); ?>" 😕</h3>
            <a href="dashboard.php" style="color: #16A34A; font-weight:bold;">العودة للمتجر</a>
        </div>
    <?php endif; ?>

    <?php foreach ($categories as $categoryName => $products): 
        $meta = $cat_meta[$categoryName] ?? $cat_meta['Default'];
    ?>
    <div class="cat-block <?php echo ($searchTerm != '') ? 'open' : ''; ?>" id="cat-<?php echo md5($categoryName); ?>">
        <div class="cat-header" onclick="toggleCat('cat-<?php echo md5($categoryName); ?>')">
            <div class="cat-icon" style="background: <?php echo $meta['bg']; ?>"><?php echo $meta['icon']; ?></div>
            <div class="cat-info">
                <div class="cat-name"><?php echo $categoryName; ?></div>
                <div style="font-size: 0.8rem; color: #64748B;"><?php echo $meta['desc']; ?></div>
            </div>
            <span class="chevron">▼</span>
        </div>
        
        <div class="products-panel">
            <div class="products-grid">
                <?php foreach ($products as $p): 
                    $emoji = !empty($p['ProductEmoji']) ? $p['ProductEmoji'] : $meta['icon'];
                ?>
                <div class="product-card">
                    <div class="card-top" style="background: <?php echo $meta['bg']; ?>"><?php echo $emoji; ?></div>
                    <div class="card-body">
                        <div style="font-weight: 700; font-size: 0.85rem; height: 40px;"><?php echo $p['ProductName']; ?></div>
                        <div class="p-price">JD <?php echo number_format($p['Price'], 2); ?></div>
                        <button class="add-btn" style="background: <?php echo $meta['ac']; ?>" 
                                onclick="addToCart('<?php echo $p['ProductID']; ?>', '<?php echo addslashes($p['ProductName']); ?>', <?php echo $p['Price']; ?>, '<?php echo $emoji; ?>', <?php echo $p['StockLevel']; ?>)">
                            + إضافة للسلة
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</main>

<div class="overlay" id="overlay" onclick="toggleCart()"></div>
<div class="drawer" id="cartDrawer">
    <div class="drawer-header">
        <h2 style="margin:0; font-weight: 900;">🛒 طلباتك</h2>
        <button onclick="toggleCart()" style="border:none; background:none; font-size: 1.2rem; cursor:pointer;">✕</button>
    </div>
    <div id="drawerItems"></div>
    <div class="drawer-footer">
        <div style="display:flex; justify-content:space-between; font-weight:900; font-size:1.2rem; margin-bottom:20px;">
            <span>المجموع:</span> <span id="totalPrice" style="color: #16A34A;">JD 0.00</span>
        </div>
        <button class="checkout-btn" onclick="confirmOrder()">تأكيد وتثبيت الطلب</button>
    </div>
</div>

<script>
let cart = {};
function toggleCat(id) { document.getElementById(id).classList.toggle('open'); }
function toggleCart() {
    document.getElementById('cartDrawer').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('open');
}

// دالة الإضافة للسلة مع فحص المخزون
function addToCart(id, name, price, emoji, stockLevel) {
    let currentQtyInCart = cart[id] ? cart[id].qty : 0;

    if (currentQtyInCart + 1 > stockLevel) {
        alert(`عذراً، لا يمكن إضافة المزيد من ${name}. الكمية المتاحة في المخزن هي ${stockLevel} فقط.`);
        return; 
    }

    if (cart[id]) {
        cart[id].qty += 1;
    } else {
        cart[id] = { name, price, emoji, qty: 1, maxStock: stockLevel };
    }
    updateUI();
}

// تعديل دالة تحديث الكمية داخل السلة أيضاً
function updateQty(id, delta) {
    if (cart[id]) {
        if (delta > 0 && cart[id].qty + delta > cart[id].maxStock) {
            alert("وصلت للحد الأقصى المتاح في المخزن!");
            return;
        }
        cart[id].qty += delta;
        if (cart[id].qty <= 0) delete cart[id];
        updateUI();
    }
}

function updateUI() {
    const container = document.getElementById('drawerItems');
    container.innerHTML = '';
    let total = 0, count = 0;
    for (const id in cart) {
        const item = cart[id];
        const subtotal = item.price * item.qty;
        total += subtotal; count += item.qty;
        container.innerHTML += `
            <div class="cart-item">
                <div style="font-size: 2rem;">${item.emoji}</div>
                <div style="flex: 1;">
                    <div style="font-weight: 800; font-size: 0.9rem;">${item.name}</div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty('${id}', -1)">-</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty('${id}', 1)">+</button>
                    </div>
                </div>
                <div style="text-align: left;">
                    <div style="font-weight: 900; color: #1E293B;">JD ${subtotal.toFixed(2)}</div>
                </div>
            </div>`;
    }
    if(count === 0) container.innerHTML = '<p style="text-align:center; color:#94A3B8; margin-top:50px;">السلة فارغة</p>';
    document.getElementById('totalPrice').innerText = 'JD ' + total.toFixed(2);
    document.getElementById('cartCount').innerText = count;
}

function confirmOrder() {
    if (Object.keys(cart).length === 0) return alert("سلتك فارغة!");
    const totalAmount = parseFloat(document.getElementById('totalPrice').innerText.replace('JD ', ''));
    
    fetch('process_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ total: totalAmount, items: cart })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert("✅ تم تسجيل طلبك بنجاح!");
            cart = {}; updateUI(); toggleCart();
        } else { alert("❌ خطأ: " + data.message); }
    });
}
</script>
</body>
</html>