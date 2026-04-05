<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) session_start();
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Campus Bazaar | Home</title>


<style>

:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --accent-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --glass-bg: rgba(255, 255, 255, 0.12);
    --glass-border: rgba(255, 255, 255, 0.18);
}

body {
    background: url('images/campus1.jpg') no-repeat center center/cover;
    backdrop-filter: blur(4px);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.3) 100%);
    z-index: 0;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 0.6; }
}

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.7; }
  50% { transform: translateY(-20px) scale(1.05); opacity: 0.9; }
}

.glow {
    position: fixed;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.25) 0%, transparent 70%);
    animation: float 8s ease-in-out infinite;
    z-index: 0;
    filter: blur(12px);
}
.glow:nth-child(1){ top: 20%; left: 10%; width: 120px; height: 120px; animation-delay: 0s; }
.glow:nth-child(2){ top: 60%; right: 15%; width: 180px; height: 180px; animation-delay: 2s; }
.glow:nth-child(3){ bottom: 10%; left: 40%; width: 150px; height: 150px; animation-delay: 4s; }

.container {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 2rem;
}

.card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid var(--glass-border);
    color: white;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    padding: 4rem 3rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    max-width: 800px;
    width: 100%;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 50px rgba(0,0,0,0.4);
}

.card h2 {
    font-size: 3.2rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    background: linear-gradient(90deg, #ff8a00, #e52e71, #6a11cb, #2575fc);
    background-size: 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: moveGradient 8s infinite linear;
    line-height: 1.2;
}

@keyframes moveGradient {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}

.card p {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.7;
    margin: 1.2rem auto;
    max-width: 650px;
    font-weight: 300;
}

.card .btn-area {
    margin-top: 2.5rem;
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.action-btn {
    background: var(--secondary-gradient);
    color: white;
    padding: 1.2rem 2.5rem;
    border: none;
    border-radius: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1.1rem;
    box-shadow: 0 8px 20px rgba(245, 87, 108, 0.3);
    position: relative;
    overflow: hidden;
    font-family: inherit;
}

.action-btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(245, 87, 108, 0.4);
}

footer {
    position: relative;
    z-index: 2;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(10px);
    color: #fff;
    padding: 2rem;
    text-align: center;
    margin-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.15);
}

@media (max-width: 768px) {
    .card {
        padding: 3rem 2rem;
        margin: 1rem;
    }
    
    .card h2 {
        font-size: 2.2rem;
    }
    
    .card p {
        font-size: 1.1rem;
    }
    
    .card .btn-area {
        flex-direction: column;
        align-items: center;
    }
    
    .action-btn {
        width: 100%;
        max-width: 250px;
    }
}
</style>

<body

<div class="glow"></div>
<div class="glow"></div>
<div class="glow"></div>

<div class="container">
    <div class="card">
        <h2>Welcome to Campus Bazaar!</h2>
        <p>Buy & Sell study materials, stationery, electronics, and more — securely and easily.</p>
        <p>Connect with your campus community and trade items hassle-free!</p>
        <div class="btn-area">
            <button class="action-btn" data-page="register.php">Register Now</button>
            <button class="action-btn" data-page="login.php">Login</button>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelectorAll('.action-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            if (page) {
                window.location.href = page;
            }
        });
    });
});
</script>

</body>
</html>
