<?php
include 'db_connect.php';
session_start();
include 'header.php';
?>


<style>

:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.15);
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('images/checkout-bg.jpg') no-repeat center center/cover;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.3) 100%);
    z-index: -1;
}

.container {
    max-width: 1000px;
    margin: 80px auto;
    padding: 2rem;
    position: relative;
    z-index: 1;
}

h2 {
    text-align: center;
    color: white;
    font-size: 2.5rem;
    margin-bottom: 2.5rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    background: linear-gradient(45deg, #fff, #e3f2fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
}

h3 {
    color: white;
    text-align: center;
    font-size: 1.8rem;
    margin: 2rem 0;
    font-weight: 600;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    animation: slideIn 0.6s ease;
    transition: all 0.3s ease;
    color: white;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.card h3 {
    color: white;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    text-align: left;
    font-weight: 600;
}

.card p {
    margin: 0.5rem 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
}

form {
    text-align: center;
    margin: 2rem 0;
}

button[type="submit"] {
    background: var(--success-gradient);
    color: white;
    border: none;
    padding: 1.2rem 3rem;
    border-radius: 12px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3);
    position: relative;
    overflow: hidden;
}

button[type="submit"]::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

button[type="submit"]:hover::before {
    left: 100%;
}

button[type="submit"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(67, 233, 123, 0.4);
}

p {
    text-align: center;
    color: white;
    font-size: 1.1rem;
    margin: 1rem 0;
}

p[style*="color:red"] {
    background: rgba(231, 76, 60, 0.2);
    border: 1px solid rgba(231, 76, 60, 0.4);
    color: #ffeaea !important;
    padding: 1rem;
    border-radius: 12px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

p[style*="color:green"] {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.4);
    color: #d3ffe0 !important;
    padding: 1rem;
    border-radius: 12px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

footer {
    text-align: center;
    color: white;
    padding: 2rem;
    margin-top: 3rem;
    background: rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255,255,255,0.1);
}

@media (max-width: 768px) {
    .container {
        margin: 60px 1rem;
        padding: 1rem;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    h3 {
        font-size: 1.5rem;
    }
    
    .grid-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .card {
        padding: 1.2rem;
    }
    
    button[type="submit"] {
        width: 100%;
        padding: 1rem 2rem;
    }
}
</style>

<div class="container">
    <h2> Checkout</h2>

    <?php
    $uid = $_SESSION['user_id'];
    $cart = $conn->query("SELECT cart_id FROM cart WHERE user_id=$uid")->fetch_assoc();

    if($cart){
        $cid = $cart['cart_id'];
        $items = $conn->query("SELECT items.item_id, items.title, items.price, items.stock, cart_items.quantity
                               FROM cart_items 
                               JOIN items ON items.item_id=cart_items.item_id
                               WHERE cart_id=$cid");

        if($items->num_rows == 0){
            echo "<p> Your cart is empty.</p>";
        } else {
            $total = 0;
            $can_checkout = true;

            echo "<div class='grid-container'>";
            $cart_items = [];
            while($row = $items->fetch_assoc()){
                $subtotal = $row['price'] * $row['quantity'];
                $total += $subtotal;
                if($row['stock'] < $row['quantity']) $can_checkout = false
                $cart_items[] = $row;

                echo "<div class='card'>
                        <h3> {$row['title']}</h3>
                        <p> Qty: {$row['quantity']}</p>
                        <p> Price: ₹{$subtotal}</p>
                        <p> Stock Available: {$row['stock']}</p>
                      </div>";
            }
            echo "</div>";
            echo "<h3> Total Amount: ₹$total</h3>";

            if(!$can_checkout){
                echo "<p style='color:red;'> Some items exceed available stock. Please adjust quantities.</p>";
            } else {
                if(isset($_POST['checkout'])){
                   
                    $conn->begin_transaction();
                    try {
                        
                        $conn->query("INSERT INTO orders (user_id, total) VALUES ($uid, $total)");
                        $order_id = $conn->insert_id;

                        
                        foreach($cart_items as $item){
                            $conn->query("INSERT INTO order_items (order_id, item_id, quantity, price)
                                          VALUES ($order_id, {$item['item_id']}, {$item['quantity']}, {$item['price']})");

                            
                            $new_stock = $item['stock'] - $item['quantity'];
                            $conn->query("UPDATE items SET stock=$new_stock WHERE item_id={$item['item_id']}");
                        }

                        
                        $conn->query("DELETE FROM cart_items WHERE cart_id=$cid");

                        $conn->commit();
                        echo "<p style='color:green;'> Checkout successful! Order #$order_id confirmed.</p>";
                    } catch(Exception $e){
                        $conn->rollback();
                        echo "<p style='color:red;'> Error during checkout: ".$e->getMessage()."</p>";
                    }
                }

                echo "<form method='POST'>
                        <button type='submit' name='checkout'> Confirm Checkout</button>
                      </form>";
            }
        }
    } else {
        echo "<p> Your cart is empty.</p>";
    }
    ?>
</div>

<footer>&copy; 2025 Campus Bazaar</footer>
