<?php
require 'db.php';

// Security: Ensure user is logged in
if (!isset($_SESSION['user'])) { 
    http_response_code(403); 
    exit; 
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$filter_cat = isset($_GET['filter_cat']) ? (int)$_GET['filter_cat'] : 0;
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : "";

$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if ($search != "") {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($filter_cat > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $filter_cat;
    $types .= "i";
}

if ($filter_status == "out") {
    $sql .= " AND p.quantity = 0";
} elseif ($filter_status == "low") {
    $sql .= " AND p.quantity > 0 AND p.quantity <= 10";
} elseif ($filter_status == "in") {
    $sql .= " AND p.quantity > 10";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
?>

<table class="table table-hover mb-0 align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Buy</th>
            <th>Sell</th>
            <th>Last Updated</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if($products->num_rows > 0): ?>
            <?php while($row = $products->fetch_assoc()): 
                $qty = (int)$row['quantity'];
                $disable_minus = "";
                
                if ($qty == 0) {
                    $status_badge = "<span class='badge bg-danger'>Out of Stock</span>";
                    $qty_class = "text-danger fw-bold";
                    $row_class = "table-danger";
                    $disable_minus = "disabled";
                } elseif ($qty <= 10) {
                    $status_badge = "<span class='badge bg-warning text-dark'>Low Stock</span>";
                    $qty_class = "text-warning fw-bold";
                    $row_class = "table-warning";
                } else {
                    $status_badge = "<span class='badge bg-success'>In Stock</span>"; 
                    $qty_class = "text-success fw-bold";
                    $row_class = "table-success";
                }
            ?>
            <tr class="<?= $row_class ?>">
                <td><?= $row['id'] ?></td>
                <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                <td><span class="badge bg-secondary"><?= $row['cat_name'] ?? 'None' ?></span></td>
                
                <td class="<?= $qty_class ?> text-center">
                    <div class="fw-bold" style="font-size: 1.3rem;" id="qty-<?= $row['id'] ?>"><?= $qty ?></div>
                    
                    <div class="d-flex justify-content-center mt-1 gap-1">
                        <button onclick="updateStock(<?= $row['id'] ?>, 'decrease', this)" class="btn btn-sm btn-outline-danger" style="padding: 2px 8px;" <?= $disable_minus ?>>-</button>
                        <button onclick="updateStock(<?= $row['id'] ?>, 'increase', this)" class="btn btn-sm btn-outline-success" style="padding: 2px 8px;">+</button>
                    </div>
                    
                    <div class="mt-1" id="status-<?= $row['id'] ?>"><?= $status_badge ?></div>
                </td>
                
                <td><?= number_format($row['buy_price'], 2) ?></td>
                <td><?= number_format($row['sale_price'], 2) ?></td>
                
                <td class="text-muted small" id="date-<?= $row['id'] ?>">
                    <?= date('M d, Y - h:i A', strtotime($row['updated_at'])) ?>
                </td>
                
                <td>
                    <a href="products.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="products.php?del=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No products found matching your criteria.</td></tr>
        <?php endif; ?>
    </tbody>
</table>