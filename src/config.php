<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'supermarket'); // تأكدي إن هذا الاسم هو نفسه اللي بـ phpMyAdmin
define('DB_USER', 'root');
define('DB_PASS', '12345678'); // اكتبي باسورد الـ AppServ تبعك هون إذا فيه

function getDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        // تفعيل وضع الأخطاء عشان نعرف لو صار مشكلة
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("فشل الاتصال بالقاعدة: " . $e->getMessage());
    }
}
?>
