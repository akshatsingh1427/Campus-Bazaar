<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db_connect.php';


if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT wallet FROM users WHERE user_id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['wallet'] = floatval($row['wallet']); 
    } else {
        $_SESSION['wallet'] = 0.00;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    
    :root {
        --primary-gradient: linear-gradient(135deg, #a0abdaff 0%, #bb8ceaff 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-light: #ffffff;
        --text-dark: #2d3748;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    header {
        background: var(--primary-gradient);
        color: var(--text-light);
        padding: 1.5rem 2rem;
        text-align: center;
        font-size: 2.8rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        position: relative;
        overflow: hidden;
    }

    header::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 200%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    header span {
        background: linear-gradient(45deg, #8febb7ff, #679fc4ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    nav {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        padding: 1rem 2rem;
        border-bottom: 1px solid var(--glass-border);
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }

    .nav-btn {
        color: var(--text-light);
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        background: rgba(255,255,255,0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }

    .nav-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--secondary-gradient);
        transition: left 0.3s ease;
        z-index: -1;
    }

    .nav-btn:hover::before {
        left: 0;
    }

    .nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(240, 147, 251, 0.4);
    }

    .welcome-bar {
        text-align: center;
        margin: 1rem auto;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 1.1rem;
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(230,230,255,0.9));
        padding: 0.8rem 1.5rem;
        border-radius: 15px;
        width: fit-content;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: slideDown 0.5s ease;
        backdrop-filter: blur(10px);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        header {
            font-size: 2rem;
            padding: 1rem;
        }
        
        nav {
            flex-wrap: wrap;
            gap: 0.8rem;
            padding: 0.8rem;
        }
        
        .nav-btn {
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
        }
        
        .welcome-bar {
            font-size: 1rem;
            padding: 0.6rem 1rem;
        }
    }
    </style>
</head>
<body>
    <header>
        <span>Campus Bazaar</span>
    </header>

    <nav id="mainNav">
        <button class="nav-btn" data-page="index.php"> Home</button>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <button class="nav-btn" data-page="add_item.php"> Add Item</button>
        <?php endif; ?>
        <button class="nav-btn" data-page="catalog.php"> Catalog</button>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
            <button class="nav-btn" data-page="cart.php"> Cart</button>
            <button class="nav-btn" data-page="wallet.php"> Wallet</button>
        <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="nav-btn" data-page="logout.php"> Logout</button>
        <?php else: ?>
            <button class="nav-btn" data-page="register.php"> Register</button>
            <button class="nav-btn" data-page="login.php"> Login</button>
        <?php endif; ?>
    </nav>

    <?php if(isset($_SESSION['name'])): ?>
    <div class="welcome-bar">
         Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
        <?php if(isset($_SESSION['wallet'])): ?>
            |  Wallet: ₹<?php echo number_format($_SESSION['wallet'], 2); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <script>
    
    document.addEventListener('DOMContentLoaded', function() {
        const navButtons = document.querySelectorAll('.nav-btn');
        
        navButtons.forEach(button => {
            button.addEventListener('click', function() {
                const page = this.getAttribute('data-page');
                if (page) {
                    window.location.href = page;
                }
            });
            
            
            button.addEventListener('mouseover', function() {
                
            });
        });
    });
    </script>
</body>
</html>