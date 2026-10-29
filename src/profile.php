<?php
session_start();
 
// ✅ إصلاح: التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/db_config.php';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $customerId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE CustomerID = ?");
    $stmt->execute([$customerId]);
    // ✅ إصلاح: كان اسمه $orders بالخطأ، الصح هو $user_data
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$user_data) {
        die("لم يتم العثور على بيانات المستخدم.");
    }
} catch (Exception $e) {
    die("خطأ: " . $e->getMessage());
}
?>
 
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملفي الشخصي | HSH Supermarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .profile-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 450px; }
        h2 { text-align: center; color: #1e293b; margin-bottom: 25px; }
        .form-row { display: flex; gap: 15px; }
        .form-group { margin-bottom: 15px; flex: 1; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-weight: bold; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; font-family: 'Cairo'; }
        .btn-save { width: 100%; padding: 12px; background: #16a34a; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 1rem; font-weight: bold; margin-top: 10px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #64748b; text-decoration: none; }
    </style>
</head>
<body>
 
<div class="profile-card">
    <h2>👤 تعديل بياناتي</h2>
    <form action="update_profile.php" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>الاسم الأول</label>
                <!-- ✅ إصلاح: $user_data بدل $orders -->
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_data['FirstName']); ?>" required>
            </div>
            <div class="form-group">
                <label>الاسم الأخير</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_data['LastName']); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" required>
        </div>
        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['Phone']); ?>">
        </div>
        <div class="form-group">
            <label>المدينة</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($user_data['City']); ?>">
        </div>
        <button type="submit" class="btn-save">حفظ التغييرات</button>
    </form>
    <a href="dashboard.php" class="back-link">← العودة للمتجر</a>
</div>
 
</body>
</html>
 