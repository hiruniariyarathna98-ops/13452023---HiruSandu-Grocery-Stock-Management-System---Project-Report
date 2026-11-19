<?php
date_default_timezone_set('Asia/Colombo');
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

// Get Dashboard Stats
$total = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$low = $conn->query("SELECT COUNT(*) as c FROM products WHERE quantity <= 10 AND quantity > 0")->fetch_assoc()['c'];
$out = $conn->query("SELECT COUNT(*) as c FROM products WHERE quantity = 0")->fetch_assoc()['c'];

$fin = $conn->query("SELECT SUM(quantity * buy_price) as val, SUM(quantity * (sale_price - buy_price)) as profit FROM products")->fetch_assoc();
$top_products = $conn->query("SELECT name, quantity FROM products ORDER BY quantity DESC LIMIT 5");
$out_products = $conn->query("SELECT id, name FROM products WHERE quantity = 0");

include 'includes/header.php';
?>

<style>
    .card-stat { border: none; border-radius: 12px; color: white; padding: 20px; text-decoration: none; display: block; transition: transform 0.2s; }
    .card-stat:hover { transform: scale(1.03); color: white; }
    .bg-purple { background: linear-gradient(45deg, #667eea, #764ba2); }
    .bg-orange { background: linear-gradient(45deg, #f6d365, #fda085); }
    .bg-red { background: linear-gradient(45deg, #ff758c, #ff7eb3); }
</style>

<h3 class="mb-4 fw-bold">Dashboard Overview</h3>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="products.php" class="card-stat bg-purple d-flex justify-content-between align-items-center shadow">
            <div>
                <h6 class="text-uppercase mb-1">Total Products</h6>
                <h2 class="mb-0 fw-bold"><?= $total ?></h2>
            </div>
            <i class="fas fa-box fa-3x opacity-50"></i>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="products.php?filter_status=low" class="card-stat bg-orange d-flex justify-content-between align-items-center shadow">
            <div>
                <h6 class="text-uppercase mb-1">Low Stock Items</h6>
                <h2 class="mb-0 fw-bold"><?= $low ?></h2>
            </div>
            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="products.php?filter_status=out" class="card-stat bg-red d-flex justify-content-between align-items-center shadow">
            <div>
                <h6 class="text-uppercase mb-1">Out of Stock</h6>
                <h2 class="mb-0 fw-bold"><?= $out ?></h2>
            </div>
            <i class="fas fa-times-circle fa-3x opacity-50"></i>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card p-4 h-100">
            <h5 class="fw-bold"><i class="fas fa-coins text-warning me-2"></i>Financial Summary</h5>
            <hr>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Total Stock Value:</span>
                <strong class="fs-5 text-dark">Rs. <?= number_format($fin['val'] ?? 0, 2) ?></strong>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Potential Profit:</span>
                <strong class="text-success fs-5">Rs. <?= number_format($fin['profit'] ?? 0, 2) ?></strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card p-4 h-100">
            <h5 class="fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i>Top 5 Products</h5>
            <hr>
            <ul class="list-group list-group-flush">
                <?php if($top_products->num_rows > 0): ?>
                    <?php while($p = $top_products->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <?= htmlspecialchars($p['name']) ?>
                            <span class="badge bg-primary rounded-pill"><?= $p['quantity'] ?></span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item px-0 text-muted bg-transparent">No products available.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card p-4 border-danger">
            <h5 class="fw-bold text-danger"><i class="fas fa-exclamation-circle me-2"></i>Out of Stock Products</h5>
            <hr>
            <?php if($out_products->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $out_products->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                                <td>
                                    <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Update Stock</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">All products are in stock! ✅</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>