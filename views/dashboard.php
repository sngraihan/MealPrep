<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Dashboard";
require_once '../models/meal_model.php';
require_once '../models/order_model.php';
require_once '../models/user_model.php';

$mealModel = new MealModel();
$orderModel = new OrderModel();
$userModel = new UserModel();

// Get statistics
// [UPDATED]: Using stored functions for statistics
$total_meals = $mealModel->getTotalAvailableMeals();
$today_orders = $orderModel->getTodayOrders();
$total_users = $userModel->getTotalUsers();
$recent_orders = $orderModel->getRecentOrders();

// Debug information
echo "<!-- Debug: Found " . count($recent_orders) . " recent orders -->";
if (empty($recent_orders)) {
    echo "<!-- Debug: No orders found. Checking database connection... -->";
    $db_check = $orderModel->debugOrders();
    echo "<!-- Debug: Total orders in database: " . $db_check . " -->";
}

include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Available Meals</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_meals; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-utensils fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Today's Orders</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $today_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Recent Orders</h5>
    </div>
    <div class="card-body">
        <?php 
        // Debug information
        $db_check = $orderModel->debugOrders();
        
        if (empty($recent_orders)): 
        ?>
            <div class="text-center py-4">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Recent Orders</h5>
                <p class="text-muted">
                    <?php if ($db_check > 0): ?>
                        There are <?php echo $db_check; ?> orders in the database, but none could be retrieved.
                        Please check the database connection and query.
                    <?php else: ?>
                        No orders have been placed yet.
                    <?php endif; ?>
                </p>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <div class="mt-3">
                    <a href="admin_orders.php" class="btn btn-primary">
                        <i class="fas fa-clipboard-list me-2"></i>Manage Orders
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'Unknown User'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td>Rp<?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_icon = '';
                                switch($order['status']) {
                                    case 'pending': 
                                        $status_class = 'warning'; 
                                        $status_icon = 'clock';
                                        break;
                                    case 'confirmed': 
                                        $status_class = 'info'; 
                                        $status_icon = 'check-circle';
                                        break;
                                    case 'delivered': 
                                        $status_class = 'success'; 
                                        $status_icon = 'truck';
                                        break;
                                    case 'cancelled': 
                                        $status_class = 'danger'; 
                                        $status_icon = 'times-circle';
                                        break;
                                    default:
                                        $status_class = 'secondary';
                                        $status_icon = 'question-circle';
                                }
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
