<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getConnection();

// Query Filters
$brand = sanitize($_GET['brand'] ?? '');
$fuel_type = sanitize($_GET['fuel_type'] ?? '');
$body_type = sanitize($_GET['body_type'] ?? '');
$transmission = sanitize($_GET['transmission'] ?? '');
$condition_type = sanitize($_GET['condition_type'] ?? '');
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$featured = filter_input(INPUT_GET, 'featured', FILTER_VALIDATE_INT);
$sort = sanitize($_GET['sort'] ?? 'latest');

$query = "SELECT * FROM cars WHERE availability_status = 'Available'";
$params = [];

if ($brand) {
    $query .= " AND brand LIKE ?";
    $params[] = "%$brand%";
}
if ($fuel_type) {
    $query .= " AND fuel_type = ?";
    $params[] = $fuel_type;
}
if ($body_type) {
    $query .= " AND body_type = ?";
    $params[] = $body_type;
}
if ($transmission) {
    $query .= " AND transmission = ?";
    $params[] = $transmission;
}
if ($condition_type) {
    $query .= " AND condition_type = ?";
    $params[] = $condition_type;
}
if ($max_price) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}
if ($featured) {
    $query .= " AND is_featured = 1";
}

if ($sort === 'price_asc') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY id DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Vehicle Showroom</h2>
    <div class="row g-4">
        <!-- Sidebar Filter -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="fw-bold mb-3">Filter Vehicles</h5>
                <form method="GET" action="cars.php">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Brand</label>
                        <input type="text" name="brand" value="<?= sanitize($brand) ?>" class="form-control" placeholder="Search Brand">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Body Type</label>
                        <select name="body_type" class="form-select">
                            <option value="">All</option>
                            <option value="Sedan" <?= $body_type === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="SUV" <?= $body_type === 'SUV' ? 'selected' : '' ?>>SUV</option>
                            <option value="Hatchback" <?= $body_type === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                            <option value="Truck" <?= $body_type === 'Truck' ? 'selected' : '' ?>>Truck</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fuel Type</label>
                        <select name="fuel_type" class="form-select">
                            <option value="">All</option>
                            <option value="Petrol" <?= $fuel_type === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                            <option value="Diesel" <?= $fuel_type === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="Electric" <?= $fuel_type === 'Electric' ? 'selected' : '' ?>>Electric</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Transmission</label>
                        <select name="transmission" class="form-select">
                            <option value="">All</option>
                            <option value="Manual" <?= $transmission === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="Automatic" <?= $transmission === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    <a href="cars.php" class="btn btn-link text-center w-100 mt-2 text-muted text-decoration-none">Reset All</a>
                </form>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="col-md-9">
            <?php if (empty($cars)): ?>
                <div class="alert alert-info">No vehicles matched your current filters.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($cars as $car): ?>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                <img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= sanitize($car['brand'] . ' ' . $car['model']) ?>">
                                <div class="card-body">
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>