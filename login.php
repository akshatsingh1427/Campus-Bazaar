<?php
include 'db_connect.php';
include 'header.php';
?>


<style>

:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);
    --glass-bg: rgba(255, 255, 255, 0.08);
    --glass-border: rgba(255, 255, 255, 0.15);
}

body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    background: url('images/login1.jpg') no-repeat center center/cover;
    background-attachment: fixed;
    min-height: 100vh;
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.3) 100%);
    z-index: 0;
}

.container.card {
    position: relative;
    z-index: 1;
    max-width: 420px;
    margin: 100px auto;
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 3rem 2.5rem;
    text-align: center;
    color: #fff;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.container.card h2 {
    margin: 0 0 2rem 0;
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(45deg, #fff, #e3f2fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

label {
    display: block;
    text-align: left;
    margin-top: 1.2rem;
    font-weight: 600;
    color: #f0f4ff;
    font-size: 0.95rem;
}

input[type="email"], input[type="password"] {
    width: 100%;
    padding: 1rem;
    margin-top: 0.5rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.2);
    outline: none;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 1rem;
    box-sizing: border-box;
    transition: all 0.3s ease;
}

input[type="email"]:focus, input[type="password"]:focus {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.4);
    box-shadow: 0 0 15px rgba(255,255,255,0.1);
}

input::placeholder {
    color: rgba(255,255,255,0.7);
}

button[type="submit"] {
    width: 100%;
    padding: 1.2rem;
    margin-top: 2rem;
    border: none;
    border-radius: 12px;
    background: var(--secondary-gradient);
    color: #fff;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(54, 209, 220, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    box-shadow: 0 15px 30px rgba(54, 209, 220, 0.4);
}

.msg {
    margin-top: 1.5rem;
    padding: 1rem;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.msg.error {
    background: rgba(255, 0, 72, 0.2);
    border: 1px solid rgba(255, 0, 72, 0.3);
    color: #ffe3e3;
}

.msg.success {
    background: rgba(46, 204, 113, 0.15);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #d3ffe0;
}

.small-link {
    margin-top: 1.5rem;
    display: block;
    color: #cfeeff;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.small-link:hover {
    text-decoration: underline;
    color: #ffffff;
}

@media (max-width: 520px) {
    .container.card {
        margin: 60px 1rem;
        padding: 2.5rem 2rem;
    }
    
    .container.card h2 {
        font-size: 1.8rem;
    }
    
    input[type="email"], input[type="password"] {
        padding: 0.9rem;
    }
}
</style>

<div class="container card">
    <h2> Login to Campus Bazaar</h2>

    <?php
    if(isset($_POST['login'])){
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if(empty($email) || empty($password)){
            echo "<div class='msg error'> Please enter both email and password.</div>";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result && $result->num_rows > 0){
                $user = $result->fetch_assoc();
                if(password_verify($password, $user['password'])){
                    
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['wallet'] = $user['wallet'];

                    
                    echo "<script>
                            setTimeout(function() {
                                window.location.href = '" . ($user['role'] === 'admin' ? 'add_item.php' : 'catalog.php') . "';
                            }, 100);
                          </script>";
                    echo "<div class='msg success'> Login successful! Redirecting...</div>";
                    exit;
                } else {
                    echo "<div class='msg error'> Incorrect email or password!</div>";
                }
            } else {
                echo "<div class='msg error'> Incorrect email or password!</div>";
            }
            $stmt->close();
        }
    }
    ?>

    <form method="POST">
        <label> Email:</label>
        <input type="email" name="email" placeholder="you@mail.jiit.ac.in/you@admin.in" required>

        <label> Password:</label>
        <input type="password" name="password" placeholder="Enter your password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <a class="small-link" href="register.php">Don't have an account? Register here</a>
</div>