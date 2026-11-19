<?php
require 'db.php';
if (!isset($_SESSION['user'])) { exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0) {
    // Get current quantity using a prepared statement
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $current_qty = (int)$row['quantity'];
        
        // Calculate new quantity
        if ($action == 'increase') {
            $new_qty = $current_qty + 1;
        } else {
            $new_qty = max(0, $current_qty - 1);
        }
        
        // Update the database using a prepared statement
        $update_stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_qty, $id);
        $update_stmt->execute();
        
        // Determine the new status, colors, and classes
        if ($new_qty == 0) {
            $status_badge = "<span class='badge bg-danger'>Out of Stock</span>";
            $row_class = "table-danger"; 
            $qty_class = "text-danger fw-bold";
        } elseif ($new_qty <= 10) {
            $status_badge = "<span class='badge bg-warning text-dark'>Low Stock</span>";
            $row_class = "table-warning"; 
            $qty_class = "text-warning fw-bold";
        } else {
            $status_badge = "<span class='badge bg-success'>In Stock</span>";
            $row_class = "table-success"; 
            $qty_class = "text-success fw-bold";
        }
        
        // Return data as JSON
        echo json_encode([
            'success' => true,
            'new_qty' => $new_qty,
            'status_badge' => $status_badge,
            'row_class' => $row_class,
            'qty_class' => $qty_class,
            'new_date' => date('M d, Y - h:i A')
        ]);
    }
}
?>