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

// حذف طلب
if (isset($_GET['delete_order'])) {
    $oid = $_GET['delete_order'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE OrderID = ?");
    $stmt->execute([$oid]);
    $message = "✅ تم حذف الطلب بنجاح";
    $messageType = 'success';
}

// ===== فلاتر البحث =====
$search     = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$filterDate = isset($_GET['date'])     ? trim($_GET['date'])     : '';
$filterChan = isset($_GET['channel'])  ? trim($_GET['channel'])  : '';

$sql = "SELECT o.*, c.FirstName, c.LastName, c.Email 
        FROM orders o 
        LEFT JOIN customers c ON o.CustomerID = c.CustomerID 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (o.OrderID LIKE ? OR c.FirstName LIKE ? OR c.LastName LIKE ? OR c.Email LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterDate !== '') {
    $sql .= " AND o.OrderDate = ?";
    $params[] = $filterDate;
}
if ($filterChan !== '') {
    $sql .= " AND o.SalesChannel = ?";
    $params[] = $filterChan;
}
$sql .= " ORDER BY o.OrderDate DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// إحصائيات
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(TotalAmount),0) FROM orders")->fetchColumn();
$todayOrders   = $pdo->query("SELECT COUNT(*) FROM orders WHERE OrderDate = CURDATE()")->fetchColumn();
$avgOrder      = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// قائمة القنوات المتاحة
$channels = $pdo->query("SELECT DISTINCT SalesChannel FROM orders WHERE SalesChannel IS NOT NULL ORDER BY SalesChannel")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات | HSH Supermarket</title>
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
        .search-input { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Cairo'; font-size: 0.85rem; min-width: 200px; }
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
        .btn-info { background: var(--blue-light); color: var(--blue); }
        .btn-sm { padding: 6px 13px; font-size: 0.78rem; }

        /* Table */
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th { padding: 12px 16px; text-align: right; background: #F8FAFC; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border); }
        .orders-table td { padding: 14px 16px; border-bottom: 1px solid #F1F5F9; font-size: 0.87rem; vertical-align: middle; }
        .orders-table tr:hover td { background: #FAFCFF; }

        .order-id { font-weight: 800; color: var(--green); font-size: 0.85rem; }
        .customer-name { font-weight: 700; }
        .customer-email { font-size: 0.78rem; color: var(--text-muted); }
        .amount-badge { background: var(--green-light); color: #166534; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; }
        .channel-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .channel-online  { background: var(--blue-light);   color: #1D4ED8; }
        .channel-store   { background: var(--orange-light); color: #92400E; }
        .details-cell { font-size: 0.8rem; color: #475569; max-width: 250px; line-height: 1.5; }
        .date-cell { font-size: 0.85rem; color: var(--text-muted); white-space: nowrap; }
        .actions-cell { display: flex; gap: 8px; }

        /* Detail Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; backdrop-filter: blur(3px); justify-content: center; align-items: center; }
        .modal-overlay.open { display: flex; }
        .modal { background: #fff; border-radius: 20px; padding: 32px; width: 90%; max-width: 580px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-title { font-size: 1.1rem; font-weight: 900; }
        .modal-close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted); }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .detail-label { color: var(--text-muted); font-weight: 700; }
        .detail-value { font-weight: 600; text-align: left; max-width: 60%; }
        .items-list { background: #F8FAFC; border-radius: 12px; padding: 14px; margin-top: 10px; line-height: 2; font-size: 0.88rem; }

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
    <a href="admin_orders.php"    class="sidebar-link active"><span class="icon">🧾</span> الطلبات</a>
    <a href="admin_customers.php" class="sidebar-link"><span class="icon">👥</span> العملاء</a>
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
        <h1>🧾 إدارة الطلبات</h1>
        <p>عرض وتتبع جميع طلبات العملاء</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DCFCE7">🧾</div>
            <div class="stat-info">
                <div class="value" style="color:var(--green)"><?php echo $totalOrders; ?></div>
                <div class="label">إجمالي الطلبات</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#DBEAFE">💰</div>
            <div class="stat-info">
                <div class="value" style="color:var(--blue)">JD <?php echo number_format($totalRevenue, 0); ?></div>
                <div class="label">إجمالي الإيرادات</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7">📅</div>
            <div class="stat-info">
                <div class="value" style="color:var(--orange)"><?php echo $todayOrders; ?></div>
                <div class="label">طلبات اليوم</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FE">📊</div>
            <div class="stat-info">
                <div class="value" style="color:var(--purple)">JD <?php echo number_format($avgOrder, 2); ?></div>
                <div class="label">متوسط قيمة الطلب</div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="alert <?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="section-card">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="admin_orders.php" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" name="search" class="search-input"
                       placeholder="🔍 بحث برقم الطلب أو اسم العميل..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <input type="date" name="date" class="filter-select"
                       value="<?php echo htmlspecialchars($filterDate); ?>">
                <select name="channel" class="filter-select">
                    <option value="">كل القنوات</option>
                    <?php foreach($channels as $ch): ?>
                    <option value="<?php echo $ch; ?>" <?php echo $filterChan===$ch?'selected':''; ?>>
                        <?php echo $ch; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">بحث</button>
                <?php if ($search || $filterDate || $filterChan): ?>
                <a href="admin_orders.php" class="btn btn-secondary">إلغاء</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-header" style="border-top:none;">
            <span class="section-title">📋 قائمة الطلبات (<?php echo count($orders); ?>)</span>
            <span style="font-size:0.82rem; color:var(--text-muted);">
                إجمالي معروض: JD <?php echo number_format(array_sum(array_column($orders,'TotalAmount')),2); ?>
            </span>
        </div>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>لا توجد طلبات مطابقة</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>التاريخ</th>
                    <th>قناة البيع</th>
                    <th>المشتريات</th>
                    <th>المجموع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><span class="order-id"><?php echo htmlspecialchars($o['OrderID']); ?></span></td>
                <td>
                    <div class="customer-name">
                        <?php echo htmlspecialchars(($o['FirstName'] ?? '') . ' ' . ($o['LastName'] ?? '')); ?>
                    </div>
                    <div class="customer-email"><?php echo htmlspecialchars($o['Email'] ?? ''); ?></div>
                </td>
                <td class="date-cell"><?php echo htmlspecialchars($o['OrderDate']); ?></td>
                <td>
                    <?php 
                    $ch = $o['SalesChannel'] ?? 'Online';
                    $chClass = strtolower($ch) === 'online' ? 'channel-online' : 'channel-store';
                    ?>
                    <span class="channel-badge <?php echo $chClass; ?>"><?php echo htmlspecialchars($ch); ?></span>
                </td>
                <td class="details-cell"><?php echo htmlspecialchars($o['OrderDetails'] ?? ''); ?></td>
                <td><span class="amount-badge">JD <?php echo number_format($o['TotalAmount'], 2); ?></span></td>
                <td>
                    <div class="actions-cell">
                        <button class="btn btn-info btn-sm"
                            onclick="showDetail(
                                '<?php echo addslashes($o['OrderID']); ?>',
                                '<?php echo addslashes(($o['FirstName']??'').' '.($o['LastName']??'')); ?>',
                                '<?php echo addslashes($o['Email']??''); ?>',
                                '<?php echo addslashes($o['OrderDate']); ?>',
                                '<?php echo addslashes($o['SalesChannel']??''); ?>',
                                '<?php echo addslashes($o['OrderDetails']??''); ?>',
                                '<?php echo $o['TotalAmount']; ?>'
                            )">👁️ تفاصيل</button>
                        <a href="admin_orders.php?delete_order=<?php echo urlencode($o['OrderID']); ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('هل تريد حذف الطلب <?php echo addslashes($o['OrderID']); ?> نهائياً؟')">
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

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">🧾 تفاصيل الطلب</span>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<script>
function showDetail(id, customer, email, date, channel, details, total) {
    document.getElementById('modalBody').innerHTML = `
        <div class="detail-row"><span class="detail-label">رقم الطلب</span><span class="detail-value" style="color:var(--green);font-weight:900;">${id}</span></div>
        <div class="detail-row"><span class="detail-label">اسم العميل</span><span class="detail-value">${customer}</span></div>
        <div class="detail-row"><span class="detail-label">البريد الإلكتروني</span><span class="detail-value">${email}</span></div>
        <div class="detail-row"><span class="detail-label">تاريخ الطلب</span><span class="detail-value">${date}</span></div>
        <div class="detail-row"><span class="detail-label">قناة البيع</span><span class="detail-value">${channel}</span></div>
        <div class="detail-row"><span class="detail-label">المجموع الكلي</span><span class="detail-value" style="color:var(--green);font-size:1.1rem;font-weight:900;">JD ${parseFloat(total).toFixed(2)}</span></div>
        <div style="margin-top:14px;">
            <div class="detail-label" style="margin-bottom:8px;">📋 قائمة المشتريات:</div>
            <div class="items-list">${details.replace(/,\s*/g,'<br>• ').replace(/^/,'• ')}</div>
        </div>`;
    document.getElementById('detailModal').classList.add('open');
}
function closeModal() {
    document.getElementById('detailModal').classList.remove('open');
}
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
