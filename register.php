<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) session_start();
include 'header.php';
?>


<style>

:root {
    --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.15);
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    color: white;
    background: url('images/register-bg.jpg') no-repeat center center/cover;
    background-size: cover;
    position: relative;
    overflow-x: hidden;
    min-height: 100vh;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(106, 17, 203, 0.6) 0%, rgba(37, 117, 252, 0.4) 100%);
    backdrop-filter: blur(8px);
    z-index: -1;
}

.container.card {
    max-width: 500px;
    margin: 80px auto;
    background: var(--glass-bg);
    backdrop-filter: blur(25px);
    padding: 3rem 2.5rem;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    border: 1px solid var(--glass-border);
    text-align: center;
    animation: slideIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

h2 {
    font-size: 2.4rem;
    text-shadow: 0 4px 15px rgba(0,0,0,0.3);
    margin-bottom: 1.5rem;
    background: linear-gradient(45deg, #fff, #e3f2fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
}

label {
    display: block;
    text-align: left;
    margin-top: 1.2rem;
    font-weight: 600;
    color: #f5f6fa;
    font-size: 0.95rem;
}

.input-field {
    width: 100%;
    padding: 1rem 1.2rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.2);
    outline: none;
    margin-top: 0.5rem;
    background: rgba(255,255,255,0.1);
    color: #35323269;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.input-field:focus {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.4);
    box-shadow: 0 0 20px rgba(255,255,255,0.1);
    transform: translateY(-2px);
}

.input-field::placeholder {
    color: rgba(255,255,255,0.7);
}

.register {
    width: 100%;
    padding: 1.2rem;
    margin-top: 2rem;
    border: none;
    border-radius: 12px;
    background: var(--secondary-gradient);
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);
    position: relative;
    overflow: hidden;
}

.register::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.register:hover::before {
    left: 100%;
}

.register:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(245, 87, 108, 0.4);
}

.message {
    margin-top: 1.5rem;
    padding: 1rem 1.2rem;
    border-radius: 12px;
    font-weight: 600;
    text-align: center;
    animation: fadeInUp 0.5s ease;
    backdrop-filter: blur(10px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.success {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.4);
    color: #d3ffe0;
}

.message.error {
    background: rgba(255, 99, 132, 0.2);
    border: 1px solid rgba(255, 99, 132, 0.4);
    color: #ffe2e2;
}

.message a {
    color: #a8e6cf;
    text-decoration: none;
    font-weight: 600;
}

.message a:hover {
    text-decoration: underline;
}

footer {
    text-align: center;
    padding: 2rem;
    font-size: 0.9rem;
    background: rgba(0,0,0,0.3);
    color: white;
    margin-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .container.card {
        margin: 60px 1rem;
        padding: 2.5rem 2rem;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    .input-field {
        padding: 0.9rem 1rem;
    }
}

@media (max-width: 480px) {
    .container.card {
        padding: 2rem 1.5rem;
        margin: 40px 1rem;
    }
    
    h2 {
        font-size: 1.8rem;
    }
}
</style>

<div class="container card">
    <h2>Register</h2>
    <form method="POST" class="register-form">
        <label>Name:</label>
        <input type="text" name="name" class="input-field" placeholder="Enter your full name" required maxlength="100">

        <label>Email:</label>
        <input type="email" name="email" class="input-field" placeholder="Enter your email" required maxlength="100">

        <label>Password:</label>
        <input type="password" name="password" class="input-field"
               placeholder="6+ characters"
               pattern=".{6,}"
               title="6+ characters" required>

        <label>Confirm Password:</label>
        <input type="password" name="cpassword" class="input-field" placeholder="Re-enter your password" required>

        <label>Role:</label>
        <select name="role" class="input-field" required>
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit" name="register" class="register">Register</button>
    </form>

<?php
if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $cpassword_raw = $_POST['cpassword'];
    $role = strtolower(trim($_POST['role']));

    
    if(empty($name) || empty($email) || empty($password_raw) || empty($role)) {
        echo "<p class='message error'>All fields are required!</p>";
        exit;
    }

    if($password_raw !== $cpassword_raw){
        echo "<p class='message error'>Passwords do not match!</p>";
        exit;
    }

    if(strlen($password_raw) < 6){
        echo "<p class='message error'>Password must be at least 6 characters!</p>";
        exit;
    }

    if($role !== 'student' && $role !== 'admin'){
        echo "<p class='message error'>Invalid role selected!</p>";
        exit;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<p class='message error'>Invalid email format!</p>";
        exit;
    }

    
    $emailValid = false;
    if($role == 'student' && preg_match('/@mail\.jiit\.ac\.in$/', $email)){
        $emailValid = true;
    } elseif($role == 'admin' && preg_match('/@admin\.in$/', $email)){
        $emailValid = true;
    }

    if(!$emailValid){
        echo "<p class='message error'>Invalid email for the selected role. Students: @mail.jiit.ac.in, Admins: @admin.in</p>";
        exit;
    }

    
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0){
        echo "<p class='message error'>Email already registered!</p>";
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, wallet) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if($stmt->execute()){
        echo "<p class='message success'>Registration successful! <a href='login.php'>Login here</a></p>";
    } else {
        echo "<p class='message error'>Error: ".htmlspecialchars($stmt->error)."</p>";
    }

    $stmt->close();
}
?>
</div>

<footer>&copy; 2025 Campus Bazaar</footer>