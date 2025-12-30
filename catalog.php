<?php
include 'db_connect.php';
session_start();
include 'header.php';
?>


<style>

:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --accent-gradient: linear-gradient(135deg, #48C6EF 0%, #6F86D6 100%);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.15);
    --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

body {
    background: url('images/catalog_bg.jpg') no-repeat center center/cover;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    min-height: 100vh;
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.4) 100%);
    z-index: -1;
}

.container {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    width: 90%;
    max-width: 1200px;
    margin: 50px auto;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: var(--glass-shadow);
    animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.reward-card {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
    backdrop-filter: blur(15px);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 2.5rem;
    box-shadow: var(--glass-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.reward-card::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.reward-card h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.reward-card p {
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.search-bar {
    text-align: center;
    margin-bottom: 2.5rem;
}

.search-bar form {
    display: flex;
    justify-content: center;
    gap: 1rem;
    max-width: 600px;
    margin: 0 auto;
}

.search-bar input {
    flex: 1;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    outline: none;
    transition: all 0.3s ease;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
}

.search-bar input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-bar input:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.search-bar button {
    padding: 1rem 2rem;
    border: none;
    background: var(--primary-gradient);
    color: white;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    backdrop-filter: blur(10px);
}

.search-bar button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 15px;
    padding: 1.8rem;
    box-shadow: var(--glass-shadow);
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    color: white;
}

.card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--accent-gradient);
}

.card::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    z-index: -1;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    border-color: rgba(255, 255, 255, 0.3);
}

.card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.card p {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.5;
    margin-bottom: 0.8rem;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.card p b {
    color: #fff;
}

.upi-info {
    background: linear-gradient(135deg, rgba(240, 147, 251, 0.3) 0%, rgba(245, 87, 108, 0.3) 100%);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    display: inline-block;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.add-cart-btn {
    background: var(--secondary-gradient);
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    margin-top: 0.5rem;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.add-cart-btn:disabled {
    background: rgba(160, 174, 192, 0.5);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.add-cart-btn:disabled:hover {
    transform: none;
    box-shadow: none;
}

.add-cart-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(240, 147, 251, 0.4);
}

.popup-msg {
    display: block;
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.9rem;
    margin-top: 0.5rem;
    font-weight: 600;
}

.success-msg {
    color: #90EE90;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.error-msg {
    color: #FFB6C1;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

#goto-cart {
    text-align: center;
    margin: 3rem 0 2rem 0;
}

#goto-cart button {
    background: var(--accent-gradient);
    color: white;
    border: none;
    padding: 1.2rem 2.5rem;
    font-size: 1.1rem;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    box-shadow: 0 8px 25px rgba(72, 198, 239, 0.3);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

#goto-cart button:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 30px rgba(72, 198, 239, 0.4);
}

footer {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    text-align: center;
    padding: 2rem;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.2);
}

@media (max-width: 768px) {
    .container {
        width: 95%;
        margin: 30px auto;
        padding: 1.5rem;
    }
    
    .reward-card {
        padding: 1.5rem;
    }
    
    .reward-card h2 {
        font-size: 1.6rem;
    }
    
    .search-bar form {
        flex-direction: column;
    }
    
    .grid-container {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .card {
        padding: 1.5rem;
    }
}
</style>

<div class="container">
    <?php if(isset($_SESSION['name'])): ?>
        <div class="reward-card">
            <h2> Welcome, <?php echo $_SESSION['name']; ?></h2>
            <p>Explore the Campus Bazaar — your one-stop marketplace for books, electronics, and more!</p>
        </div>
    <?php endif; ?>

    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder=" Search items by title or category" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="grid-container">
    <?php
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    
    $query = "SELECT * FROM items WHERE stock > 0 AND (title LIKE '%$search%' OR category LIKE '%$search%')";
    $result = $conn->query($query);

    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $isOutOfStock = $row['stock'] == 0;
            $buttonText = $isOutOfStock ? 'Out of Stock' : ' Add to Cart';
            $buttonDisabled = $isOutOfStock ? 'disabled' : '';
            
            echo "<div class='card' id='item-{$row['item_id']}'>
                    <h3>{$row['title']}</h3>
                    <p>{$row['description']}</p>
                    <p><b> Price:</b> ₹{$row['price']}</p>
                    <p><b> Stock:</b> {$row['stock']}</p>
                    <div class='upi-info'>
                        <b> UPI ID:</b> {$row['upi_id']}
                    </div>";
            if(isset($_SESSION['role']) && $_SESSION['role'] === 'student'){
                echo "<button class='add-cart-btn' data-id='{$row['item_id']}' {$buttonDisabled}>{$buttonText}</button>";
                echo "<span class='popup-msg' id='popup-{$row['item_id']}'></span>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='text-align: center; color: rgba(255,255,255,0.8); grid-column: 1 / -1;'>No items available matching your search.</p>";
    }
    ?>
    </div>
</div>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
<div id="goto-cart">
    <button data-page="cart.php"> Go to Cart</button>
</div>
<?php endif; ?>

<script>

document.querySelectorAll('.add-cart-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        if(this.disabled) return;
        
        const itemId = this.getAttribute('data-id');
        const popup = document.getElementById('popup-' + itemId);
        const button = this;

        
        button.disabled = true;
        button.innerHTML = ' Adding...';

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'item_id=' + itemId
        })
        .then(response => response.text())
        .then(data => {
           
            const message = data.replace(/<[^>]*>/g, '').trim();
            
            if(data.includes('') || data.includes('#43e97b') || message.includes('added to cart')) {
               
                popup.innerText = " Added to cart!";
                popup.className = 'popup-msg success-msg';
                popup.style.opacity = '1';
                button.innerHTML = ' Added!';
                
                setTimeout(() => { 
                    popup.style.opacity = '0';
                    button.innerHTML = ' Add to Cart';
                    button.disabled = false;
                }, 2000);
                
            } else if(data.includes('') || data.includes('#ff6b6b') || message.includes('out of stock')) {
                
                popup.innerText = " Item out of stock!";
                popup.className = 'popup-msg error-msg';
                popup.style.opacity = '1';
                button.innerHTML = 'Out of Stock';
                button.disabled = true;
                
                setTimeout(() => { 
                    popup.style.opacity = '0';
                }, 3000);
                
            } else if(data.includes('') || data.includes('#ff9a9e') || message.includes('Only') || message.includes('available')) {
                
                popup.innerText = " Stock limit reached!";
                popup.className = 'popup-msg error-msg';
                popup.style.opacity = '1';
                button.innerHTML = 'Max Reached';
                button.disabled = true;
                
                setTimeout(() => { 
                    popup.style.opacity = '0';
                }, 3000);
                
            } else if(message.includes('Please login')) {
                
                popup.innerText = " Please login first!";
                popup.className = 'popup-msg error-msg';
                popup.style.opacity = '1';
                button.innerHTML = ' Add to Cart';
                button.disabled = false;
                
                setTimeout(() => { 
                    popup.style.opacity = '0';
                }, 3000);
                
            } else {
                
                popup.innerText = " Error adding item";
                popup.className = 'popup-msg error-msg';
                popup.style.opacity = '1';
                button.innerHTML = ' Add to Cart';
                button.disabled = false;
                
                setTimeout(() => { 
                    popup.style.opacity = '0';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            popup.innerText = " Network error!";
            popup.className = 'popup-msg error-msg';
            popup.style.opacity = '1';
            button.innerHTML = ' Add to Cart';
            button.disabled = false;
            
            setTimeout(() => { 
                popup.style.opacity = '0';
            }, 3000);
        });
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const navButtons = document.querySelectorAll('button[data-page]');
    
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            if (page) {
                window.location.href = page;
            }
        });
    });
});
</script>

<footer>&copy; 2025 Campus Bazaar</footer>