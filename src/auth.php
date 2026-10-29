<?php
session_start();
header('Content-Type: application/json');
 
require_once __DIR__ . '/db_config.php';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الاتصال بقاعدة البيانات']);
    exit();
}
 
$action = $_POST['action'] ?? '';
 
// ========== تسجيل الدخول ==========
if ($action === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'يرجى إدخال الإيميل وكلمة المرور']);
        exit();
    }
 
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE Email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($customer && $customer['Password'] === $password) {
        $_SESSION['user_id']   = $customer['CustomerID'];
        $_SESSION['user_name'] = $customer['FirstName'];
        $_SESSION['user_role'] = $customer['Role']; // ✅ حفظ الـ Role في السيشن
 
        // ✅ توجيه مختلف حسب الدور
        $redirect = ($customer['Role'] === 'Admin') ? 'admin_dashboard.php' : 'dashboard.php';
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'error' => 'الإيميل أو كلمة المرور غلطة']);
    }
    exit();
}
 
// ========== إنشاء حساب جديد ==========
if ($action === 'register') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $password  = $_POST['password'] ?? '';
 
    if (empty($firstName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'يرجى ملء الحقول المطلوبة']);
        exit();
    }
 
    $check = $pdo->prepare("SELECT CustomerID FROM customers WHERE Email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'error' => 'هذا الإيميل مسجل مسبقاً']);
        exit();
    }
 
    $sql = "INSERT INTO customers (FirstName, LastName, Email, Phone, City, Password, Role) 
            VALUES (:fname, :lname, :email, :phone, :city, :pass, 'Customer')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fname' => $firstName,
        ':lname' => $lastName,
        ':email' => $email,
        ':phone' => $phone,
        ':city'  => $city,
        ':pass'  => $password
    ]);
 
    $_SESSION['user_id']   = $pdo->lastInsertId();
    $_SESSION['user_name'] = $firstName;
    $_SESSION['user_role'] = 'Customer';
 
    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
    exit();
}
 
echo json_encode(['success' => false, 'error' => 'طلب غير صالح']);
?>
 