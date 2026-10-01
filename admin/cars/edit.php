<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-sidebar.php';
require_once __DIR__ . '/../../includes/csrf.php';

$db = Database::getConnection();
$error = '';

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    redirect('admin/cars/index.php');
}

// Fetch existing car details
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('admin/cars/index.php');
}

// Handle Gallery Image Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_gallery_image') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $img_id = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
        $stmt = $db->prepare("SELECT image_path FROM car_images WHERE id = ? AND car_id = ?");
        $stmt->execute([$img_id, $car_id]);
        $img_path = $stmt->fetchColumn();

        if ($img_path) {
            $stmt = $db->prepare("DELETE FROM car_images WHERE id = ?");
            $stmt->execute([$img_id]);

            if (file_exists(UPLOAD_DIR . $img_path)) {
                unlink(UPLOAD_DIR . $img_path);
            }
            $_SESSION['flash_success'] = "Gallery image removed successfully.";
            redirect("admin/cars/edit.php?id=" . $car_id);
        }
    }
}

// Handle Form Submission (Update Car)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'update_car')) {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF security check failed.";
    } else {
        $brand = sanitize($_POST['brand'] ?? '');
        $model = sanitize($_POST['model'] ?? '');
        $variant = sanitize($_POST['variant'] ?? '');
        $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $discount_price = filter_input(INPUT_POST, 'discount_price', FILTER_VALIDATE_FLOAT) ?? 0.00;
        $fuel_type = sanitize($_POST['fuel_type'] ?? '');
        $transmission = sanitize($_POST['transmission'] ?? '');
        $engine = sanitize($_POST['engine'] ?? '');
        $mileage = sanitize($_POST['mileage'] ?? '');
        $color = sanitize($_POST['color'] ?? '');
        $body_type = sanitize($_POST['body_type'] ?? '');
        $seating_capacity = filter_input(INPUT_POST, 'seating_capacity', FILTER_VALIDATE_INT);
        $registration_info = sanitize($_POST['registration_info'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $availability_status = sanitize($_POST['availability_status'] ?? 'Available');
        $condition_type = sanitize($_POST['condition_type'] ?? 'New');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $main_image_name = $car['main_image'];

        // Main Image Update Processing
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['main_image']['tmp_name'];
            $file_name = $_FILES['main_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;

                if (move_uploaded_file($file_tmp, UPLOAD_DIR . $new_file_name)) {
                    // Unlink old image if custom
                    if ($main_image_name !== 'default-car.jpg' && file_exists(UPLOAD_DIR . $main_image_name)) {
                        unlink(UPLOAD_DIR . $main_image_name);
                    }
                    $main_image_name = $new_file_name;
                }
            } else {
                $error = "Invalid image file format for main display photo.";
            }
        }

        if (!$error && $brand && $model && $year && $price) {
            $stmt = $db->prepare("
                UPDATE cars SET 
                    brand = ?, model = ?, variant = ?, year = ?, price = ?, discount_price = ?,
                    fuel_type = ?, transmission = ?, engine = ?, mileage = ?, color = ?,
                    body_type = ?, seating_capacity = ?, registration_info = ?, description = ?,
                    main_image = ?, availability_status = ?, condition_type = ?, is_featured = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $brand, $model, $variant, $year, $price, $discount_price,
                $fuel_type, $transmission, $engine, $mileage, $color,
                $body_type, $seating_capacity, $registration_info, $description,
                $main_image_name, $availability_status, $condition_type, $is_featured,
                $car_id
            ]);

            // Process New Gallery Uploads
            if (isset($_FILES['gallery_images'])) {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                $total_files = count($_FILES['gallery_images']['name']);
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $g_tmp = $_FILES['gallery_images']['tmp_name'][$i];
                        $g_name = $_FILES['gallery_images']['name'][$i];
                        $g_ext = strtolower(pathinfo($g_name, PATHINFO_EXTENSION));

                        if (in_array($g_ext, $allowed_exts)) {
                            $g_new_name = 'car_gallery_' . time() . '_' . rand(1000, 9999) . '.' . $g_ext;
                            if (move_uploaded_file($g_tmp, UPLOAD_DIR . $g_new_name)) {
                                $stmt_img = $db->prepare("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)");
                                $stmt_img->execute([$car_id, $g_new_name]);
                            }
                        }
                    }
                }
            }

            log_audit_action($_SESSION['admin_id'], 'CAR_UPDATED', "Updated vehicle details: {$brand} {$model} (ID: {$car_id})");
            $_SESSION['flash_success'] = "Vehicle details updated successfully!";
            redirect('admin/cars/index.php');
        } elseif (!$error) {
            $error = "Please fill in all required fields (Brand, Model, Year, Price).";
        }
    }
}

// Fetch Gallery Images
$stmt = $db->prepare("SELECT * FROM car_images WHERE car_id = ?");
$stmt->execute([$car_id]);
$gallery_images = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Edit Vehicle Details</h3>
    <a href="index.php" class="btn btn-secondary">Back to Cars List</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm p-4">
    <form method="POST" enctype="multipart/form-data">
        <?php CSRF::insertField(); ?>
        <input type="hidden" name="action" value="update_car">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Brand *</label>
                <input type="text" name="brand" value="<?= sanitize($car['brand']) ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Model *</label>
                <input type="text" name="model" value="<?= sanitize($car['model']) ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Variant</label>
                <input type="text" name="variant" value="<?= sanitize($car['variant']) ?>" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Manufacture Year *</label>
                <input type="number" name="year" value="<?= $car['year'] ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Price (NPR) *</label>
                <input type="number" step="0.01" name="price" value="<?= $car['price'] ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Discount Price (NPR)</label>
                <input type="number" step="0.01" name="discount_price" value="<?= $car['discount_price'] ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Availability Status *</label>
                <select name="availability_status" class="form-select">
                    <option value="Available" <?= $car['availability_status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Reserved" <?= $car['availability_status'] === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
                    <option value="Sold" <?= $car['availability_status'] === 'Sold' ? 'selected' : '' ?>>Sold</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Condition *</label>
                <select name="condition_type" class="form-select">
                    <option value="New" <?= $car['condition_type'] === 'New' ? 'selected' : '' ?>>New</option>
                    <option value="Used" <?= $car['condition_type'] === 'Used' ? 'selected' : '' ?>>Used</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Fuel Type *</label>
                <select name="fuel_type" class="form-select" required>
                    <option value="Petrol" <?= $car['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= $car['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="Electric" <?= $car['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                    <option value="Hybrid" <?= $car['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Transmission *</label>
                <select name="transmission" class="form-select" required>
                    <option value="Automatic" <?= $car['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual" <?= $car['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Body Type *</label>
                <select name="body_type" class="form-select" required>
                    <option value="Sedan" <?= $car['body_type'] === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                    <option value="SUV" <?= $car['body_type'] === 'SUV' ? 'selected' : '' ?>>SUV</option>
                    <option value="Hatchback" <?= $car['body_type'] === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                    <option value="Coupe" <?= $car['body_type'] === 'Coupe' ? 'selected' : '' ?>>Coupe</option>
                    <option value="Truck" <?= $car['body_type'] === 'Truck' ? 'selected' : '' ?>>Truck</option>
                    <option value="Van" <?= $car['body_type'] === 'Van' ? 'selected' : '' ?>>Van</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Seating Capacity *</label>
                <input type="number" name="seating_capacity" value="<?= $car['seating_capacity'] ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Engine</label>
                <input type="text" name="engine" value="<?= sanitize($car['engine']) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Mileage</label>
                <input type="text" name="mileage" value="<?= sanitize($car['mileage']) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Color</label>
                <input type="text" name="color" value="<?= sanitize($car['color']) ?>" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Registration / Plate Information</label>
                <input type="text" name="registration_info" value="<?= sanitize($car['registration_info']) ?>" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Replace Main Photo</label>
                <input type="file" name="main_image" class="form-control" accept="image/*">
                <small class="text-muted">Current: <?= sanitize($car['main_image']) ?></small>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold">Add Additional Gallery Images</label>
                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold">Vehicle Description</label>
                <textarea name="description" class="form-control" rows="4"><?= sanitize($car['description']) ?></textarea>
            </div>

            <div class="col-md-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featuredCheck" <?= $car['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="featuredCheck">
                        Feature this vehicle on the homepage
                    </label>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100">Update Vehicle Information</button>
            </div>
        </div>
    </form>
</div>

<!-- Gallery Images Management Section -->
<?php if (!empty($gallery_images)): ?>
<div class="card border-0 shadow-sm p-4 mt-4">
    <h5 class="fw-bold mb-3">Current Gallery Images</h5>
    <div class="row g-3">
        <?php foreach ($gallery_images as $g): ?>
            <div class="col-md-2 text-center">
                <img src="<?= UPLOAD_URL . sanitize($g['image_path']) ?>" class="img-thumbnail mb-2" style="height: 100px; width: 100%; object-fit: cover;">
                <form method="POST" onsubmit="return confirm('Delete this gallery image?');">
                    <?php CSRF::insertField(); ?>
                    <input type="hidden" name="action" value="delete_gallery_image">
                    <input type="hidden" name="image_id" value="<?= $g['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete Image</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

    </div> <!-- Close flex-grow-1 -->
</div> <!-- Close d-flex -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>