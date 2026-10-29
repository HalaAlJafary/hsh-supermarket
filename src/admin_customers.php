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

$message = '';
$messageType = '';

// حذف عميل
if (isset($_GET['delete_customer'])) {
    $cid = $_GET['delete_customer'];
    // حذف طلباته أولاً ثم الحساب
    $pdo->prepare("DELETE FROM orders WHERE CustomerID = ?")->execute([$cid]);
    $pdo->prepare("DELETE FROM customers WHERE CustomerID = ? AND Role != 'Admin'")->execute([$cid]);
    $message = "✅ تم حذف العميل وطلباته بنجاح";
    $messageType = 'success';
}

// تعديل بيانات عميل
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_customer') {
    $stmt = $pdo->prepare("UPDATE customers SET FirstName=?, LastName=?, Email=?, Phone=?, City=? WHERE CustomerID=? AND Role != 'Admin'");
    $stmt->execute([
        trim($_POST['first_name']),
        trim($_POST['last_name']),
        trim($_POST['email']),
        trim($_POST['phone']),
        trim($_POST['city']),
        $_POST['customer_id']
    ]);
    $message = "✅ تم تحديث بيانات العميل بنجاح";
    $messageType = 'success';
}

// ===== فلاتر البحث =====
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterCity = isset($_GET['city'])   ? trim($_GET['city'])   : '';

$sql = "SELECT c.*, 
               COUNT(o.OrderID) as OrderCount, 
               COALESCE(SUM(o.TotalAmount), 0) as TotalSpent
        FROM customers c
        LEFT JOIN orders o ON c.CustomerID = o.CustomerID
        WHERE c.Role = 'Customer'";
$params = [];

if ($search !== '') {
    $sql .= " AND (c.FirstName LIKE ? OR c.LastName LIKE ? OR c.Email LIKE ? OR c.Phone LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterCity !== '') {
    $sql .= " AND c.City = ?";
    $params[] = $filterCity;
}
$sql .= " GROUP BY c.CustomerID ORDER BY TotalSpent DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// إحصائيات
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE Role='Customer'")->fetchColumn();
$totalRevenue   = $pdo->query("SELECT COALESCE(SUM(TotalAmount),0) FROM orders")->fetchColumn();
$newThisMonth   = $pdo->query("SELECT COUNT(*) FROM customers WHERE Role='Customer' AND MONTH(CustomerID) = MONTH(CURDATE())")->fetchColumn();
// أكثر عميل إنفاقاً
$topSpender = $pdo->query("SELECT c.FirstName, c.LastName, COALESCE(SUM(o.TotalAmount),0) as total 
                            FROM customers c LEFT JOIN orders o ON c.CustomerID=o.CustomerID 
                            WHERE c.Role='Customer' GROUP BY c.CustomerID ORDER BY total DESC LIMIT 1")->fetch();

// قائمة المدن
$cities = $pdo->query("SELECT DISTINCT City FROM customers WHERE City IS NOT NULL AND City != '' AND Role='Customer' ORDER BY City")->fetchAll(PDO::FETCH_COLUMN);

// جلب عميل للتعديل
$editCustomer = null;
if (isset($_GET['edit'])) {
    $s2 = $pdo->prepare("SELECT * FROM customers WHERE CustomerID = ? AND Role != 'Admin'");
    $s2->execute([$_GET['edit']]);
    $editCustomer = $s2->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملاء | HSH Supermarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #16A34A; --green-dark: #14532D; --green-light: #DCFCE7;
            --red: #EF4444; --red-light: #FEE2E2;
            --orange: #F59E0B; --orange-light: #FEF3C7;
            --blue: #3B82F6; --blue-light: #DBEAFE;
            --purple: #8B5CF6; --purple-light: #EDE9FE;
            --bg: #F0F4F8; --sidebar-bg: #0F172A;
            --card-bg: #FFFFFF; --text: #1E293B;
            --text-muted: #64748B; --border: #E2E8F0;
            --shadow: 0 4px 20px rgba(0,0,0,0.07);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Cairo', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 240px; min-height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; position: fixed; top: 0; right: 0; z-index: 50; padding: 0 0 20px; }
        .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.07); font-size: 1.5rem; font-weight: 900; color: #fff; }
        .sidebar-logo span { color: var(--green); }
        .sidebar-label { font-size: 0.68rem; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 20px 20px 6px; font-weight: 700; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #94A3B8; text-decoration: none; font-size: 0.88rem; font-weight: 600; transition: 0.2s; border-right: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { color: #fff; background: rgba(255,255,255,0.05); border-right-color: var(--green); }
        .sidebar-link .icon { font-size: 1rem; width: 20px; text-align: center; }
        .sidebar-bottom { margin-top: auto; padding: 0 20px; }
        .admin-badge { background: rgba(22,163,74,0.15); border: 1px solid rgba(22,163,74,0.3); border-radius: 10px; padding: 12px; margin-bottom: 12px; }
        .admin-badge .name { font-weight: 800; color: #fff; font-size: 0.9rem; }
        .admin-badge .role { color: var(--green); font-size: 0.75rem; font-weight: 700; }
        .logout-btn { display: block; text-align: center; padding: 10px; background: rgba(239,68,68,0.1); color: #F87171; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: 0.2s; }
        .logout-btn:hover { background: rgba(239,68,68,0.2); }

        /* Main */
        .main { margin-right: 240px; flex: 1; padding: 28px; }
        .page-header { margin-bottom: 24px; }
        .page-header h1 { font-size: 1.6rem; font-weight: 900; }
        .page-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--card-bg); border-radius: 16px; padding: 20px; box-shadow: var(--shadow); display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .stat-info .value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
        .stat-info .label { color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; }

        /* Alert */
        .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; font-size: 0.9rem; }
        .alert.success { background: var(--green-light); color: #166534; border: 1px solid #86EFAC; }
        .alert.error   { background: var(--red-light); color: #991B1B; border: 1px solid #FCA5A5; }

        /* Section */
        .section-card { background: var(--card-bg); border-radius: 18px; box-shadow: var(--shadow); margin-bottom: 24px; overflow: hidden; }
        .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .section-title { font-size: 1rem; font-weight: 800; }

        /* Filter Bar */
        .filter-bar { display: flex; gap: 12px; align-items: center; padding: 16px 24px; border-bottom: 1px solid var(--border); background: #FAFCFF; flex-wrap: wrap; }
        .search-input { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Cairo'; font-size: 0.85rem; min-width: 220px; }
        .search-input:focus { border-color: var(--green); outline: none; }
        .filter-select { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Cairo'; font-size: 0.85rem; background: #fff; cursor: pointer; }
        .filter-select:focus { border-color: var(--green); outline: none; }

        /* Buttons */
        .btn { padding: 9px 20px; border: none; border-radius: 10px; cursor: pointer; font-family: 'Cairo'; font-weight: 700; font-size: 0.85rem; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-primary:hover { background: var(--green-dark); }
        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #CBD5E1; }
        .btn-danger { background: var(--red-light); color: var(--red); }
        .btn-danger:hover { background: #FECACA; }
        .btn-edit { background: var(--blue-light); color: var(--blue); }
        .btn-edit:hover { background: #BFDBFE; }
        .btn-sm { padding: 6px 13px; font-size: 0.78rem; }

        /* Table */
        .customers-table { width: 100%; border-collapse: collapse; }
        .customers-table th { padding: 12px 16px; text-align: right; background: #F8FAFC; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border); }
        .customers-table td { padding: 14px 16px; border-bottom: 1px solid #F1F5F9; font-size: 0.87rem; vertical-align: middle; }
        .customers-table tr:hover td { background: #FAFCFF; }

        .avatar { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem; color: #fff; flex-shrink: 0; }
        .customer-name { font-weight: 700; }
        .customer-email { font-size: 0.78rem; color: var(--text-muted); }
        .customer-phone { font-size: 0.82rem; color: #475569; }
        .city-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: var(--purple-light); color: #5B21B6; }
        .orders-count { font-weight: 800; color: var(--blue); font-size: 1rem; }
        .spent-badge { background: var(--green-light); color: #166534; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; }
        .no-orders { color: var(--text-muted); font-size: 0.8rem; }
        .top-badge { background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }
        .actions-cell { display: flex; gap: 8px; }

        /* Edit Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; backdrop-filter: blur(3px); justify-content: center; align-items: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: #fff; border-radius: 20px; padding: 32px; width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .modal-title { font-size: 1.1rem; font-weight: 900; }
        .modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 0.82rem; font-weight: 700; color: var(--text-muted); }
        input[type=text], input[type=email] { padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Cairo'; font-size: 0.9rem; background: #FAFAFA; }
        input:focus { border-color: var(--green); outline: none; background: #fff; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">H<span>S</span>H <span style="font-size:0.7rem; color:#475569; font-weight:400;">Admin</span></div>
    <div class="sidebar-label">القائمة</div>
    <a href="admin_dashboard.php" class="sidebar-link"><span class="icon">📦</span> إدارة المنتجات</a>
    <a href="admin_orders.php"    class="sidebar-link"><span class="icon">🧾</span> الطلبات</a>
    <a href="admin_customers.php" class="sidebar-link active"><span class="icon">👥</span> العملاء</a>
    <div class="sidebar-label">المتجر</div>
    <a href="dashboard.php" class="sidebar-link" target="_blank"><span class="icon">🛒</span> عرض المتجر</a>
    <div class="sidebar-bottom">
        <div class="admin-badge">
            <div class="name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="role">🔐 مدير النظام</div>
        </div>
        <a href="logout.php" class="logout-btn">⬅️ تسجيل الخروج</a>
    </div>
</aside>

<!-- Main -->
<div class="main">
    <div class="page-header">
        <h1>👥 إدارة العملاء</h1>
        <p>عرض وإدارة جميع حسابات العملاء المسجلين</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DCFCE7">👥</div>
            <div class="stat-info">
                <div class="value" style="color:var(--green)"><?php echo $totalCustomers; ?></div>
                <div class="label">إجمالي العملاء</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#DBEAFE">💰</div>
            <div class="stat-info">
                <div class="value" style="color:var(--blue)">JD <?php echo number_format($totalRevenue, 0); ?></div>
                <div class="label">إجمالي مبيعاتهم</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7">🏆</div>
            <div class="stat-info">
                <div class="value" style="color:var(--orange); font-size:1rem; padding-top:4px;">
                    <?php echo $topSpender ? htmlspecialchars($topSpender['FirstName']) : '-'; ?>
                </div>
                <div class="label">الأكثر إنفاقاً (JD <?php echo $topSpender ? number_format($topSpender['total'],0) : 0; ?>)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FE">🏙️</div>
            <div class="stat-info">
                <div class="value" style="color:var(--purple)"><?php echo count($cities); ?></div>
                <div class="label">مدن مختلفة</div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="alert <?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Customers Table -->
    <div class="section-card">
        <div class="filter-bar">
            <form method="GET" action="admin_customers.php" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" name="search" class="search-input"
                       placeholder="🔍 بحث بالاسم أو الإيميل أو الهاتف..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="city" class="filter-select">
                    <option value="">كل المدن</option>
                    <?php foreach($cities as $c): ?>
                    <option value="<?php echo $c; ?>" <?php echo $filterCity===$c?'selected':''; ?>>
                        <?php echo $c; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                <?php if ($search || $filterCity): ?>
                <a href="admin_customers.php" class="btn btn-secondary">إلغاء</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-header" style="border-top:none;">
            <span class="section-title">📋 قائمة العملاء (<?php echo count($customers); ?>)</span>
        </div>

        <?php if (empty($customers)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>لا توجد نتائج مطابقة</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="customers-table">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>المدينة</th>
                    <th>عدد الطلبات</th>
                    <th>إجمالي الإنفاق</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $colors = ['#16A34A','#3B82F6','#8B5CF6','#F59E0B','#EC4899','#06B6D4'];
            foreach ($customers as $i => $c):
                $initials = strtoupper(mb_substr($c['FirstName']??'?',0,1) . mb_substr($c['LastName']??'',0,1));
                $color = $colors[$i % count($colors)];
                $isTop = ($topSpender && $c['FirstName']==$topSpender['FirstName'] && $c['TotalSpent']==$topSpender['total'] && $c['TotalSpent']>0);
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="avatar" style="background:<?php echo $color; ?>"><?php echo $initials; ?></div>
                        <div>
                            <div class="customer-name">
                                <?php echo htmlspecialchars($c['FirstName'].' '.$c['LastName']); ?>
                                <?php if($isTop): ?><span class="top-badge">🏆 Top</span><?php endif; ?>
                            </div>
                            <div class="customer-email"><?php echo htmlspecialchars($c['Email']); ?></div>
                        </div>
                    </div>
                </td>
                <td class="customer-phone"><?php echo htmlspecialchars($c['Phone'] ?? '—'); ?></td>
                <td>
                    <?php if($c['City']): ?>
                    <span class="city-badge">📍 <?php echo htmlspecialchars($c['City']); ?></span>
                    <?php else: ?>
                    <span style="color:var(--text-muted);font-size:0.8rem;">غير محدد</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['OrderCount'] > 0): ?>
                    <span class="orders-count"><?php echo $c['OrderCount']; ?></span>
                    <span style="color:var(--text-muted);font-size:0.78rem;"> طلب</span>
                    <?php else: ?>
                    <span class="no-orders">لا يوجد</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['TotalSpent'] > 0): ?>
                    <span class="spent-badge">JD <?php echo number_format($c['TotalSpent'], 2); ?></span>
                    <?php else: ?>
                    <span class="no-orders">JD 0.00</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions-cell">
                        <a href="admin_customers.php?edit=<?php echo urlencode($c['CustomerID']); ?>"
                           class="btn btn-edit btn-sm">✏️ تعديل</a>
                        <a href="admin_customers.php?delete_customer=<?php echo urlencode($c['CustomerID']); ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('هل تريد حذف العميل <?php echo addslashes($c['FirstName'].' '.$c['LastName']); ?> وجميع طلباته؟')">
                           🗑️ حذف
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Customer Modal -->
<?php if ($editCustomer): ?>
<div class="modal-overlay open" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✏️ تعديل بيانات العميل</span>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form method="POST" action="admin_customers.php">
            <input type="hidden" name="action" value="edit_customer">
            <input type="hidden" name="customer_id" value="<?php echo $editCustomer['CustomerID']; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>الاسم الأول</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($editCustomer['FirstName']); ?>" required>
                </div>
                <div class="form-group">
                    <label>الاسم الأخير</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($editCustomer['LastName']); ?>">
                </div>
                <div class="form-group full">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($editCustomer['Email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($editCustomer['Phone']); ?>">
                </div>
                <div class="form-group">
                    <label>المدينة</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($editCustomer['City']); ?>">
                </div>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function closeModal() {
    const url = new URL(window.location.href);
    url.searchParams.delete('edit');
    window.location.href = url.toString();
}
</script>
</body>
</html>
