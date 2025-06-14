<?php
require_once '../controllers/orders.php';

$page_title = "My Orders";
include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-list-alt me-2"></i>My Orders</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="meals.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Order
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Order History</h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Orders Yet</h4>
                <p class="text-muted">You haven't placed any orders yet. Start by ordering your favorite meals!</p>
                <a href="meals.php" class="btn btn-primary">
                    <i class="fas fa-utensils me-2"></i>Order Now
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Meal</th>
                            <th>Quantity</th>
                            <th>Order Date</th>
                            <th>Subtotal</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['meal_name']); ?></td>
                            <td><span class="badge bg-info"><?php echo $order['quantity']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td><strong>Rp<?php echo number_format($order['subtotal'], 0, ',', '.'); ?></strong></td>
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
                                }
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <form method="POST" action="../controllers/orders.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button type="submit" name="cancel_order" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
