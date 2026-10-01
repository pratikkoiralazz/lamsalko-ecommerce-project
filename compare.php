<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['compare_ids'])) {
    $_SESSION['compare_ids'] = [];
}

// Add car to compare
if (isset($_GET['add'])) {
    $add_id = (int)$_GET['add'];
    if (!in_array($add_id, $_SESSION['compare_ids']) && count($_SESSION['compare_ids']) < 4) {
        $_SESSION['compare_ids'][] = $add_id;
    }
    redirect('compare.php');
}

// Remove car
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $_SESSION['compare_ids'] = array_filter($_SESSION['compare_ids'], fn($id) => $id !== $remove_id);
    redirect('compare.php');
}

$compared_cars = [];
if (!empty($_SESSION['compare_ids'])) {
    $db = Database::getConnection();
    $in  = str_repeat('?,', count($_SESSION['compare_ids']) - 1) . '?';
    $stmt = $db->prepare("SELECT * FROM cars WHERE id IN ($in)");
    $stmt->execute(array_values($_SESSION['compare_ids']));
    $compared_cars = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Compare Vehicles</h2>
    
    <?php if (empty($compared_cars)): ?>
        <div class="alert alert-info">No vehicles selected for comparison. <a href="cars.php">Browse inventory</a> to add vehicles.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 200px;">Specification</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <th>
                                <?= sanitize($car['brand'] . ' ' . $car['model']) ?>
                                <br>
                                <a href="compare.php?remove=<?= $car['id'] ?>" class="btn btn-sm btn-outline-danger mt-1">Remove</a>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Image</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><img src="<?= UPLOAD_URL . sanitize($car['main_image']) ?>" style="height: 100px; object-fit: cover;" class="rounded"></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td class="fw-bold text-success">NPR <?= number_format($car['price'], 2) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Year</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><?= sanitize($car['year']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Fuel Type</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><?= sanitize($car['fuel_type']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Transmission</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><?= sanitize($car['transmission']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Engine</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><?= sanitize($car['engine']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Mileage</th>
                        <?php foreach ($compared_cars as $car): ?>
                            <td><?= sanitize($car['mileage']) ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>