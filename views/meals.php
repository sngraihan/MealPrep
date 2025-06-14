<?php
require_once '../controllers/meals.php';

$page_title = "Order Food";
include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i>Order Food</h1>
</div>

<div class="row">
    <!-- Available Meals -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Available Meals</h5>
            </div>
            <div class="card-body">
                <?php if (empty($meals)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No meals available at the moment</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Meal Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($meals as $meal): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($meal['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($meal['description']); ?></td>
                                    <td><span class="badge bg-success fs-6">Rp<?php echo number_format($meal['price'], 0, ',', '.'); ?></span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="selectMeal(<?php echo $meal['id']; ?>, '<?php echo htmlspecialchars($meal['name']); ?>', <?php echo $meal['price']; ?>)">
                                            <i class="fas fa-plus me-1"></i>Select
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
    </div>

    <!-- Order Form -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Place Order</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="../controllers/meals.php">
                    <div class="mb-3">
                        <label for="meal_id" class="form-label">Select Meal</label>
                        <select class="form-select" id="meal_id" name="meal_id" required>
                            <option value="">Choose a meal...</option>
                            <?php foreach ($meals as $meal): ?>
                            <option value="<?php echo $meal['id']; ?>" data-price="<?php echo $meal['price']; ?>">
                                <?php echo htmlspecialchars($meal['name']); ?> - Rp<?php echo number_format($meal['price'], 0, ',', '.'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="10" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="order_date" class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" id="order_date" name="order_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Order Summary</h6>
                                <div id="order-summary">
                                    <p class="text-muted">Select a meal to see summary</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-success w-100">
                        <i class="fas fa-shopping-cart me-2"></i>Place Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectMeal(id, name, price) {
    document.getElementById('meal_id').value = id;
    updateOrderSummary();
}

function updateOrderSummary() {
    const mealSelect = document.getElementById('meal_id');
    const quantity = document.getElementById('quantity').value;
    const summaryDiv = document.getElementById('order-summary');
    
    if (mealSelect.value) {
        const selectedOption = mealSelect.options[mealSelect.selectedIndex];
        const price = parseFloat(selectedOption.dataset.price);
        const total = price * quantity;
        
        summaryDiv.innerHTML = `
            <p><strong>Meal:</strong> ${selectedOption.text.split(' - ')[0]}</p>
            <p><strong>Quantity:</strong> ${quantity}</p>
            <p><strong>Unit Price:</strong> Rp${price.toLocaleString('id-ID')}</p>
            <hr>
            <p><strong>Total:</strong> Rp${total.toLocaleString('id-ID')}</p>
        `;
    } else {
        summaryDiv.innerHTML = '<p class="text-muted">Select a meal to see summary</p>';
    }
}

document.getElementById('meal_id').addEventListener('change', updateOrderSummary);
document.getElementById('quantity').addEventListener('input', updateOrderSummary);
</script>

<?php include 'layout/footer.php'; ?>
