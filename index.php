<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

$db = Database::getConnection();

// Featured Cars
$stmt = $db->query("SELECT * FROM cars WHERE is_featured = 1 AND availability_status = 'Available' ORDER BY id DESC LIMIT 6");
$featured_cars = $stmt->fetchAll();

// Latest Cars
$stmt = $db->query("SELECT * FROM cars WHERE availability_status = 'Available' ORDER BY id DESC LIMIT 6");
$latest_cars = $stmt->fetchAll();

// Handle Contact Form Submission
$contact_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $contact_msg = '<div class="alert alert-danger">CSRF verification failed.</div>';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($name && $email && $subject && $message) {
            $stmt = $db->prepare("INSERT INTO inquiries (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $contact_msg = '<div class="alert alert-success">Thank you! Your message has been sent.</div>';
        } else {
            $contact_msg = '<div class="alert alert-warning">Please fill in all required fields.</div>';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-dark text-white py-5 position-relative" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero-bg.jpg') center/cover no-repeat; min-height: 480px;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-3">Find Your Dream Vehicle</h1>
                <p class="lead mb-4">Discover premium, verified luxury and everyday cars with seamless online purchasing and eSewa payments.</p>
                <a href="cars.php" class="btn btn-danger btn-lg me-2">Browse All Inventory</a>
                <a href="#search-bar" class="btn btn-outline-light btn-lg">Search Cars</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Search Bar -->
<div class="container my-4" id="search-bar">
    <div class="card shadow-sm border-0 p-3 bg-white rounded-3">
        <form action="cars.php" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Brand</label>
                <input type="text" name="brand" class="form-control" placeholder="e.g. Tesla, Hyundai">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Fuel Type</label>
                <select name="fuel_type" class="form-select">
                    <option value="">All Fuel Types</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Max Price (NPR)</label>
                <input type="number" name="max_price" class="form-control" placeholder="e.g. 10000000">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search Vehicles</button>
            </div>
        </form>
    </div>
</div>

<!-- Featured Cars -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Featured Inventory</h3>
        <a href="cars.php?featured=1" class="text-decoration-none fw-bold text-danger">View All &rarr;</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featured_cars as $car): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                    <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= sanitize($car['brand'] . ' ' . $car['model']) ?>">
                    <div class="card-body">
                        <span class="badge bg-danger mb-2">Featured</span>
                        <h5 class="card-title fw-bold"><?= sanitize($car['brand'] . ' ' . $car['model']) ?></h5>
                        <p class="text-muted small mb-2"><?= sanitize($car['year']) ?> • <?= sanitize($car['fuel_type']) ?> • <?= sanitize($car['transmission']) ?></p>
                        <h5 class="text-success fw-bold">NPR <?= number_format($car['discount_price'] > 0 ? $car['discount_price'] : $car['price'], 2) ?></h5>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-outline-dark w-100">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Latest Cars -->
<div class="bg-light py-5">
    <div class="container">
        <h3 class="fw-bold mb-4">Latest Arrivals</h3>
        <div class="row g-4">
            <?php foreach ($latest_cars as $car): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= sanitize($car['brand'] . ' ' . $car['model']) ?>">
                        <div class="card-body">
                            <span class="badge bg-info text-dark mb-2"><?= sanitize($car['condition_type']) ?></span>
                            <h5 class="card-title fw-bold"><?= sanitize($car['brand'] . ' ' . $car['model']) ?></h5>
                            <p class="text-muted small mb-2"><?= sanitize($car['year']) ?> • <?= sanitize($car['body_type']) ?> • <?= sanitize($car['engine']) ?></p>
                            <h5 class="text-success fw-bold">NPR <?= number_format($car['price'], 2) ?></h5>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Contact Section -->
<div class="container my-5" id="contact">
    <div class="row g-4">
        <div class="col-md-5">
            <h3 class="fw-bold">Contact Our Showroom</h3>
            <p class="text-muted">Have questions about a vehicle or payment options? Get in touch with our expert sales team.</p>
            <p class="mb-1"><strong>Address:</strong> Kathmandu, Nepal</p>
            <p class="mb-1"><strong>Phone:</strong> +977-1-4433221</p>
            <p class="mb-1"><strong>Email:</strong> contact@apexmotors.com</p>
            <p class="mb-0"><strong>Hours:</strong> Sun - Fri: 9:00 AM - 6:00 PM</p>
        </div>
        <div class="col-md-7">
            <?= $contact_msg ?>
            <div class="card border-0 shadow-sm p-4">
                <form method="POST">
                    <?php CSRF::insertField(); ?>
                    <input type="hidden" name="action" value="contact">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>