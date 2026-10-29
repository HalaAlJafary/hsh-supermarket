<?php
session_start();
 
// ✅ إصلاح: التحقق من وجود المستخدم في السيشن قبل أي شيء
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit();
}
 
require_once __DIR__ . '/db_config.php';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
 
    $data = json_decode(file_get_contents('php://input'), true);
 
    if ($data && isset($data['items'])) {
        // ✅ إصلاح: استخدام السيشن بشكل صحيح
        $customerId = $_SESSION['user_id'];
        $total = $data['total'];
        $orderDate = date('Y-m-d');
        $channel = 'Online';
 
        $detailsArray = [];
        foreach ($data['items'] as $item) {
            $detailsArray[] = $item['name'] . " (x" . $item['qty'] . ")";
        }
        $orderDetails = implode(", ", $detailsArray);
 
        $sqlOrder = "INSERT INTO orders (OrderID, CustomerID, OrderDate, SalesChannel, TotalAmount, OrderDetails) 
                     VALUES (:id, :cid, :odate, :channel, :total, :details)";
        
        $stmtOrder = $pdo->prepare($sqlOrder);
        $orderId = "ORD-" . rand(1000, 9999);
 
        $stmtOrder->execute([
            ':id'      => $orderId,
            ':cid'     => $customerId,
            ':odate'   => $orderDate,
            ':channel' => $channel,
            ':total'   => $total,
            ':details' => $orderDetails
        ]);
 
        foreach ($data['items'] as $item) {
            $updateProduct = "UPDATE products 
                              SET StockLevel = StockLevel - :qty 
                              WHERE ProductName = :p_name";
            $stmt1 = $pdo->prepare($updateProduct);
            $stmt1->execute([':qty' => $item['qty'], ':p_name' => $item['name']]);
 
            $updateWarehouse = "UPDATE warehouses 
                                SET AvailableSpace = AvailableSpace + :qty 
                                WHERE WarehouseID = (SELECT WarehouseID FROM products WHERE ProductName = :p_name LIMIT 1)";
            $stmt2 = $pdo->prepare($updateWarehouse);
            $stmt2->execute([':qty' => $item['qty'], ':p_name' => $item['name']]);
        }
 
        echo json_encode(['success' => true, 'orderId' => $orderId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'بيانات الطلب ناقصة']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
 