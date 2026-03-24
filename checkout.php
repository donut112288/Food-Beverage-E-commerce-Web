<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
};
if(isset($_POST['order'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' - '. $_POST['poscode'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      };
   };

   $total_products = implode(', ', $cart_products);

   $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if($cart_total == 0){
      $message[] = 'Your cart is empty';
   }elseif($order_query->rowCount() > 0){
      $message[] = 'Order placed already!';
   }else{
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      $message[] = 'Order placed successfully!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout Page</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<?php include 'header.php'; ?>
<section class="display-orders">
   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?= $fetch_cart_items['name']; ?> <span>(<?= '$'.$fetch_cart_items['price'].' x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty">Your cart is empty!</p>';
   }
   ?>
   <div class="grand-total">Grand Total : <span>$<?= $cart_grand_total; ?></span></div>
</section>

<section class="checkout-orders">
   <form action="" method="POST">
      <h3>place your order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Name:</span>
            <input type="text" name="name" placeholder="Name" class="box" required>
         </div>
         <div class="inputBox">
            <span>Number:</span>
            <input type="number" name="number" placeholder="Number" class="box" required>
         </div>
         <div class="inputBox">
            <span>Email:</span>
            <input type="email" name="email" placeholder="Email" class="box" required>
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash on delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="debit card">Debit Card</option>
               <option value="Tng">Tng</option>
               <option value="PayPal">PayPal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 01:</span>
            <input type="text" name="flat" placeholder="" class="box" required>
         </div>
         <div class="inputBox">
            <span>Address Line 02:</span>
            <input type="text" name="street" placeholder="" class="box" required>
         </div>
         <div class="inputBox">
            <span>Poscode:</span>
            <input type="number" min="0" name="poscode" placeholder="Poscode" class="box" required>
         </div>
         <div class="inputBox">
            <span>City:</span>
            <input type="text" name="city" placeholder="City" class="box" required>
         </div>
         <div class="inputBox">
            <span>State:</span>
            <input type="text" name="state" placeholder="State" class="box" required>
         </div>
         <div class="inputBox">
            <span>Country:</span>
            <input type="text" name="country" placeholder="Country" class="box" required>
         </div>
      </div>
      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1)?'':'disabled'; ?>" value="place order">
   </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>