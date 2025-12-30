<?php 

ob_start();

include 'db_connect.php'; 
if (session_status() == PHP_SESSION_NONE) session_start();
include 'header.php'; 


$uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$successMsg = "";
$showPaymentConfirmation = false;
$showIndividualConfirmation = false;
$total = 0;
$errorMsg = "";
$selectedSellerUPI = "";
$individualPaymentItem = null;
$paymentType = "";


if($uid > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if(isset($_POST['update_quantity'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $action = $_POST['action'];
        
        
        $stmt = $conn->prepare("SELECT ci.quantity, i.stock, i.title 
                               FROM cart_items ci 
                               JOIN items i ON ci.item_id = i.item_id 
                               WHERE ci.cart_item_id = ?");
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $current_qty = $item['quantity'];
            $available_stock = $item['stock'];
            
            if($action == 'increase') {
                $new_qty = $current_qty + 1;
                if($new_qty > $available_stock) {
                    $errorMsg = " Only {$available_stock} '{$item['title']}' available in stock!";
                } else {
                    $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                    $update_stmt->bind_param("ii", $new_qty, $cart_item_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            } elseif($action == 'decrease' && $current_qty > 1) {
                $new_qty = $current_qty - 1;
                $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
                $update_stmt->bind_param("ii", $new_qty, $cart_item_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        $stmt->close();
        
        
        ob_end_clean();
        header("Location: cart.php");
        exit;
    }

    
    if(isset($_POST['remove_item'])){
        $cart_item_id = intval($_POST['cart_item_id']);
        $delete_stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
        $delete_stmt->bind_param("i", $cart_item_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        
        ob_end_clean();
        header("Location: cart.php");
        exit;
    }

    
    if(isset($_POST['pay_individual'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $showIndividualConfirmation = true;
        $individualPaymentItem = $cart_item_id;
        $paymentType = 'individual';
    }

    
    if(isset($_POST['confirm_individual_payment'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        
        
        $stmt = $conn->prepare("SELECT ci.quantity, ci.item_id, i.price, i.stock, i.upi_id, i.title 
                               FROM cart_items ci 
                               JOIN items i ON ci.item_id = i.item_id 
                               WHERE ci.cart_item_id = ?");
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $amount = $item['price'] * $item['quantity'];
            $seller_upi = $item['upi_id'];
            
           
            $user_res = $conn->query("SELECT wallet FROM users WHERE user_id=$uid");
            if($user_res && $user_res->num_rows > 0) {
                $user = $user_res->fetch_assoc();
                $wallet_balance = $user['wallet'];

                if($wallet_balance >= $amount) {
                    $conn->begin_transaction();
                    try {
                        
                        $new_wallet = $wallet_balance - $amount;
                        $conn->query("UPDATE users SET wallet=$new_wallet WHERE user_id=$uid");
                        $_SESSION['wallet'] = $new_wallet;

                        
                        $new_stock = $item['stock'] - $item['quantity'];
                        $conn->query("UPDATE items SET stock=$new_stock WHERE item_id={$item['item_id']}");

                        
                        if($new_stock <= 0) {
                            $conn->query("DELETE FROM items WHERE item_id={$item['item_id']}");
                        }

                        
                        $conn->query("DELETE FROM cart_items WHERE cart_item_id=$cart_item_id");

                        $conn->commit();
                        $successMsg = "Payment successful! ₹$amount paid to $seller_upi for '{$item['title']}'";
                        $showIndividualConfirmation = false;
                    } catch(Exception $e) {
                        $conn->rollback();
                        $errorMsg = " Payment failed: " . $e->getMessage();
                    }
                } else {
                    $errorMsg = " Not enough balance in wallet!";
                }
            }
        }
        $stmt->close();
    }

    
    if(isset($_POST['confirm_bulk_payment'])){
        
        if(isset($_POST['seller_upi']) && !empty($_POST['seller_upi'])) {
            $seller_upi = $conn->real_escape_string($_POST['seller_upi']);
            
            $user_res = $conn->query("SELECT wallet FROM users WHERE user_id=$uid");
            if($user_res && $user_res->num_rows > 0){
                $user = $user_res->fetch_assoc();
                $wallet_balance = $user['wallet'];

                $cart_res = $conn->query("SELECT cart_id FROM cart WHERE user_id=$uid");
                if($cart_res && $cart_res->num_rows > 0){
                    $cart = $cart_res->fetch_assoc();
                    $cid = $cart['cart_id'];

                    $sql = "SELECT items.item_id, items.title, items.price, items.stock, cart_items.quantity, items.upi_id
                            FROM cart_items 
                            JOIN items ON items.item_id=cart_items.item_id
                            WHERE cart_id=$cid AND items.upi_id='$seller_upi'";
                    $result = $conn->query($sql);
                    $total = 0;

                    if($result && $result->num_rows > 0){
                        $cart_items = [];
                        while($row = $result->fetch_assoc()){
                            $sub = $row['price'] * $row['quantity'];
                            $total += $sub;
                            $cart_items[] = $row;
                        }

                        if($wallet_balance < $total){
                            $errorMsg = " Not enough balance in wallet! Add money first.";
                        } else {
                            $conn->begin_transaction();
                            try {
                                $new_wallet = $wallet_balance - $total;
                                $conn->query("UPDATE users SET wallet=$new_wallet WHERE user_id=$uid");
                                $_SESSION['wallet'] = $new_wallet;

                                foreach($cart_items as $item){
                                    $new_stock = max(0, $item['stock'] - $item['quantity']);
                                    $conn->query("UPDATE items SET stock=$new_stock WHERE item_id={$item['item_id']}");
                                }

                                $conn->query("DELETE FROM cart_items WHERE cart_id=$cid AND item_id IN (SELECT item_id FROM items WHERE upi_id='$seller_upi')");

                                $conn->commit();
                                $successMsg = " Payment successful! ₹$total paid to $seller_upi.";
                                $showPaymentConfirmation = false;
                            } catch(Exception $e){
                                $conn->rollback();
                                $errorMsg = " Error during payment: ".$e->getMessage();
                            }
                        }
                    } else {
                        $errorMsg = " No items found for the selected seller!";
                    }
                }
            }
        } else {
            $errorMsg = " Please select a seller to pay!";
        }
    }

   
    if(isset($_POST['cancel_payment'])){
        $showPaymentConfirmation = false;
        $showIndividualConfirmation = false;
    }
}
?>

<style>

:root {
    --primary: #667eea;
    --primary-glow: rgba(102, 126, 234, 0.4);
    --success: #43e97b;
    --success-glow: rgba(67, 233, 123, 0.4);
    --danger: #ff6b6b;
    --danger-glow: rgba(255, 107, 107, 0.4);
    --accent: #48C6EF;
    --accent-glow: rgba(72, 198, 239, 0.4);
    --gold: #FFD700;
    --text-primary: #ffffff;
    --text-secondary: rgba(255, 255, 255, 0.85);
    --text-muted: rgba(255, 255, 255, 0.6);
    --glass-bg: rgba(255, 255, 255, 0.08);
    --glass-border: rgba(255, 255, 255, 0.15);
    --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    --elevation-1: 0 4px 20px rgba(0, 0, 0, 0.15);
    --elevation-2: 0 12px 40px rgba(0, 0, 0, 0.25);
    --elevation-3: 0 20px 60px rgba(0, 0, 0, 0.3);
    --border-radius: 20px;
    --border-radius-sm: 12px;
    --border-radius-lg: 28px;
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: 
        
        linear-gradient(125deg, #0f0c29 0%, #302b63 50%, #24243e 100%),
       
        radial-gradient(circle at 20% 80%, var(--primary-glow) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, var(--success-glow) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, var(--accent-glow) 0%, transparent 50%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
    color: var(--text-primary);
    line-height: 1.6;
    position: relative;
    overflow-x: hidden;
}


.background-elements {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
    overflow: hidden;
}

.floating-orb {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
    animation: float 8s ease-in-out infinite;
    filter: blur(40px);
}

.floating-orb:nth-child(1) {
    top: 20%;
    left: 10%;
    width: 300px;
    height: 300px;
    animation-delay: 0s;
    background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
}

.floating-orb:nth-child(2) {
    top: 60%;
    right: 15%;
    width: 400px;
    height: 400px;
    animation-delay: 2s;
    background: radial-gradient(circle, var(--success-glow) 0%, transparent 70%);
}

.floating-orb:nth-child(3) {
    bottom: 10%;
    left: 40%;
    width: 350px;
    height: 350px;
    animation-delay: 4s;
    background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
}

@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg) scale(1);
        opacity: 0.6;
    }
    33% { 
        transform: translateY(-30px) rotate(120deg) scale(1.1);
        opacity: 0.8;
    }
    66% { 
        transform: translateY(20px) rotate(240deg) scale(0.9);
        opacity: 0.7;
    }
}


.container {
    max-width: 1200px;
    margin: 80px auto 40px;
    padding: 0 2rem;
    position: relative;
    z-index: 1;
}


.cart-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.cart-header h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #fff 0%, var(--gold) 50%, #fff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -1px;
    text-shadow: 0 4px 30px rgba(255, 215, 0, 0.3);
    position: relative;
    display: inline-block;
}

.cart-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    font-weight: 400;
    margin-top: 0.5rem;
    opacity: 0.9;
}


.cart-items {
    display: grid;
    gap: 2rem;
    margin-bottom: 3rem;
}

.cart-item {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.05) 100%);
    backdrop-filter: blur(25px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: var(--border-radius-lg);
    padding: 2.5rem;
    box-shadow: var(--elevation-2);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.cart-item::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--accent), var(--success));
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
}

.cart-item::after {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.1), 
        transparent);
    transition: left 0.6s ease;
}

.cart-item:hover::after {
    left: 100%;
}

.cart-item:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--elevation-3);
    border-color: rgba(255, 255, 255, 0.25);
}


.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.item-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-primary);
    flex: 1;
    line-height: 1.3;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.item-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--success);
    background: linear-gradient(135deg, var(--success), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    white-space: nowrap;
}


.item-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
}

.detail-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.detail-icon {
    font-size: 1.8rem;
    opacity: 0.9;
}

.detail-content {
    flex: 1;
}

.detail-label {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
}


.upi-badge {
    background: linear-gradient(135deg, 
        rgba(102, 126, 234, 0.2), 
        rgba(72, 198, 239, 0.2));
    border: 1px solid rgba(102, 126, 234, 0.3);
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1rem 0;
    backdrop-filter: blur(10px);
}


.quantity-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 2rem 0;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.quantity-btn {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1.5rem;
    font-weight: 300;
    backdrop-filter: blur(10px);
}

.quantity-btn:hover:not(:disabled) {
    background: var(--primary);
    transform: scale(1.15);
    box-shadow: 0 6px 20px var(--primary-glow);
}

.quantity-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    transform: none;
}

.quantity-display {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
    min-width: 60px;
    text-align: center;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.subtotal {
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--success), var(--gold));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


.item-actions {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.btn {
    padding: 1.2rem 2.5rem;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.3), 
        transparent);
    transition: left 0.6s ease;
}

.btn:hover::before {
    left: 100%;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger), #ff5252);
    color: white;
    box-shadow: 0 6px 20px var(--danger-glow);
}

.btn-danger:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 30px var(--danger-glow);
}

.btn-success {
    background: linear-gradient(135deg, var(--success), #3bd46a);
    color: white;
    box-shadow: 0 6px 20px var(--success-glow);
}

.btn-success:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 30px var(--success-glow);
}


.cart-summary {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.05) 100%);
    backdrop-filter: blur(30px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: var(--border-radius-lg);
    padding: 3rem;
    margin-bottom: 3rem;
    box-shadow: var(--elevation-2);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cart-summary::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--success), var(--accent), var(--primary));
}

.total-amount {
    font-size: 3.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, var(--success), var(--gold), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
    text-shadow: 0 4px 30px rgba(67, 233, 123, 0.3);
}


.payment-modal {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.12) 0%, 
        rgba(255, 255, 255, 0.06) 100%);
    backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-lg);
    padding: 3.5rem;
    margin: 3rem 0;
    box-shadow: var(--elevation-3);
    animation: modalSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.payment-modal::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--success), var(--accent));
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    text-align: center;
    margin-bottom: 3rem;
}

.modal-header h3 {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #fff, var(--gold));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.modal-header p {
    color: var(--text-secondary);
    font-size: 1.2rem;
    font-weight: 400;
}


.seller-selection {
    margin: 2.5rem 0;
}

.seller-option {
    background: rgba(255, 255, 255, 0.07);
    border: 2px solid transparent;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin: 1.5rem 0;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    backdrop-filter: blur(10px);
}

.seller-option:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateX(10px);
}

.seller-option.selected {
    background: rgba(67, 233, 123, 0.15);
    border-color: rgba(67, 233, 123, 0.5);
    transform: translateX(20px);
}

.seller-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.4rem;
    box-shadow: 0 4px 15px var(--primary-glow);
}

.seller-info {
    flex: 1;
}

.seller-name {
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.seller-upi {
    color: var(--text-secondary);
    font-size: 1rem;
    font-family: 'Monaco', 'Consolas', monospace;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius-sm);
    display: inline-block;
}


.modal-actions {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-top: 3rem;
    flex-wrap: wrap;
}

.btn-confirm {
    background: linear-gradient(135deg, var(--success), #3bd46a);
    padding: 1.5rem 3rem;
    font-size: 1.2rem;
    font-weight: 800;
    box-shadow: 0 8px 25px var(--success-glow);
    border: none;
    cursor: pointer;
}

.btn-cancel {
    background: linear-gradient(135deg, var(--danger), #ff5252);
    padding: 1.5rem 3rem;
    font-size: 1.2rem;
    font-weight: 800;
    box-shadow: 0 8px 25px var(--danger-glow);
    border: none;
    cursor: pointer;
}

.btn-confirm:hover, .btn-cancel:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
}


.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
    margin: 2rem 0;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.empty-state h3 {
    font-size: 2.2rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
    font-weight: 700;
}

.empty-state p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.8;
}


.message {
    padding: 2rem;
    border-radius: var(--border-radius);
    margin: 2rem 0;
    font-weight: 700;
    text-align: center;
    animation: slideInUp 0.5s ease;
    backdrop-filter: blur(20px);
    border: 1px solid;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-success {
    background: rgba(67, 233, 123, 0.2);
    border-color: rgba(67, 233, 123, 0.4);
    color: #c8f7d8;
    box-shadow: 0 4px 20px rgba(67, 233, 123, 0.2);
}

.message-error {
    background: rgba(255, 107, 107, 0.2);
    border-color: rgba(255, 107, 107, 0.4);
    color: #ffd1d1;
    box-shadow: 0 4px 20px rgba(255, 107, 107, 0.2);
}


.footer {
    text-align: center;
    padding: 3rem 0 2rem;
    color: var(--text-muted);
    font-size: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 4rem;
    backdrop-filter: blur(10px);
}


@media (max-width: 768px) {
    .container {
        margin: 60px auto 30px;
        padding: 0 1rem;
    }
    
    .cart-header h1 {
        font-size: 2.8rem;
    }
    
    .cart-item {
        padding: 2rem;
    }
    
    .item-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .quantity-section {
        flex-direction: column;
        gap: 1.5rem;
        text-align: center;
    }
    
    .item-actions {
        justify-content: center;
    }
    
    .btn {
        flex: 1;
        justify-content: center;
        min-width: 160px;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .payment-modal {
        padding: 2.5rem 1.5rem;
    }
    
    .total-amount {
        font-size: 2.8rem;
    }
    
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .empty-icon {
        font-size: 4rem;
    }
}

@media (max-width: 480px) {
    .cart-header h1 {
        font-size: 2.2rem;
    }
    
    .item-title {
        font-size: 1.3rem;
    }
    
    .empty-icon {
        font-size: 3.5rem;
    }
    
    .empty-state h3 {
        font-size: 1.8rem;
    }
}
</style>


<div class="background-elements">
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
</div>

<div class="container">
    
    <div class="cart-header">
        <h1>🛒 Campus Cart</h1>
        <p class="cart-subtitle">Buy & Sell study materials, stationery, electronics, and more — securely and easily.</p>
        <p class="cart-subtitle">Connect with your campus community and trade items hassle-free!</p>
    </div>

    <?php if(!$uid): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Authentication Required</h3>
            <p>Please login to access your campus shopping experience</p>
            <button onclick="window.location.href='login.php'" class="btn btn-success">
                <span> Sign In to Continue</span>
            </button>
        </div>
    <?php else: ?>
        <?php
        
        $cart_res = $conn->query("SELECT cart_id FROM cart WHERE user_id=$uid");
        if($cart_res && $cart_res->num_rows > 0){
            $cart = $cart_res->fetch_assoc();
            $cid = $cart['cart_id'];
            $sql = "SELECT cart_items.cart_item_id, items.title, items.price, cart_items.quantity, items.upi_id, items.stock
                    FROM cart_items 
                    JOIN items ON items.item_id=cart_items.item_id
                    WHERE cart_id=$cid";
            $result = $conn->query($sql);

            $total = 0;
            $sellerUPIs = [];
            
            if($result && $result->num_rows > 0){
                echo '<div class="cart-items">';
                
                while($row = $result->fetch_assoc()){
                    $sub = $row['price'] * $row['quantity'];
                    $total += $sub;
                    $sellerUPIs[$row['upi_id']] = $row['upi_id'];
                    
                    echo "<div class='cart-item'>
                            <div class='item-header'>
                                <h3 class='item-title'>{$row['title']}</h3>
                                <div class='item-price'>₹{$row['price']}</div>
                            </div>
                            
                            <div class='item-details'>
                                <div class='detail-card'>
                                    <div class='detail-icon'></div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Available Stock</div>
                                        <div class='detail-value'>{$row['stock']} units</div>
                                    </div>
                                </div>
                                <div class='detail-card'>
                                    <div class='detail-icon'></div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Quantity</div>
                                        <div class='detail-value'>{$row['quantity']}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='upi-badge'>
                                <div class='detail-icon'></div>
                                <div class='detail-content'>
                                    <div class='detail-label'>Seller UPI ID</div>
                                    <div class='detail-value'>{$row['upi_id']}</div>
                                </div>
                            </div>
                            
                            <div class='quantity-section'>
                                <div class='quantity-controls'>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                                        <input type='hidden' name='action' value='decrease'>
                                        <button type='submit' name='update_quantity' class='quantity-btn' " . ($row['quantity'] <= 1 ? 'disabled' : '') . ">-</button>
                                    </form>
                                    
                                    <span class='quantity-display'>{$row['quantity']}</span>
                                    
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                                        <input type='hidden' name='action' value='increase'>
                                        <button type='submit' name='update_quantity' class='quantity-btn' " . ($row['quantity'] >= $row['stock'] ? 'disabled' : '') . ">+</button>
                                    </form>
                                </div>
                                
                                <div class='subtotal'>₹{$sub}</div>
                            </div>
                            
                            <div class='item-actions'>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                                    <button type='submit' name='remove_item' class='btn btn-danger'>
                                        <span></span>
                                        <span>Remove Item</span>
                                    </button>
                                </form>
                                
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='cart_item_id' value='{$row['cart_item_id']}'>
                                    <button type='submit' name='pay_individual' class='btn btn-success'>
                                        <span></span>
                                        <span>Pay ₹{$sub}</span>
                                    </button>
                                </form>
                            </div>
                          </div>";
                }
                
                echo '</div>';
                
               
                echo "<div class='cart-summary'>
                        <div class='total-amount'>₹{$total}</div>
                        <p style='color: var(--text-secondary); margin-bottom: 2rem; font-size: 1.2rem;'>Total amount for all items in your cart</p>
                      </div>";

            } else {
                echo '<div class="empty-state">
                        <div class="empty-icon">🛒</div>
                        <h3>Your Campus Cart is Empty</h3>
                        <p>Browse items from your campus community and add them to your cart</p>
                        <button onclick="window.location.href=\'catalog.php\'" class="btn btn-success">
                            <span> Browse Campus Items</span>
                        </button>
                      </div>';
            }

        } else {
            echo '<div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h3>Your Campus Cart is Empty</h3>
                    <p>Browse items from your campus community and add them to your cart</p>
                    <button onclick="window.location.href=\'catalog.php\'" class="btn btn-success">
                        <span> Browse Campus Items</span>
                    </button>
                  </div>';
        }

        
        if(!empty($errorMsg)) {
            echo "<div class='message message-error'>{$errorMsg}</div>";
        }

        
        echo '<div id="payment-section">';

        
        if($showIndividualConfirmation && $individualPaymentItem) {
            
            $stmt = $conn->prepare("SELECT ci.quantity, i.price, i.upi_id, i.title 
                                   FROM cart_items ci 
                                   JOIN items i ON ci.item_id = i.item_id 
                                   WHERE ci.cart_item_id = ?");
            $stmt->bind_param("i", $individualPaymentItem);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                $item = $result->fetch_assoc();
                $amount = $item['price'] * $item['quantity'];
                $seller_upi = $item['upi_id'];
                $seller_initials = strtoupper(substr($seller_upi, 0, 2));
                
                echo "
                <div class='payment-modal'>
                    <div class='modal-header'>
                        <h3>Confirm Campus Payment</h3>
                        <p>Review your purchase details</p>
                    </div>
                    
                    <div class='item-details'>
                        <div class='detail-card'>
                            <div class='detail-icon'></div>
                            <div class='detail-content'>
                                <div class='detail-label'>Item</div>
                                <div class='detail-value'>{$item['title']}</div>
                            </div>
                        </div>
                        <div class='detail-card'>
                            <div class='detail-icon'></div>
                            <div class='detail-content'>
                                <div class='detail-label'>Amount</div>
                                <div class='detail-value'>₹{$amount}</div>
                            </div>
                        </div>
                        <div class='detail-card'>
                            <div class='detail-icon'></div>
                            <div class='detail-content'>
                                <div class='detail-label'>Quantity</div>
                                <div class='detail-value'>{$item['quantity']}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class='seller-selection'>
                        <div class='seller-option selected'>
                            <div class='seller-avatar'>{$seller_initials}</div>
                            <div class='seller-info'>
                                <div class='seller-name'>Campus Seller</div>
                                <div class='seller-upi'>{$seller_upi}</div>
                            </div>
                        </div>
                    </div>
                    
                    <form method='POST' class='modal-actions'>
                        <input type='hidden' name='cart_item_id' value='{$individualPaymentItem}'>
                        <button type='submit' name='confirm_individual_payment' class='btn-confirm'>
                            <span></span>
                            <span>Confirm Payment</span>
                        </button>
                        <button type='submit' name='cancel_payment' class='btn-cancel'>
                            <span></span>
                            <span>Cancel</span>
                        </button>
                    </form>
                </div>";
            }
            $stmt->close();
        }

        echo '</div>'; 

       
        if(!empty($successMsg)) {
            echo "<div class='message message-success'>{$successMsg}</div>";
            echo "<div style='text-align: center; margin-top: 3rem;'>
                    <button onclick=\"window.location.href='catalog.php'\" class='btn btn-success'>
                        <span></span>
                        <span>Continue Campus Shopping</span>
                    </button>
                  </div>";
        }
        ?>
    <?php endif; ?>
</div>

<div class="footer">
    &copy; 2025 Campus Bazaar • Connect, Trade, Succeed
</div>

<script>
function selectSellerUPI(upi) {
    
    document.querySelectorAll('.seller-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    
    event.currentTarget.classList.add('selected');
    
    
    document.getElementById('upi_' + upi).checked = true;
}


<?php if($showIndividualConfirmation): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.getElementById('payment-section').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }, 300);
});
<?php endif; ?>


<?php if(!empty($successMsg)): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const successMessage = document.querySelector('.message-success');
        if (successMessage) {
            successMessage.scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }
    }, 500);
});
<?php endif; ?>
</script>