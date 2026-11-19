<?php
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

// Add Product
if (isset($_POST['add'])) {
    $n = trim($_POST['name']); 
    $q = max(0, (int)$_POST['qty']); 
    $b = max(0, (float)$_POST['buy']); 
    $s = max(0, (float)$_POST['sell']); 
    $c = (int)$_POST['category_id'];
    
    $stmt = $conn->prepare("INSERT INTO products (name, quantity, buy_price, sale_price, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siddi", $n, $q, $b, $s, $c);
    $stmt->execute();
    header("Location: products.php");
    exit;
}

// Delete Product
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: products.php");
    exit;
}

// Update Product
if (isset($_POST['update'])) {
    $id = (int)$_POST['id']; 
    $n = trim($_POST['name']); 
    $q = max(0, (int)$_POST['qty']); 
    $b = max(0, (float)$_POST['buy']); 
    $s = max(0, (float)$_POST['sell']); 
    $c = (int)$_POST['category_id'];
    
    $stmt = $conn->prepare("UPDATE products SET name=?, quantity=?, buy_price=?, sale_price=?, category_id=? WHERE id=?");
    $stmt->bind_param("siddii", $n, $q, $b, $s, $c, $id);
    $stmt->execute();
    header("Location: products.php");
    exit;
}

// Get categories once for both the form and the filter
$categories = $conn->query("SELECT * FROM categories");

// Load data for Edit mode
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

include 'includes/header.php';
?>

<h3 class="mb-4 fw-bold">Product Management</h3>

<div class="card p-4 mb-4">
    <h5 class="fw-bold"><?= $edit_data ? 'Edit Product' : 'Add New Product' ?></h5>
    <hr>
    <form method="POST" class="row g-2">
        <?php if($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        
        <div class="col-12 col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Product Name" value="<?= $edit_data ? htmlspecialchars($edit_data['name']) : '' ?>" required>
        </div>
        <div class="col-6 col-md-2">
            <input type="number" name="qty" class="form-control" placeholder="Qty" value="<?= $edit_data ? $edit_data['quantity'] : '' ?>" min="0" oninput="validity.valid||(value='');" required>
        </div>
        <div class="col-6 col-md-2">
            <input type="number" step="0.01" name="buy" class="form-control" placeholder="Buy Price" value="<?= $edit_data ? $edit_data['buy_price'] : '' ?>" min="0" oninput="validity.valid||(value='');" required>
        </div>
        <div class="col-6 col-md-2">
            <input type="number" step="0.01" name="sell" class="form-control" placeholder="Sale Price" value="<?= $edit_data ? $edit_data['sale_price'] : '' ?>" min="0" oninput="validity.valid||(value='');" required>
        </div>
        <div class="col-6 col-md-2">
            <select name="category_id" class="form-select" required>
                <option value="">Category</option>
                <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($edit_data && $edit_data['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-12 col-md-1">
            <?php if($edit_data): ?>
                <button type="submit" name="update" class="btn btn-success w-100"><i class="fas fa-check"></i></button>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-primary w-100"><i class="fas fa-plus"></i></button>
            <?php endif; ?>
        </div>
    </form>
    <?php if($edit_data): ?>
        <div class="mt-2"><a href="products.php" class="text-decoration-none small">Cancel Edit</a></div>
    <?php endif; ?>
</div>

<div class="card p-3 mb-3">
    <div class="row g-2 align-items-center" id="filterForm">
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" id="searchInput" class="form-control border-start-0" placeholder="Search product name...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="filter_cat" id="filterCat" class="form-select">
                <option value="">All Categories</option>
                <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="filter_status" id="filterStatus" class="form-select">
                <option value="">All Status</option>
                <option value="in">In Stock (>10)</option>
                <option value="low">Low Stock (1-10)</option>
                <option value="out">Out of Stock (0)</option>
            </select>
        </div>
        <div class="col-12 col-md-2">
            <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary w-100">Reset</button>
        </div>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive" id="productTableContainer"></div>
</div>

<script>
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === '-' || e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    });
});

const searchInput = document.getElementById('searchInput');
const filterCat = document.getElementById('filterCat');
const filterStatus = document.getElementById('filterStatus');
const container = document.getElementById('productTableContainer');
let searchTimeout;

const urlParams = new URLSearchParams(window.location.search);
const statusParam = urlParams.get('filter_status');
if (statusParam) { filterStatus.value = statusParam; }

function fetchProducts() {
    const search = searchInput.value;
    const cat = filterCat.value;
    const status = filterStatus.value;
    const url = `fetch_products.php?search=${encodeURIComponent(search)}&filter_cat=${cat}&filter_status=${status}`;
    
    fetch(url)
        .then(res => res.text())
        .then(data => { container.innerHTML = data; });
}

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchProducts, 300);
});

filterCat.addEventListener('change', fetchProducts);
filterStatus.addEventListener('change', fetchProducts);

function resetFilters() {
    searchInput.value = "";
    filterCat.value = "";
    filterStatus.value = "";
    fetchProducts();
}

document.addEventListener("DOMContentLoaded", function() { fetchProducts(); });

function updateStock(id, action, btnElement) {
    const url = `update_stock_ajax.php?id=${id}&action=${action}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const qtyElement = document.getElementById(`qty-${id}`);
                qtyElement.innerText = data.new_qty;
                qtyElement.className = 'fw-bold ' + data.qty_class;
                
                const statusElement = document.getElementById(`status-${id}`);
                statusElement.innerHTML = data.status_badge;
                
                const dateElement = document.getElementById(`date-${id}`);
                if (dateElement) {
                    dateElement.innerText = data.new_date;
                }
                
                const row = btnElement.closest('tr');
                if (row) {
                    row.classList.remove('table-danger', 'table-warning', 'table-success');
                    row.classList.add(data.row_class);
                }
                
                const minusBtn = btnElement.parentElement.querySelector('.btn-outline-danger');
                if (data.new_qty == 0) {
                    minusBtn.disabled = true;
                } else {
                    minusBtn.disabled = false;
                }
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php include 'includes/footer.php'; ?>