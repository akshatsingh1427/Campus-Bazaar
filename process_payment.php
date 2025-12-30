<?php
include 'db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$uid = $_SESSION['user_id'];
$amount = floatval($_POST['amount']);
$upi_id = trim($_POST['upi_id']);
$entered_pin = $_POST['pin'];


if ($amount <= 0 || empty($upi_id) || empty($entered_pin)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment details']);
    exit;
}


$stmt = $conn->prepare("SELECT wallet, upi_pin FROM users WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
$stored_pin_hash = $user['upi_pin'];


if (!password_verify($entered_pin, $stored_pin_hash)) {
    echo json_encode(['success' => false, 'message' => 'Invalid UPI PIN']);
    exit;
}

try {
    
    $conn->query("UPDATE users SET wallet = wallet + $amount WHERE user_id=$uid");
    
    
    $res = $conn->query("SELECT wallet FROM users WHERE user_id=$uid");
    $new_balance = 0;
    if($res && $res->num_rows > 0){
        $row = $res->fetch_assoc();
        $new_balance = floatval($row['wallet']);
        $_SESSION['wallet'] = $new_balance;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment successful',
        'new_balance' => $new_balance
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
}

$stmt->close();
?>