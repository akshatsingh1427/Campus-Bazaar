<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) session_start();

header('Content-Type: text/html; charset=UTF-8');

if(!isset($_SESSION['user_id'])){
    echo "<span style='color:#ff6b6b;'> Please login!</span>";
    exit;
}

$uid = intval($_SESSION['user_id']);
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

if($item_id <= 0){
    echo "<span style='color:#ff6b6b;'> Invalid item!</span>";
    exit;
}


$conn->begin_transaction();

try {
    
    $check_item = $conn->prepare("SELECT stock, title FROM items WHERE item_id = ? FOR UPDATE");
    $check_item->bind_param("i", $item_id);
    $check_item->execute();
    $item_result = $check_item->get_result();

    if($item_result->num_rows === 0){
        echo "<span style='color:#ff6b6b;'> Item not found!</span>";
        $conn->rollback();
        $check_item->close();
        exit;
    }

    $item = $item_result->fetch_assoc();
    $check_item->close();

    if($item['stock'] <= 0){
        echo "<span style='color:#ff6b6b;'> '{$item['title']}' is out of stock!</span>";
        $conn->rollback();
        exit;
    }

    
    $conn->query("INSERT IGNORE INTO cart (user_id) VALUES ($uid)");
    
    
    $cart_res = $conn->query("SELECT cart_id FROM cart WHERE user_id=$uid");
    if(!$cart_res || $cart_res->num_rows === 0){
        echo "<span style='color:#ff6b6b;'> Cart error!</span>";
        $conn->rollback();
        exit;
    }
    
    $cart = $cart_res->fetch_assoc();
    $cart_id = $cart['cart_id'];

   
    $check_cart = $conn->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND item_id = ?");
    $check_cart->bind_param("ii", $cart_id, $item_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();

    if($cart_result->num_rows > 0){
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + 1;
        
        
        if($new_quantity > $item['stock']){
            echo "<span style='color:#ff9a9e;'> Only {$item['stock']} '{$item['title']}' available in stock!</span>";
            $conn->rollback();
            $check_cart->close();
            exit;
        }
        
        
        $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND item_id = ?");
        $update_stmt->bind_param("iii", $new_quantity, $cart_id, $item_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        
        if(1 > $item['stock']){
            echo "<span style='color:#ff9a9e;'> '{$item['title']}' is out of stock!</span>";
            $conn->rollback();
            $check_cart->close();
            exit;
        }
        
        $insert_stmt = $conn->prepare("INSERT INTO cart_items (cart_id, item_id, quantity) VALUES (?, ?, 1)");
        $insert_stmt->bind_param("ii", $cart_id, $item_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    $check_cart->close()

    
    $conn->commit();
    
    echo "<span style='color:#43e97b;'> '{$item['title']}' added to cart!</span>";

} catch (Exception $e) {
   
    $conn->rollback();
    echo "<span style='color:#ff6b6b;'>Error: " . htmlspecialchars($e->getMessage()) . "</span>";
    error_log("Cart Error: " . $e->getMessage());
}
?>
