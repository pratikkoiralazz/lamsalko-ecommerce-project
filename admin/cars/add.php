<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_admin_login();

$db = Database::getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $condition_type = sanitize($_POST['condition_type'] ?? 'New');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // Image Processing
        $main_image_name = 'default-car.jpg';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['main_image']['tmp_tmp_name'] ?? $_FILES['main_image']['tmp_name'];
            $file_name = $_FILES['main_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $target_path = UPLOAD_DIR . $new_file_name;

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                if (move_uploaded_file($file_tmp, $target_path)) {
                    $main_image_name = $new_file_name;
                }
            } else {
                $error = "Invalid image file type. Allowed formats: JPG, JPEG, PNG, WEBP.";
            }
        }

        if (!$error && $brand && $model && $year && $price) {
            $stmt = $db->prepare("
                INSERT INTO cars (brand, model, variant, year, price, discount_price, fuel_type, transmission, engine, mileage, color, body_type, seating_capacity, registration_info, description, main_image, availability_status, condition_type, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', ?, ?)
            ");
            $stmt->execute([
                $brand, $model, $variant, $year, $price, $discount_price, $fuel_type,
                $transmission, $engine, $mileage, $color, $body_type, $seating_capacity,
                $registration_info, $description, $main_image_name, $condition_type, $is_featured
            ]);

            $car_id = $db->lastInsertId();

            // Multiple Gallery Images Processing
            if (isset($_FILES['gallery_images'])) {
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

            log_audit_action($_SESSION['admin_id'], 'CAR_ADDED', "Added vehicle: {$brand} {$model} (ID: {$car_id})");
            $_SESSION['flash_success'] = "Vehicle added successfully!";
            redirect('admin/cars/index.php');
        } elseif (!$error) {
            $error = "Please fill in all required fields (Brand, Model, Year, Price).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Vehicle - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Add New Vehicle to Inventory</h3>
        <a href="index.php" class="btn btn-secondary">Back to Cars List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm p-4">
        <form method="POST" enctype="multipart/form-data">
            <?php CSRF::insertField(); ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Brand *</label>
                    <input type="text" name="brand" class="form-control" placeholder="e.g. Hyundai" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Model *</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g. Tucson" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Variant</label>
                    <input type="text" name="variant" class="form-control" placeholder="e.g. Signature 2.0">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Manufacture Year *</label>
                    <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Price (NPR) *</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Discount Price (NPR)</label>
                    <input type="number" step="0.01" name="discount_price" class="form-control" value="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Condition *</label>
                    <select name="condition_type" class="form-select">
                        <option value="New">New</option>
                        <option value="Used">Used</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Fuel Type *</label>
                    <select name="fuel_type" class="form-select" required>
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Transmission *</label>
                    <select name="transmission" class="form-select" required>
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Body Type *</label>
                    <select name="body_type" class="form-select" required>
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Coupe">Coupe</option>
                        <option value="Truck">Truck</option>
                        <option value="Van">Van</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Seating Capacity *</label>
                    <input type="number" name="seating_capacity" class="form-control" value="5" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Engine Specification</label>
                    <input type="text" name="engine" class="form-control" placeholder="e.g. 1999 cc / Dual Motor">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mileage</label>
                    <input type="text" name="mileage" class="form-control" placeholder="e.g. 15.4 kmpl / 500 km range">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Color</label>
                    <input type="text" name="color" class="form-control" placeholder="e.g. Pearl White">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Registration / Plate Information</label>
                    <input type="text" name="registration_info" class="form-control" placeholder="e.g. BA 2 PA 8821">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Main Display Image *</label>
                    <input type="file" name="main_image" class="form-control" accept="image/*" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Additional Gallery Images</label>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-bold">Vehicle Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featuredCheck">
                        <label class="form-check-label fw-bold" for="featuredCheck">
                            Feature this vehicle on the home page hero section
                        </label>
                    </div>
                </div>

                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn btn-success btn-lg w-100">Save Vehicle to Showroom</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>