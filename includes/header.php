<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-danger" href="<?= SITE_URL ?>/index.php"><?= SITE_NAME ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/cars.php">Cars</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/compare.php">Compare</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php#contact">Contact</a></li>
      </ul>
      <div class="d-flex">
        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="<?= SITE_URL ?>/customer/dashboard.php" class="btn btn-outline-light me-2">My Account</a>
            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-danger">Logout</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light me-2">Login</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-danger">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>