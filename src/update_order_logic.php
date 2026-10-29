<?php
require_once __DIR__ . '/db_config.php';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['orderId'])) {
        // تنفيذ التحديث بناءً على رقم الطلب الموجود أصلاً
        $sql = "UPDATE orders 
                SET TotalAmount = :total, OrderDetails = :details 
                WHERE OrderID = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':total'   => $data['total'],
            ':details' => $data['details'],
            ':id'      => $data['orderId']
        ]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'رقم الطلب مفقود']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}