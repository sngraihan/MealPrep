<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "All Orders";
require_once '../models/order_model.php';

$orderModel = new OrderModel();

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    // [UPDATED]: Using stored procedure to update order status
    try {
        if ($orderModel->updateOrderStatus($_POST['order_id'], $_POST['status'])) {
            $_SESSION['success'] = 'Order status updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update status!';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
    }
    
    header('Location: admin_orders.php');
    exit();
}

// Get all orders
$orders = $orderModel->getAllOrders();

include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i>All Orders</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-1"></i>Filter by Status
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?status=all">All Orders</a></li>
                <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                <li><a class="dropdown-item" href="?status=confirmed">Confirmed</a></li>
                <li><a class="dropdown-item" href="?status=delivered">Delivered</a></li>
                <li><a class="dropdown-item" href="?status=cancelled">Cancelled</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Order Management</h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-4">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No orders found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Meals</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($order['meals']); ?>
                                </small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td><strong>Rp<?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong></td>
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
                            <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                    <i class="fas fa-edit me-1"></i>Update
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="update_order_id" name="order_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStatus(orderId, currentStatus) {
    document.getElementById('update_order_id').value = orderId;
    document.getElementById('status').value = currentStatus;
    
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php include 'layout/footer.php'; ?>
