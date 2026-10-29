<?php
session_start();
 
// ✅ إصلاح: التحقق من تسجيل الدخول أولاً
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
require_once __DIR__ . '/db_config.php';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
 
    // ✅ إصلاح: استخدام السيشن بشكل صحيح بدون fallback وهمي
    $customerId = $_SESSION['user_id'];
 
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE CustomerID = ? ORDER BY OrderDate DESC");
    $stmt->execute([$customerId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
} catch (Exception $e) {
    die("خطأ في جلب البيانات: " . $e->getMessage());
}
?>
 
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجل طلباتي | HSH Supermarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; padding: 40px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: #1e293b; margin-bottom: 25px; border-right: 5px solid #16a34a; padding-right: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: right; border-bottom: 1px solid #f1f5f9; }
        th { background-color: #f8fafc; color: #64748b; font-weight: 700; }
        .order-id { color: #16a34a; font-weight: bold; }
        .details-text { font-size: 0.9rem; color: #475569; }
        .total-price { font-weight: bold; color: #1e293b; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border-radius: 10px; text-decoration: none; font-size: 0.85rem; transition: 0.3s; border: none; cursor: pointer; font-family: 'Cairo'; }
        .btn-delete { background-color: #fee2e2; color: #ef4444; }
        .empty-msg { text-align: center; padding: 60px 0; color: #94a3b8; font-size: 1.1rem; }
    </style>
</head>
<body>
 
<div class="container">
    <h2>📦 سجل طلباتي السابقة</h2>
 
    <?php if (empty($orders)): ?>
        <div class="empty-msg">
            <div style="font-size: 3rem; margin-bottom: 15px;">🛒</div>
            <p>ما سجلت أي طلبات بعد!</p>
            <a href="dashboard.php" style="color: #16a34a; font-weight: bold;">تصفح المتجر</a>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>التاريخ</th>
                <th>قائمة المشتريات</th>
                <th>المجموع الكلي</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td class="order-id"><?php echo htmlspecialchars($order['OrderID']); ?></td>
                <td><?php echo htmlspecialchars($order['OrderDate']); ?></td>
                <td class="details-text"><?php echo htmlspecialchars($order['OrderDetails']); ?></td>
                <td class="total-price">JD <?php echo number_format($order['TotalAmount'], 2); ?></td>
                <td class="actions">
                    <button onclick="confirmDelete('<?php echo $order['OrderID']; ?>')" class="btn btn-delete">حذف</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
 
    <div style="margin-top: 30px;">
        <a href="dashboard.php" style="color: #16a34a; text-decoration: none; font-weight: bold;">← العودة للمتجر</a>
    </div>
</div>
 
<script>
function confirmDelete(orderId) {
    if (confirm('هل أنت متأكد من حذف هذا الطلب نهائياً؟')) {
        window.location.href = 'delete_order.php?id=' + orderId;
    }
}
</script>
 
</body>
</html>
