<?php
session_start();
 
// ✅ إصلاح: التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
require_once __DIR__ . '/db_config.php';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $firstName  = $_POST['first_name'];
        $lastName   = $_POST['last_name'];
        $email      = $_POST['email'];
        $phone      = $_POST['phone'];
        $city       = $_POST['city'];
        // ✅ إصلاح: كان مثبت على 1، الآن يأخذ من السيشن
        $customerId = $_SESSION['user_id'];
 
        $sql = "UPDATE customers 
                SET FirstName = :fname, LastName = :lname, Email = :email, Phone = :phone, City = :city 
                WHERE CustomerID = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fname' => $firstName,
            ':lname' => $lastName,
            ':email' => $email,
            ':phone' => $phone,
            ':city'  => $city,
            ':id'    => $customerId
        ]);
 
        echo "<script>alert('✅ تم تحديث بياناتك بنجاح!'); window.location.href='dashboard.php';</script>";
        
    } catch (Exception $e) {
        echo "❌ خطأ في التحديث: " . $e->getMessage();
    }
}
?>
 