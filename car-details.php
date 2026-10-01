<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    redirect('cars.php');
}

$db = Database::getConnection();

// Fetch Car Info
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('cars.php');
}

// Gallery Images
$stmt = $db->prepare("SELECT * FROM car_images WHERE car_id = ?");
$stmt->execute([$car_id]);
$gallery = $stmt->fetchAll();

// Booking Form Logic
$booking_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_test_drive') {
    if (!isset($_SESSION['customer_id'])) {
        $_SESSION['flash_error'] = "Please log in to book a test drive.";
        redirect('login.php');
    }
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $booking_msg = '<div class="alert alert-danger">CSRF token error.</div>';
    } else {
        $phone = sanitize($_POST['phone'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $preferred_date = sanitize($_POST['preferred_date'] ?? '');
        $preferred_time = sanitize($_POST['preferred_time'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        // Prevent Duplicate Slot Bookings
        $stmt = $db->prepare("SELECT COUNT(*) FROM test_drive_bookings WHERE car_id = ? AND preferred_date = ? AND preferred_time = ? AND status != 'Cancelled'");
        $stmt->execute([$car_id, $preferred_date, $preferred_time]);
        if ($stmt->fetchColumn() > 0) {
            $booking_msg = '<div class="alert alert-warning">This time slot is already reserved for this car. Please pick another date/time.</div>';
        } else {
            $stmt = $db->prepare("INSERT INTO test_drive_bookings (customer_id, car_id, phone, email, preferred_date, preferred_time, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['customer_id'], $car_id, $phone, $email, $preferred_date, $preferred_time, $message]);
            $booking_msg = '<div class="alert alert-success">Test drive request submitted successfully! We will contact you to confirm.</div>';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <?= $booking_msg ?>
    <div class="row g-4">
        <!-- Image Gallery -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm overflow-hidden mb-3">
                <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="img-fluid" id="mainCarImg" alt="Vehicle Image">
            </div>
            <div class="d-flex gap-2">
                <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="img-thumbnail rounded" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainCarImg').src=this.src">
                <?php foreach ($gallery as $img): ?>
                    <img src="<?= UPLOAD_URL . sanitize($img['image_path']) ?>" class="img-thumbnail rounded" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainCarImg').src=this.src">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Spec Overview & Purchase Controls -->
        <div class="col-md-5">
            <h2 class="fw-bold"><?= sanitize($car['brand'] . ' ' . $car['model']) ?></h2>
            <p class="text-muted mb-2">Variant: <?= sanitize($car['variant']) ?> (<?= sanitize($car['year']) ?>)</p>

            <?php if ($car['availability_status'] === 'Sold'): ?>
                <span class="badge bg-danger fs-5 px-3 py-2 mb-3">SOLD OUT</span>
            <?php else: ?>
                <span class="badge bg-success fs-6 mb-3">Available in Stock</span>
            <?php endif; ?>

            <div class="mb-4">
                <?php if ($car['discount_price'] > 0): ?>
                    <span class="text-decoration-line-through text-muted fs-5">NPR <?= number_format($car['price'], 2) ?></span>
                    <h2 class="text-danger fw-bold">NPR <?= number_format($car['discount_price'], 2) ?></h2>
                <?php else: ?>
                    <h2 class="text-success fw-bold">NPR <?= number_format($car['price'], 2) ?></h2>
                <?php endif; ?>
            </div>

            <!-- Main Actions -->
            <div class="d-grid gap-2 mb-4">
                <?php if ($car['availability_status'] === 'Available'): ?>
                    <a href="buy.php?id=<?= $car['id'] ?>" class="btn btn-danger btn-lg">Buy Now (eSewa Online Payment)</a>
                    <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#testDriveModal">Book a Test Drive</button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>Vehicle Sold</button>
                <?php endif; ?>
                <a href="compare.php?add=<?= $car['id'] ?>" class="btn btn-outline-secondary">Add to Compare</a>
            </div>

            <div class="card border-0 bg-light p-3">
                <h6 class="fw-bold">Quick Specifications</h6>
                <ul class="list-unstyled mb-0 small">
                    <li><strong>Fuel Type:</strong> <?= sanitize($car['fuel_type']) ?></li>
                    <li><strong>Transmission:</strong> <?= sanitize($car['transmission']) ?></li>
                    <li><strong>Engine:</strong> <?= sanitize($car['engine']) ?></li>
                    <li><strong>Mileage:</strong> <?= sanitize($car['mileage']) ?></li>
                    <li><strong>Body Type:</strong> <?= sanitize($car['body_type']) ?></li>
                    <li><strong>Seating:</strong> <?= sanitize($car['seating_capacity']) ?> Seats</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="mt-5">
        <h4 class="fw-bold">Vehicle Overview</h4>
        <p><?= nl2br(sanitize($car['description'])) ?></p>
    </div>
</div>

<!-- Test Drive Modal -->
<div class="modal fade" id="testDriveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Test Drive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php CSRF::insertField(); ?>
                    <input type="hidden" name="action" value="book_test_drive">
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preferred Date *</label>
                        <input type="date" name="preferred_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preferred Time *</label>
                        <input type="time" name="preferred_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes / Message</label>
                        <textarea name="message" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>