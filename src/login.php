<?php
include 'config.php';
session_start();

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $password = $_POST['password'];

   $select = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND password = '$password'") or die('query failed');

   if(mysqli_num_rows($select) > 0){
      $row = mysqli_fetch_assoc($select);
      $_SESSION['user_id'] = $row['user_id'];
      $_SESSION['role'] = $row['role'];
      
      if($row['role'] == 'Admin'){
         echo "أهلاً يا أدمن!"; // مؤقتاً للتجربة
      } else {
         echo "أهلاً يا عميل!"; // مؤقتاً للتجربة
      }
   } else {
      echo "الإيميل أو الباسورد غلط!";
   }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<body>
   <form action="" method="post">
      <h3>تسجيل الدخول</h3>
      <input type="email" name="email" placeholder="أدخل الإيميل" required>
      <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
      <input type="submit" name="submit" value="دخول">
   </form>
</body>
</html>