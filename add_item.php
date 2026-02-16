<?php 
include 'db_connect.php'; 
if (session_status() == PHP_SESSION_NONE) session_start();
include 'header.php';


if(empty($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    echo "<p class='message error'> Access denied. Only admin can add items.</p>";
    exit;
}
?>

<style>

:root {
    --primary: #4DB6AC;
    --primary-glow: rgba(77, 182, 172, 0.4);
    --success: #26A69A;
    --success-glow: rgba(38, 166, 154, 0.4);
    --danger: #EF5350;
    --danger-glow: rgba(239, 83, 80, 0.4);
    --accent: #80CBC4;
    --accent-glow: rgba(128, 203, 196, 0.4);
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
        
        linear-gradient(125deg, #004D40 0%, #00796B 50%, #004D40 100%),
        
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

.page-content {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 120px;
    padding-bottom: 60px;
    min-height: calc(100vh - 200px);
}

.container {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.1) 0%, 
        rgba(255, 255, 255, 0.05) 100%);
    backdrop-filter: blur(25px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: var(--border-radius-lg);
    padding: 3rem;
    max-width: 500px;
    width: 90%;
    box-shadow: var(--elevation-2);
    animation: modalSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-align: center;
}

.container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--success), var(--accent));
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
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

h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #B2DFDB, #4DB6AC, #26A69A);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -1px;
    text-shadow: 0 4px 30px rgba(77, 182, 172, 0.3);
    position: relative;
    display: inline-block;
}

.container img {
    width: 80px;
    height: 80px;
    margin-bottom: 1rem;
    opacity: 0.9;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.2);
}

form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

label {
    text-align: left;
    color: var(--text-secondary);
    font-size: 1rem;
    margin-bottom: -0.5rem;
    font-weight: 600;
}

input, textarea {
    width: 100%;
    padding: 1.2rem 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius);
    background: rgba(255, 255, 255, 0.08);
    color: var(--text-primary);
    font-size: 1rem;
    outline: none;
    transition: var(--transition);
    font-weight: 600;
    backdrop-filter: blur(10px);
    box-sizing: border-box;
}

input::placeholder, textarea::placeholder {
    color: var(--text-muted);
    font-weight: 500;
}

input:focus, textarea:focus {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 30px var(--primary-glow);
    transform: translateY(-2px);
}

textarea {
    resize: none;
    height: 100px;
    font-family: inherit;
}

button {
    background: linear-gradient(135deg, var(--success), #00897B);
    color: white;
    border: none;
    padding: 1.3rem 2rem;
    border-radius: var(--border-radius);
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 8px 25px var(--success-glow);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
    margin-top: 0.5rem;
    font-family: inherit;
}

button::before {
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

button:hover::before {
    left: 100%;
}

button:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 15px 40px var(--success-glow);
}

.message {
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin: 1.5rem 0;
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

.success {
    background: rgba(38, 166, 154, 0.2);
    border-color: rgba(38, 166, 154, 0.4);
    color: #B2DFDB;
    box-shadow: 0 4px 20px rgba(38, 166, 154, 0.2);
}

.error {
    background: rgba(239, 83, 80, 0.2);
    border-color: rgba(239, 83, 80, 0.4);
    color: #FFCDD2;
    box-shadow: 0 4px 20px rgba(239, 83, 80, 0.2);
}

footer {
    text-align: center;
    padding: 3rem 0 2rem;
    color: var(--text-muted);
    font-size: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 4rem;
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .page-content {
        padding-top: 100px;
        padding-bottom: 40px;
    }
    
    .container {
        padding: 2.5rem 2rem;
        margin: 0 1rem;
    }
    
    h2 {
        font-size: 2.2rem;
    }
    
    .container img {
        width: 70px;
        height: 70px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 2rem 1.5rem;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    input, textarea {
        padding: 1rem 1.2rem;
    }
    
    button {
        padding: 1.2rem 1.5rem;
    }
    
    .container img {
        width: 60px;
        height: 60px;
    }
}
</style>


<div class="background-elements">
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
</div>

<div class="page-content">
    <div class="container">
        
        <div class="admin-icon" style="display: none;"></div>
        <h2> Add New Item</h2>
        <form method="POST">
            <label> Title:</label>
            <input type="text" name="title" placeholder="Enter item title" required maxlength="100">

            <label> Description:</label>
            <textarea name="description" placeholder="Enter item details" maxlength="500"></textarea>

            <label> Price:</label>
            <input type="number" step="0.01" name="price" placeholder="0.00" required min="0.01">

            <label> Stock:</label>
            <input type="number" name="stock" placeholder="Available quantity" required min="1">

            <label> Category:</label>
            <input type="text" name="category" placeholder="e.g. Electronics, Books" required maxlength="50">

            <label> UPI ID:</label>
            <input type="text" name="upi_id" placeholder="e.g. yourname@upi" required maxlength="50">

            <button type="submit" name="add"> Add Item</button>
        </form>

        <?php
        if(isset($_POST['add'])){
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $category = trim($_POST['category']);
            $upi_id = trim($_POST['upi_id']);
            $uid = $_SESSION['user_id'];

            
            if(empty($title) || empty($category) || empty($upi_id) || $price <= 0 || $stock <= 0) {
                echo "<p class='message error'> Please fill all fields with valid values!</p>";
                exit;
            }

            
            if(!preg_match('/^[a-zA-Z0-9\.\-]{2,49}@[a-zA-Z]{2,15}$/', $upi_id)) {
                echo "<p class='message error'> Please enter a valid UPI ID (e.g. yourname@upi)!</p>";
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO items (title, description, price, stock, category, upi_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdissi", $title, $desc, $price, $stock, $category, $upi_id, $uid);

            if($stmt->execute()){
                echo "<p class='message success'> Item added successfully!</p>";
                
                echo "<script>
                        setTimeout(() => {
                            document.querySelector('form').reset();
                        }, 2000);
                      </script>";
            } else {
                echo "<p class='message error'> Error: ".htmlspecialchars($stmt->error)."</p>";
            }
            $stmt->close();
        }
        ?>
    </div>
</div>


<footer>&copy; 2025 Campus Bazaar</footer>
