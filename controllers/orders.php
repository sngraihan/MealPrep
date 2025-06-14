<?php
session_start();
require_once __DIR__ . '/../models/order_model.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$orderModel = new OrderModel();

// Handle order cancellation
if ($_POST && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // [UPDATED]: Using stored procedure for order cancellation
    try {
        if ($orderModel->cancelOrder($order_id)) {
            $_SESSION['success'] = 'Order cancelled successfully!';
        } else {
            $_SESSION['error'] = 'Failed to cancel order.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    }
    
    header('Location: ../views/orders.php');
    exit();
}

// Get user orders
$user_id = $_SESSION['user_id'];
$orders = $orderModel->getUserOrders($user_id);
?>
