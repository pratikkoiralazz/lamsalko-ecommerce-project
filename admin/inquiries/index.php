<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();

if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $stmt = $db->prepare("UPDATE inquiries SET status = 'Read' WHERE id = ?");
    $stmt->execute([$id]);
    redirect('admin/inquiries/index.php');
}

$inquiries = $db->query("SELECT * FROM inquiries ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Inquiries - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Contact Messages & Inquiries</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inquiries as $inq): ?>
                    <tr>
                        <td><small><?= date('M d, Y', strtotime($inq['created_at'])) ?></small></td>
                        <td><strong><?= sanitize($inq['name']) ?></strong></td>
                        <td><?= sanitize($inq['email']) ?></td>
                        <td><?= sanitize($inq['subject']) ?></td>
                        <td><?= sanitize($inq['message']) ?></td>
                        <td><span class="badge bg-<?= $inq['status'] === 'Unread' ? 'danger' : 'secondary' ?>"><?= $inq['status'] ?></span></td>
                        <td>
                            <?php if ($inq['status'] === 'Unread'): ?>
                                <a href="index.php?mark_read=<?= $inq['id'] ?>" class="btn btn-sm btn-outline-success">Mark Read</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>