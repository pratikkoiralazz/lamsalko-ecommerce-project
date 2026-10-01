<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/csrf.php';

$db = Database::getConnection();

// Handle Vehicle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_car') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = "Security validation failed.";
    } else {
        $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
        if ($car_id) {
            // Fetch images to delete files from uploads directory
            $stmt = $db->prepare("SELECT main_image FROM cars WHERE id = ?");
            $stmt->execute([$car_id]);
            $main_image = $stmt->fetchColumn();

            $stmt = $db->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
            $stmt->execute([$car_id]);
            $gallery_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete car record (Car gallery images delete via CASCADE foreign key)
            $stmt = $db->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$car_id]);

            // Clean up main image file if not default
            if ($main_image && $main_image !== 'default-car.jpg' && file_exists(UPLOAD_DIR . $main_image)) {
                unlink(UPLOAD_DIR . $main_image);
            }

            // Clean up gallery image files
            foreach ($gallery_images as $g_img) {
                if (file_exists(UPLOAD_DIR . $g_img)) {
                    unlink(UPLOAD_DIR . $g_img);
                }
            }

            log_audit_action($_SESSION['admin_id'], 'CAR_DELETED', "Deleted vehicle ID: {$car_id}");
            $_SESSION['flash_success'] = "Vehicle and associated images deleted successfully.";
        }
    }
    redirect('admin/cars/index.php');
}

$cars = $db->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Car Inventory Management</h3>
        <p class="text-muted small mb-0">Manage vehicle specs, discounts, edit details, or remove inventory</p>
    </div>
    <a href="add.php" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-circle me-1"></i> Add New Vehicle
    </a>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= sanitize($_SESSION['flash_success']) ?>
        <?php unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= sanitize($_SESSION['flash_error']) ?>
        <?php unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Brand & Model</th>
                    <th>Year</th>
                    <th>Price (NPR)</th>
                    <th>Fuel / Transmission</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cars)): ?>
                    <tr><td colspan="8" class="text-center py-4">No vehicles found in database. Click "Add New Vehicle" above to add one.</td></tr>
                <?php else: ?>
                    <?php foreach ($cars as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td>
                                <img src="<?= UPLOAD_URL . sanitize($c['main_image']) ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover;" alt="Car Image">
                            </td>
                            <td>
                                <strong><?= sanitize($c['brand'] . ' ' . $c['model']) ?></strong><br>
                                <small class="text-muted"><?= sanitize($c['variant']) ?></small>
                            </td>
                            <td><?= $c['year'] ?></td>
                            <td class="fw-bold text-success">
                                NPR <?= number_format($c['discount_price'] > 0 ? $c['discount_price'] : $c['price'], 2) ?>
                            </td>
                            <td><?= $c['fuel_type'] ?> / <?= $c['transmission'] ?></td>
                            <td>
                                <span class="badge bg-<?= $c['availability_status'] === 'Available' ? 'success' : ($c['availability_status'] === 'Sold' ? 'danger' : 'warning') ?>">
                                    <?= $c['availability_status'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Vehicle">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.');">
                                        <?php CSRF::insertField(); ?>
                                        <input type="hidden" name="action" value="delete_car">
                                        <input type="hidden" name="car_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Vehicle">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    </div> <!-- Close flex-grow-1 -->
</div> <!-- Close d-flex -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>