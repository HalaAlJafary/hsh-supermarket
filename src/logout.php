<?php
session_start();
session_destroy(); // إنهاء الجلسة ومسح بيانات المستخدم
header("Location: index.php"); // إعادته لصفحة الدخول
exit();
?>