<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$page_title = "Manage Meals";
require_once '../models/meal_model.php';

$mealModel = new MealModel();

// Handle form submissions
if ($_POST) {
    try {
        if (isset($_POST['add_meal'])) {
            // [UPDATED]: Using stored procedure to add meal
            if ($mealModel->addMeal($_POST['name'], $_POST['description'], $_POST['price'])) {
                $_SESSION['success'] = 'Meal added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add meal!';
            }
        } elseif (isset($_POST['update_meal'])) {
            // [UPDATED]: Using stored procedure to update meal
            if ($mealModel->updateMeal($_POST['meal_id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['available'])) {
                $_SESSION['success'] = 'Meal updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update meal!';
            }
        } elseif (isset($_POST['delete_meal'])) {
            // [UPDATED]: Using stored procedure to delete meal
            if ($mealModel->deleteMeal($_POST['meal_id'])) {
                $_SESSION['success'] = 'Meal deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete meal!';
            }
        }
    } catch(Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: admin_menu.php');
    exit();
}

// Get all meals
$meals = $mealModel->getAllMeals();

include 'layout/header.php';
include 'layout/navbar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-utensils me-2"></i>Manage Meals</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMealModal">
        <i class="fas fa-plus me-1"></i>Add New Meal
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>All Meals</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meals as $meal): ?>
                    <tr>
                        <td><?php echo $meal['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($meal['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($meal['description'], 0, 50)) . '...'; ?></td>
                        <td><span class="badge bg-success fs-6">Rp<?php echo number_format($meal['price'], 0, ',', '.'); ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $meal['available'] ? 'success' : 'danger'; ?>">
                                <?php echo $meal['available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($meal['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editMeal(<?php echo htmlspecialchars(json_encode($meal)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" name="delete_meal" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $meal['id']; ?>, '<?php echo htmlspecialchars($meal['name']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Meal Modal -->
<div class="modal fade" id="addMealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Meal Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" step="1000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_meal" class="btn btn-primary">Add Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Meal Modal -->
<div class="modal fade" id="editMealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_meal_id" name="meal_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Meal Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price (Rp)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="1000" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_available" class="form-label">Status</label>
                        <select class="form-select" id="edit_available" name="available" required>
                            <option value="1">Available</option>
                            <option value="0">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_meal" class="btn btn-primary">Update Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMeal(meal) {
    document.getElementById('edit_meal_id').value = meal.id;
    document.getElementById('edit_name').value = meal.name;
    document.getElementById('edit_description').value = meal.description;
    document.getElementById('edit_price').value = meal.price;
    document.getElementById('edit_available').value = meal.available;
    
    new bootstrap.Modal(document.getElementById('editMealModal')).show();
}

function confirmDelete(mealId, mealName) {
    Swal.fire({
        title: 'Delete Meal?',
        html: `Are you sure you want to delete "<strong>${mealName}</strong>"?<br><br><small class="text-muted">Note: If this meal has been ordered before, it will be set as "Unavailable" instead of being deleted.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="meal_id" value="${mealId}">
                <input type="hidden" name="delete_meal" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include 'layout/footer.php'; ?>
