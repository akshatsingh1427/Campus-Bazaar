<?php
include 'db_connect.php';
include 'header.php';


if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: white; text-align: center; font-size: 1.2rem;'>Please login to see your wallet.</p>";
    exit;
}

$uid = $_SESSION['user_id'];
$showUPIPayment = false;
$paymentSuccess = false;
$addedAmount = 0;


$res = $conn->query("SELECT wallet, upi_pin FROM users WHERE user_id=$uid");
$wallet = 0.00;
$hasPIN = false;

if($res && $res->num_rows > 0){
    $row = $res->fetch_assoc();
    $wallet = floatval($row['wallet']);
    $hasPIN = !empty($row['upi_pin']);
    $_SESSION['wallet'] = $wallet;
}


if(isset($_POST['setup_pin'])) {
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];
    
    if(strlen($new_pin) >= 4 && $new_pin === $confirm_pin) {
        
        $hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET upi_pin = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_pin, $uid);
        
        if($stmt->execute()) {
            echo "<div class='success-message'> UPI PIN set successfully!</div>";
            $hasPIN = true;
        }
        $stmt->close();
    } else {
        echo "<div class='error-message'> PINs must match and be at least 4 digits!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Bazaar | Wallet</title>
           <style>
   
    :root {
        --primary: #8B5FBF;
        --primary-glow: rgba(139, 95, 191, 0.4);
        --success: #9C27B0;
        --success-glow: rgba(156, 39, 176, 0.4);
        --danger: #E91E63;
        --danger-glow: rgba(233, 30, 99, 0.4);
        --accent: #BA68C8;
        --accent-glow: rgba(186, 104, 200, 0.4);
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
           
            linear-gradient(125deg, #1A103D 0%, #2D1B69 50%, #1A103D 100%),
            
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
        max-width: 600px;
        margin: 80px auto 40px;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }

    
    .wallet-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
    }

    .wallet-header h2 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #E1BEE7, #BA68C8, #8B5FBF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -1px;
        text-shadow: 0 4px 30px rgba(186, 104, 200, 0.3);
        position: relative;
        display: inline-block;
    }

    .wallet-subtitle {
        font-size: 1.2rem;
        color: var(--text-secondary);
        font-weight: 400;
        margin-top: 0.5rem;
        opacity: 0.9;
    }

    
    .wallet-card {
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.1) 0%, 
            rgba(255, 255, 255, 0.05) 100%);
        backdrop-filter: blur(25px) saturate(200%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--border-radius-lg);
        padding: 3rem;
        box-shadow: var(--elevation-2);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        text-align: center;
        margin-bottom: 2rem;
    }

    .wallet-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--accent), var(--success));
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
    }

    .wallet-card::after {
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

    .wallet-card:hover::after {
        left: 100%;
    }

    .wallet-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--elevation-3);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .wallet-card p {
        font-size: 1.5rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .wallet-card strong {
        font-size: 3.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #BA68C8, #E1BEE7, #8B5FBF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 30px rgba(186, 104, 200, 0.3);
        display: block;
    }

    
    .add-money-btn, .upi-payment-section, .success-section {
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.12) 0%, 
            rgba(255, 255, 255, 0.06) 100%);
        backdrop-filter: blur(40px) saturate(200%);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius-lg);
        padding: 3rem;
        margin-bottom: 2rem;
        box-shadow: var(--elevation-2);
        animation: modalSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .add-money-btn::before, .upi-payment-section::before, .success-section::before {
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

    .payment-title {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #E1BEE7, #BA68C8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-align: center;
    }

    .payment-subtitle {
        color: var(--text-secondary);
        font-size: 1.2rem;
        font-weight: 400;
        margin-bottom: 2rem;
        text-align: center;
    }

    
    .add-money-btn form, .upi-payment-section form {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        align-items: center;
    }

    .add-money-btn input, .upi-payment-section input {
        width: 100%;
        max-width: 400px;
        padding: 1.5rem 2rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-primary);
        font-size: 1.2rem;
        text-align: center;
        outline: none;
        transition: var(--transition);
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .add-money-btn input:focus, .upi-payment-section input:focus {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 0 30px var(--primary-glow);
        transform: translateY(-2px);
    }

    .add-money-btn input::placeholder, .upi-payment-section input::placeholder {
        color: var(--text-muted);
        font-weight: 500;
    }

    .pin-input {
        letter-spacing: 0.5rem;
        font-size: 1.4rem !important;
        font-weight: 800 !important;
    }

    
    .primary-btn {
        background: linear-gradient(135deg, var(--success), #8B5FBF);
        color: white;
        border: none;
        padding: 1.5rem 3rem;
        border-radius: var(--border-radius);
        font-size: 1.2rem;
        font-weight: 800;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 8px 25px var(--success-glow);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        width: 100%;
        max-width: 400px;
        font-family: inherit;
    }

    .primary-btn::before {
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

    .primary-btn:hover::before {
        left: 100%;
    }

    .primary-btn:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 15px 40px var(--success-glow);
    }

    .primary-btn:disabled {
        background: #666;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .primary-btn:disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .secondary-btn {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        border: none;
        padding: 1.2rem 2.5rem;
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
        box-shadow: 0 6px 20px var(--primary-glow);
        font-family: inherit;
    }

    .secondary-btn::before {
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

    .secondary-btn:hover::before {
        left: 100%;
    }

    .secondary-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 12px 30px var(--primary-glow);
    }

    .action-buttons {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    
    .success-message, .error-message {
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

    .success-message {
        background: rgba(156, 39, 176, 0.2);
        border-color: rgba(156, 39, 176, 0.4);
        color: #F3E5F5;
        box-shadow: 0 4px 20px rgba(156, 39, 176, 0.2);
    }

    .error-message {
        background: rgba(233, 30, 99, 0.2);
        border-color: rgba(233, 30, 99, 0.4);
        color: #FCE4EC;
        box-shadow: 0 4px 20px rgba(233, 30, 99, 0.2);
    }

    
    .success-section {
        text-align: center;
        padding: 3.5rem;
    }

    .success-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }

    .success-section h3 {
        font-size: 2.2rem;
        margin-bottom: 1rem;
        color: var(--text-primary);
        font-weight: 700;
    }

    .success-section p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.8;
        color: var(--text-secondary);
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

    
    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .payment-processing {
        text-align: center;
        color: var(--text-secondary);
        font-size: 1.1rem;
        margin: 1rem 0;
    }

    .spinner {
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top: 3px solid var(--success);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    
    @media (max-width: 768px) {
        .container {
            margin: 60px auto 30px;
            padding: 0 1rem;
        }
        
        .wallet-header h2 {
            font-size: 2.8rem;
        }
        
        .wallet-card, .add-money-btn, .upi-payment-section, .success-section {
            padding: 2rem;
        }
        
        .wallet-card p {
            font-size: 1.3rem;
        }
        
        .wallet-card strong {
            font-size: 2.8rem;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .secondary-btn {
            width: 100%;
            max-width: 250px;
        }
        
        .add-money-btn input, .upi-payment-section input {
            padding: 1.3rem 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .wallet-header h2 {
            font-size: 2.2rem;
        }
        
        .payment-title {
            font-size: 1.8rem;
        }
        
        .success-icon {
            font-size: 4rem;
        }
        
        .success-section h3 {
            font-size: 1.8rem;
        }
    }
    </style>
</head>
<body>
    <body>
   
    <div class="background-elements">
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
    </div>

    <div class="container">
       
        <div class="wallet-header">
            <h2> Digital Wallet</h2>
            <p class="wallet-subtitle">Secure payments for your campus shopping experience</p>
        </div>
        

        <div class="wallet-card">
            <p>Current Balance</p>
            <strong id="walletBalance">₹<?php echo number_format($wallet, 2); ?></strong>
        </div>

        <?php if(!$hasPIN): ?>
        
        <div class="upi-payment-section">
            <h3 class="payment-title"> Set Your UPI PIN</h3>
            <p style="color: rgba(255,255,255,0.8); text-align: center; margin-bottom: 2rem;">
                Set a demo UPI PIN for secure payments
            </p>
            
            <form method="POST">
                <input type="password" name="new_pin" placeholder="Enter 4-digit PIN" class="pin-input" minlength="4" maxlength="6" required pattern="[0-9]+">
                <input type="password" name="confirm_pin" placeholder="Confirm PIN" class="pin-input" minlength="4" maxlength="6" required pattern="[0-9]+">
                <button type="submit" name="setup_pin" class="primary-btn">
                     Set PIN
                </button>
            </form>
        </div>

        <?php else: ?>
        
        <div class="add-money-btn" id="addMoneySection">
            <button type="button" id="initiatePaymentBtn" class="primary-btn">
                 Add Money to Wallet
            </button>
            
            <div class="action-buttons">
                <button class="secondary-btn" data-page="cart.php"> View Cart</button>
                <button class="secondary-btn" data-page="index.php"> Continue Shopping</button>
            </div>
        </div>

        
        <div class="upi-payment-section" id="upiPaymentSection" style="display: none;">
            <h3 class="payment-title">Enter Payment Details</h3>
            
            <form id="paymentForm">
                <input type="number" step="0.01" name="amount" id="amount" placeholder="Enter Amount (₹)" required min="0.01">
                <input type="text" name="upi_id" id="upi_id" placeholder="Enter UPI ID" required>
                <input type="password" name="pin" id="pin" placeholder="Enter your UPI PIN" class="pin-input" required>
                <button type="submit" id="confirmPaymentBtn" class="primary-btn">
                     Confirm Payment
                </button>
            </form>
            <div id="paymentStatus"></div>
        </div>

        
        <div class="success-section" id="successSection" style="display: none;">
            <div class="success-icon"></div>
            <h3>Payment Successful!</h3>
            <p id="successMessage"></p>
            <div class="action-buttons">
                <button class="secondary-btn" data-page="cart.php"> Go to Cart</button>
                <button class="secondary-btn" data-page="index.php"> Continue Shopping</button>
                <button type="button" id="addMoreMoneyBtn" class="secondary-btn"> Add More Money</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer>&copy; 2025 Campus Bazaar - Secure Digital Wallet</footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        
        $('#initiatePaymentBtn').click(function() {
            $('#addMoneySection').hide();
            $('#upiPaymentSection').show();
        });

        
        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            
            const amount = $('#amount').val();
            const upi_id = $('#upi_id').val();
            const pin = $('#pin').val();
            
            if (!amount || !upi_id || !pin) {
                showMessage(' Please fill all fields!', 'error');
                return;
            }

            if (parseFloat(amount) <= 0) {
                showMessage(' Please enter a valid amount!', 'error');
                return;
            }

            
            $('#confirmPaymentBtn').prop('disabled', true).text('Processing...');
            $('#paymentForm').addClass('loading');
            
            
            $('#paymentStatus').html('<div class="payment-processing"><div class="spinner"></div>Processing payment...</div>');

        
            setTimeout(function() {
                
                $.ajax({
                    url: 'process_payment.php',
                    type: 'POST',
                    data: {
                        amount: amount,
                        upi_id: upi_id,
                        pin: pin
                    },
                    success: function(response) {
                        if (response.success) {
                           
                            $('#walletBalance').text('₹' + parseFloat(response.new_balance).toFixed(2));
                            $('#successMessage').text('₹' + parseFloat(amount).toFixed(2) + ' has been added to your wallet');
                            
                            
                            $('#upiPaymentSection').hide();
                            $('#successSection').show();
                        } else {
                            showMessage(' ' + response.message, 'error');
                            $('#confirmPaymentBtn').prop('disabled', false).text(' Confirm Payment');
                            $('#paymentForm').removeClass('loading');
                        }
                    },
                    error: function() {
                        showMessage(' Payment failed! Please try again.', 'error');
                        $('#confirmPaymentBtn').prop('disabled', false).text('Confirm Payment');
                        $('#paymentForm').removeClass('loading');
                    }
                });
            }, 2000);
        });

       
        $('#addMoreMoneyBtn').click(function() {
            $('#successSection').hide();
            $('#upiPaymentSection').show();
            $('#paymentForm')[0].reset();
            $('#paymentStatus').empty();
            $('#confirmPaymentBtn').prop('disabled', false).text(' Confirm Payment');
            $('#paymentForm').removeClass('loading');
        });

       
        $('.secondary-btn[data-page]').click(function() {
            const page = $(this).data('page');
            if (page) {
                window.location.href = page;
            }
        });

        function showMessage(message, type) {
            $('#paymentStatus').html('<div class="' + type + '-message">' + message + '</div>');
        }
    });
    </script>
</body>
</html>