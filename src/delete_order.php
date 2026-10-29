<?php
session_start();
require_once __DIR__ . '/db_config.php';

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE OrderID = ?");
        $stmt->execute([$_GET['id']]);
        
        // العودة لصفحة الطلبات بعد الحذف بنجاح
        header("Location: my_orders.php");
    } catch (Exception $e) {
        echo "خطأ في الحذف: " . $e->getMessage();
    }
}
?>