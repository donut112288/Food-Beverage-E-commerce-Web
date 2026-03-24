<?php
@include 'config.php';

session_start();

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM `users` WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);
   if($rowCount > 0){
      if($row['userType'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');
      }elseif($row['userType'] == 'user'){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
      }else{
         $message[] = 'No user found!';
      }
   }else{
      $message[] = 'Incorrect email or password!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login Page</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/components.css">

</head>
<body>
<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
   
<section class="form-container">
   <form action="" method="POST">
   <img src="images/Logo.png" alt="Logo" class="logo">
      <h3>Login</h3>
      <input type="email" name="email" class="box" placeholder="Email" required>
      <input type="password" name="pass" class="box" placeholder="Password" required>
      <input type="submit" value="Sign In" class="btn" name="submit">
      <div class="or-container">
        <div class="or-line"></div>
        <span class="or-text">or</span>
        <div class="or-line"></div>
      </div>

      <div class="icons">
        <a href="#" class="fab fa-facebook"></a>
        <a href="#" class="fab fa-google"></a>
        <a href="#" class="fab fa-instagram"></a>
      </div>
      <p>Don't have an account? <a href="register.php">Sign Up</a></p>
   </form>
</section>

</body>
</html>