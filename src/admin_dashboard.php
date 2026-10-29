<?php
session_start();

// التحقق من تسجيل الدخول وصلاحية الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/db_config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// ===== معالجة العمليات (إضافة / تعديل / حذف) =====
$message = '';
$messageType = '';

// حذف منتج
if (isset($_GET['delete_product'])) {
    $pid = $_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE ProductID = ?");
    $stmt->execute([$pid]);
    $message = "✅ تم حذف المنتج بنجاح";
    $messageType = 'success';
}

// إضافة منتج جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $pid      = trim($_POST['product_id']);
    $name     = trim($_POST['product_name']);
    $emoji    = trim($_POST['product_emoji']);
    $category = trim($_POST['category']);
    $price    = floatval($_POST['price']);
    $stock    = intval($_POST['stock']);
    $wid      = trim($_POST['warehouse_id']);

    // فحص إذا المعرّف موجود مسبقاً
    $check = $pdo->prepare("SELECT ProductID FROM products WHERE ProductID = ?");
    $check->execute([$pid]);
    if ($check->fetch()) {
        $message = "❌ معرّف المنتج موجود مسبقاً، اختر معرّفاً آخر";
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (ProductID, ProductName, ProductEmoji, Category, Price, StockLevel, WarehouseID) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$pid, $name, $emoji, $category, $price, $stock, $wid]);
        $message = "✅ تمت إضافة المنتج بنجاح";
        $messageType = 'success';
    }
}

// تعديل منتج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $pid      = trim($_POST['product_id']);
    $name     = trim($_POST['product_name']);
    $emoji    = trim($_POST['product_emoji']);
    $category = trim($_POST['category']);
    $price    = floatval($_POST['price']);
    $stock    = intval($_POST['stock']);
    $wid      = trim($_POST['warehouse_id']);

    $stmt = $pdo->prepare("UPDATE products SET ProductName=?, ProductEmoji=?, Category=?, Price=?, StockLevel=?, WarehouseID=? WHERE ProductID=?");
    $stmt->execute([$name, $emoji, $category, $price, $stock, $wid, $pid]);
    $message = "✅ تم تحديث المنتج بنجاح";
    $messageType = 'success';
}

// ===== جلب البيانات =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

$sql = "SELECT p.*, w.Location as WarehouseLocation FROM products p LEFT JOIN warehouses w ON p.WarehouseID = w.WarehouseID WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.ProductName LIKE ? OR p.ProductID LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterCat !== '') {
    $sql .= " AND p.Category = ?";
    $params[] = $filterCat;
}
$sql .= " ORDER BY p.Category, p.ProductName";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// إجماليات
$totalProducts  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(DISTINCT Category) FROM products")->fetchColumn();
$lowStock       = $pdo->query("SELECT COUNT(*) FROM products WHERE StockLevel < 50")->fetchColumn();
$totalOrders    = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// قائمة الفئات
$categories = $pdo->query("SELECT DISTINCT Category FROM products ORDER BY Category")->fetchAll(PDO::FETCH_COLUMN);

// قائمة المستودعات
$warehouses = $pdo->query("SELECT WarehouseID, Location FROM warehouses ORDER BY WarehouseID")->fetchAll();

// جلب منتج للتعديل
$editProduct = null;
if (isset($_GET['edit'])) {
    $stmt2 = $pdo->prepare("SELECT * FROM products WHERE ProductID = ?");
    $stmt2->execute([$_GET['edit']]);
    $editProduct = $stmt2->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الأدمن | HSH Supermarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #16A34A;
            --green-dark: #14532D;
            --green-light: #DCFCE7;
            --red: #EF4444;
            --red-light: #FEE2E2;
            --orange: #F59E0B;
            --orange-light: #FEF3C7;
            --blue: #3B82F6;
            --blue-light: #DBEAFE;
            --bg: #F0F4F8;
            --sidebar-bg: #0F172A;
            --sidebar-text: #94A3B8;
            --sidebar-active: #16A34A;
            --card-bg: #FFFFFF;
            --text: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --shadow: 0 4px 20px rgba(0,0,0,0.07);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Cairo', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* ===== Sidebar ===== */
        .sidebar {
            width: 240px; min-height: 100vh; background: var(--sidebar-bg);
            display: flex; flex-direction: column; position: fixed; top: 0; right: 0; z-index: 50;
            padding: 0 0 20px;
        }
        .sidebar-logo {
            padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.07);
            font-size: 1.5rem; font-weight: 900; color: #fff;
        }
        .sidebar-logo span { color: var(--green); }
        .sidebar-label {
            font-size: 0.68rem; color: #475569; text-transform: uppercase;
            letter-spacing: 1px; padding: 20px 20px 6px; font-weight: 700;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px; padding: 10px 20px;
            color: var(--sidebar-text); text-decoration: none; font-size: 0.88rem;
            font-weight: 600; transition: 0.2s; border-right: 3px solid transparent;
        }
        .sidebar-link:hover, .sidebar-link.active {
            color: #fff; background: rgba(255,255,255,0.05);
            border-right-color: var(--green);
        }
        .sidebar-link .icon { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-bottom { margin-top: auto; padding: 0 20px; }
        .admin-badge {
            background: rgba(22,163,74,0.15); border: 1px solid rgba(22,163,74,0.3);
            border-radius: 10px; padding: 12px; margin-bottom: 12px;
        }
        .admin-badge .name { font-weight: 800; color: #fff; font-size: 0.9rem; }
        .admin-badge .role { color: var(--green); font-size: 0.75rem; font-weight: 700; }
        .logout-btn {
            display: block; text-align: center; padding: 10px; background: rgba(239,68,68,0.1);
            color: #F87171; border-radius: 10px; text-decoration: none; font-weight: 700;
            font-size: 0.85rem; transition: 0.2s;
        }
        .logout-btn:hover { background: rgba(239,68,68,0.2); }

        /* ===== Main Content ===== */
        .main { margin-right: 240px; flex: 1; padding: 28px; }

        /* ===== Header ===== */
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 1.6rem; font-weight: 900; color: var(--text); }
        .page-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }

        /* ===== Stats Cards ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--card-bg); border-radius: 16px; padding: 20px;
            box-shadow: var(--shadow); display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .stat-info .value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
        .stat-info .label { color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; }

        /* ===== Alert Message ===== */
        .alert {
            padding: 14px 18px; border-radius: 12px; margin-bottom: 20px;
            font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;
        }
        .alert.success { background: var(--green-light); color: #166534; border: 1px solid #86EFAC; }
        .alert.error   { background: var(--red-light); color: #991B1B; border: 1px solid #FCA5A5; }

        /* ===== Section Card ===== */
        .section-card {
            background: var(--card-bg); border-radius: 18px; box-shadow: var(--shadow);
            margin-bottom: 24px; overflow: hidden;
        }
        .section-header {
            padding: 18px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-title { font-size: 1rem; font-weight: 800; }
        .section-body { padding: 24px; }

        /* ===== Form Grid ===== */
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 0.82rem; font-weight: 700; color: var(--text-muted); }
        input[type=text], input[type=number], select {
            padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'Cairo'; font-size: 0.9rem; color: var(--text);
            transition: 0.2s; background: #FAFAFA;
        }
        input:focus, select:focus { border-color: var(--green); outline: none; background: #fff; }
        .emoji-preview { font-size: 2rem; text-align: center; padding: 6px; }

        .btn-group { display: flex; gap: 10px; margin-top: 6px; }
        .btn {
            padding: 11px 24px; border: none; border-radius: 10px; cursor: pointer;
            font-family: 'Cairo'; font-weight: 700; font-size: 0.9rem; transition: 0.2s;
        }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-primary:hover { background: var(--green-dark); }
        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #CBD5E1; }
        .btn-danger { background: var(--red-light); color: var(--red); }
        .btn-danger:hover { background: #FECACA; }
        .btn-edit { background: var(--blue-light); color: var(--blue); }
        .btn-edit:hover { background: #BFDBFE; }
        .btn-sm { padding: 7px 14px; font-size: 0.8rem; }

        /* ===== Filter Bar ===== */
        .filter-bar {
            display: flex; gap: 12px; align-items: center;
            padding: 16px 24px; border-bottom: 1px solid var(--border);
            background: #FAFCFF; flex-wrap: wrap;
        }
        .filter-bar form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .search-input {
            padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'Cairo'; font-size: 0.85rem; min-width: 220px;
        }
        .search-input:focus { border-color: var(--green); outline: none; }
        .cat-badge {
            padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700;
            cursor: pointer; text-decoration: none; border: 1.5px solid var(--border);
            color: var(--text-muted); transition: 0.2s;
        }
        .cat-badge:hover, .cat-badge.active {
            background: var(--green); color: #fff; border-color: var(--green);
        }

        /* ===== Products Table ===== */
        .products-table { width: 100%; border-collapse: collapse; }
        .products-table th {
            padding: 12px 16px; text-align: right; background: #F8FAFC;
            color: var(--text-muted); font-size: 0.78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }
        .products-table td {
            padding: 14px 16px; border-bottom: 1px solid #F1F5F9;
            font-size: 0.88rem; vertical-align: middle;
        }
        .products-table tr:hover td { background: #FAFCFF; }
        .product-name { font-weight: 700; }
        .product-id { color: var(--text-muted); font-size: 0.78rem; }
        .price-badge {
            background: var(--green-light); color: #166534;
            padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.85rem;
        }
        .stock-badge {
            padding: 4px 10px; border-radius: 8px; font-weight: 700; font-size: 0.82rem;
        }
        .stock-ok   { background: var(--green-light); color: #166534; }
        .stock-low  { background: var(--orange-light); color: #92400E; }
        .stock-very-low { background: var(--red-light); color: #991B1B; }
        .cat-pill {
            padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;
            background: var(--blue-light); color: #1D4ED8;
        }
        .actions-cell { display: flex; gap: 8px; }
        .warehouse-tag { font-size: 0.78rem; color: var(--text-muted); }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; }

        /* ===== Quick Stock Inline Edit ===== */
        .stock-quick { display: flex; align-items: center; gap: 6px; }
        .stock-input {
            width: 70px; padding: 5px 8px; border: 1.5px solid var(--border); border-radius: 8px;
            font-family: 'Cairo'; font-size: 0.82rem; text-align: center;
        }
        .stock-save-btn {
            padding: 5px 10px; background: var(--green); color: #fff; border: none;
            border-radius: 7px; cursor: pointer; font-size: 0.75rem; font-weight: 700;
        }

        /* ===== Modal ===== */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            z-index: 200; backdrop-filter: blur(3px); justify-content: center; align-items: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: #fff; border-radius: 20px; padding: 32px; width: 90%; max-width: 640px;
            max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .modal-title { font-size: 1.15rem; font-weight: 900; }
        .modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted); }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
    </style>
</head>
<body>

<!-- ===== Sidebar ===== -->
<aside class="sidebar">
    <div class="sidebar-logo">H<span>S</span>H <span style="font-size:0.7rem; color:#475569; font-weight:400;">Admin</span></div>

    <div class="sidebar-label">القائمة</div>
    <a href="admin_dashboard.php" class="sidebar-link active">
        <span class="icon">📦</span> إدارة المنتجات
    </a>
    <a href="admin_orders.php" class="sidebar-link">
        <span class="icon">🧾</span> الطلبات
    </a>
    <a href="admin_customers.php" class="sidebar-link">
        <span class="icon">👥</span> العملاء
    </a>

    <div class="sidebar-label">المتجر</div>
    <a href="dashboard.php" class="sidebar-link" target="_blank">
        <span class="icon">🛒</span> عرض المتجر
    </a>

    <div class="sidebar-bottom">
        <div class="admin-badge">
            <div class="name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="role">🔐 مدير النظام</div>
        </div>
        <a href="logout.php" class="logout-btn">⬅️ تسجيل الخروج</a>
    </div>
</aside>

<!-- ===== Main ===== -->
<div class="main">

    <div class="page-header">
        <h1>📦 إدارة المنتجات</h1>
        <p>إضافة، تعديل، وحذف منتجات المتجر</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DCFCE7">📦</div>
            <div class="stat-info">
                <div class="value" style="color:var(--green)"><?php echo $totalProducts; ?></div>
                <div class="label">إجمالي المنتجات</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#DBEAFE">🏷️</div>
            <div class="stat-info">
                <div class="value" style="color:var(--blue)"><?php echo $totalCategories; ?></div>
                <div class="label">الفئات</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7">⚠️</div>
            <div class="stat-info">
                <div class="value" style="color:var(--orange)"><?php echo $lowStock; ?></div>
                <div class="label">مخزون منخفض (&lt;50)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FCE7F3">🧾</div>
            <div class="stat-info">
                <div class="value" style="color:#BE185D"><?php echo $totalOrders; ?></div>
                <div class="label">إجمالي الطلبات</div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <?php if ($message): ?>
    <div class="alert <?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- Add Product Section -->
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">➕ إضافة منتج جديد</span>
            <button class="btn btn-secondary btn-sm" onclick="toggleForm()">إخفاء / إظهار</button>
        </div>
        <div class="section-body" id="addForm">
            <form method="POST" action="admin_dashboard.php">
                <input type="hidden" name="action" value="add_product">
                <div class="form-grid">
                    <div class="form-group">
                        <label>معرّف المنتج (P051...)</label>
                        <input type="text" name="product_id" placeholder="P051" required>
                    </div>
                    <div class="form-group">
                        <label>اسم المنتج</label>
                        <input type="text" name="product_name" placeholder="مثال: Milk 1L" required>
                    </div>
                    <div class="form-group">
                        <label>الإيموجي 🛍️</label>
                        <input type="text" name="product_emoji" placeholder="🥛" maxlength="5"
                               oninput="document.getElementById('ep').innerText=this.value||'📦'">
                        <div class="emoji-preview" id="ep">📦</div>
                    </div>
                    <div class="form-group">
                        <label>الفئة</label>
                        <select name="category" required>
                            <option value="">-- اختر الفئة --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                            <option value="__new__">+ فئة جديدة (اكتبها يدوياً)</option>
                        </select>
                    </div>
                    <div class="form-group" id="newCatGroup" style="display:none">
                        <label>اسم الفئة الجديدة</label>
                        <input type="text" name="new_category" placeholder="مثال: Frozen">
                    </div>
                    <div class="form-group">
                        <label>السعر (JD)</label>
                        <input type="number" name="price" step="0.01" min="0" placeholder="1.50" required>
                    </div>
                    <div class="form-group">
                        <label>المخزون (وحدات)</label>
                        <input type="number" name="stock" min="0" placeholder="100" required>
                    </div>
                    <div class="form-group">
                        <label>المستودع</label>
                        <select name="warehouse_id" required>
                            <option value="">-- اختر المستودع --</option>
                            <?php foreach ($warehouses as $w): ?>
                            <option value="<?php echo $w['WarehouseID']; ?>">
                                <?php echo $w['WarehouseID']; ?> - <?php echo $w['Location']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="btn-group" style="margin-top:20px">
                    <button type="submit" class="btn btn-primary">💾 حفظ المنتج</button>
                    <button type="reset" class="btn btn-secondary">🔄 مسح</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products List -->
    <div class="section-card">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="admin_dashboard.php" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" name="search" class="search-input"
                       placeholder="🔍 بحث بالاسم أو المعرّف..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary btn-sm">بحث</button>
                <?php if ($search || $filterCat): ?>
                <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">إلغاء</a>
                <?php endif; ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($filterCat); ?>">
            </form>
            <!-- Category Filters -->
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-right:auto;">
                <a href="admin_dashboard.php<?php echo $search ? '?search='.urlencode($search) : ''; ?>"
                   class="cat-badge <?php echo $filterCat===''?'active':''; ?>">الكل</a>
                <?php foreach ($categories as $cat): ?>
                <a href="admin_dashboard.php?cat=<?php echo urlencode($cat); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"
                   class="cat-badge <?php echo $filterCat===$cat?'active':''; ?>">
                    <?php echo $cat; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section-header" style="border-top: none;">
            <span class="section-title">📋 قائمة المنتجات (<?php echo count($products); ?>)</span>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>لا توجد نتائج</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="products-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الفئة</th>
                    <th>السعر</th>
                    <th>المخزون</th>
                    <th>المستودع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p):
                $stockClass = $p['StockLevel'] >= 50 ? 'stock-ok' : ($p['StockLevel'] >= 20 ? 'stock-low' : 'stock-very-low');
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:1.6rem;"><?php echo $p['ProductEmoji'] ?: '📦'; ?></span>
                        <div>
                            <div class="product-name"><?php echo htmlspecialchars($p['ProductName']); ?></div>
                            <div class="product-id"><?php echo htmlspecialchars($p['ProductID']); ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="cat-pill"><?php echo htmlspecialchars($p['Category']); ?></span></td>
                <td><span class="price-badge">JD <?php echo number_format($p['Price'], 2); ?></span></td>
                <td>
                    <!-- تعديل المخزون السريع -->
                    <form method="POST" action="admin_dashboard.php" style="display:inline">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="product_id" value="<?php echo $p['ProductID']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($p['ProductName']); ?>">
                        <input type="hidden" name="product_emoji" value="<?php echo htmlspecialchars($p['ProductEmoji']); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($p['Category']); ?>">
                        <input type="hidden" name="price" value="<?php echo $p['Price']; ?>">
                        <input type="hidden" name="warehouse_id" value="<?php echo $p['WarehouseID']; ?>">
                        <div class="stock-quick">
                            <input type="number" name="stock" value="<?php echo $p['StockLevel']; ?>"
                                   class="stock-input <?php echo $stockClass; ?>" min="0">
                            <button type="submit" class="stock-save-btn">✓</button>
                        </div>
                    </form>
                </td>
                <td>
                    <span class="warehouse-tag">
                        <?php echo htmlspecialchars($p['WarehouseID']); ?><br>
                        <small><?php echo htmlspecialchars($p['WarehouseLocation'] ?? ''); ?></small>
                    </span>
                </td>
                <td>
                    <div class="actions-cell">
                        <a href="admin_dashboard.php?edit=<?php echo urlencode($p['ProductID']); ?><?php echo $filterCat ? '&cat='.urlencode($filterCat) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"
                           class="btn btn-edit btn-sm">✏️ تعديل</a>
                        <a href="admin_dashboard.php?delete_product=<?php echo urlencode($p['ProductID']); ?><?php echo $filterCat ? '&cat='.urlencode($filterCat) : ''; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('هل تريد حذف هذا المنتج نهائياً؟')">🗑️ حذف</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end main -->

<!-- ===== Edit Modal ===== -->
<?php if ($editProduct): ?>
<div class="modal-overlay open" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✏️ تعديل: <?php echo htmlspecialchars($editProduct['ProductName']); ?></span>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form method="POST" action="admin_dashboard.php">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="product_id" value="<?php echo $editProduct['ProductID']; ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>اسم المنتج</label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($editProduct['ProductName']); ?>" required>
                </div>
                <div class="form-group">
                    <label>الإيموجي</label>
                    <input type="text" name="product_emoji" value="<?php echo htmlspecialchars($editProduct['ProductEmoji']); ?>" maxlength="5">
                </div>
                <div class="form-group">
                    <label>الفئة</label>
                    <select name="category" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $editProduct['Category']===$cat?'selected':''; ?>>
                            <?php echo $cat; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>السعر (JD)</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo $editProduct['Price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>المخزون</label>
                    <input type="number" name="stock" min="0" value="<?php echo $editProduct['StockLevel']; ?>" required>
                </div>
                <div class="form-group">
                    <label>المستودع</label>
                    <select name="warehouse_id" required>
                        <?php foreach ($warehouses as $w): ?>
                        <option value="<?php echo $w['WarehouseID']; ?>" <?php echo $editProduct['WarehouseID']===$w['WarehouseID']?'selected':''; ?>>
                            <?php echo $w['WarehouseID']; ?> - <?php echo $w['Location']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="btn-group" style="margin-top:20px">
                <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function toggleForm() {
    const f = document.getElementById('addForm');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

function closeModal() {
    const url = new URL(window.location.href);
    url.searchParams.delete('edit');
    window.location.href = url.toString();
}

// فئة جديدة
const catSelect = document.querySelector('select[name="category"]');
const newCatGroup = document.getElementById('newCatGroup');
const newCatInput = document.querySelector('input[name="new_category"]');

if (catSelect) {
    catSelect.addEventListener('change', function() {
        if (this.value === '__new__') {
            newCatGroup.style.display = 'block';
            newCatInput.required = true;
            // لما يسبمت، نحط القيمة الجديدة
            catSelect.closest('form').addEventListener('submit', function(e) {
                if (catSelect.value === '__new__' && newCatInput.value.trim()) {
                    catSelect.value = newCatInput.value.trim();
                }
            }, { once: true });
        } else {
            newCatGroup.style.display = 'none';
            newCatInput.required = false;
        }
    });
}
</script>
</body>
</html>
