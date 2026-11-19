<?php
date_default_timezone_set('Asia/Colombo');
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

// Add Category
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        header("Location: categories.php");
        exit;
    }
}

// Update Category
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        header("Location: categories.php");
        exit;
    }
}

// Delete Category
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    
    // Check if products are assigned to this category
    $check = $conn->query("SELECT id FROM products WHERE category_id = $id");
    if ($check->num_rows > 0) {
        $error = "Cannot delete! Products are assigned to this category.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: categories.php");
        exit;
    }
}

// Load data for Edit mode
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");

include 'includes/header.php';
?>

<h3 class="mb-4 fw-bold">Category Management</h3>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-3 mb-3">
            <h5 class="fw-bold"><?= $edit_data ? 'Edit Category' : 'Add New Category' ?></h5>
            <hr>
            <form method="POST">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Category Name" value="<?= $edit_data ? htmlspecialchars($edit_data['name']) : '' ?>" required>
                </div>
                
                <?php if($edit_data): ?>
                    <button type="submit" name="update" class="btn btn-success w-100"><i class="fas fa-check"></i> Update Category</button>
                    <div class="mt-2 text-center"><a href="categories.php" class="text-decoration-none small">Cancel Edit</a></div>
                <?php else: ?>
                    <button type="submit" name="add" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Category</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-0 overflow-hidden">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr><th>ID</th><th>Category Name</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if($categories->num_rows > 0): ?>
                        <?php while($row = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <a href="categories.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="categories.php?del=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No categories found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>