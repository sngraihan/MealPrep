<?php
session_start();
require_once __DIR__ . '/../models/meal_model.php';
require_once __DIR__ . '/../models/order_model.php';

$mealModel = new MealModel();
$orderModel = new OrderModel();

// Handle order submission
if ($_POST && isset($_POST['place_order'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $meal_id = $_POST['meal_id'];
    $quantity = $_POST['quantity'];
    $order_date = $_POST['order_date'];
    
    // [UPDATED]: Using transaction and stored procedure for order placement
    try {
        if ($orderModel->placeOrder($user_id, $order_date, $meal_id, $quantity)) {
            $_SESSION['success'] = 'Your order has been placed successfully!';
        } else {
            $_SESSION['error'] = 'Failed to place order. Please try again.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    }
    
    header('Location: ../views/meals.php');
    exit();
}

// Get available meals
$meals = $mealModel->getAvailableMeals();
?>
