<?php
date_default_timezone_set('Asia/Colombo');
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

$res = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");

$product_names = [];
$product_quantities = [];
$chart_data = $conn->query("SELECT name, quantity FROM products ORDER BY id ASC");
while($row = $chart_data->fetch_assoc()) {
    $product_names[] = $row['name'];
    $product_quantities[] = $row['quantity'];
}
$product_count = count($product_names);

include 'includes/header.php';
?>

<style>
    .chart-container {
        position: relative; 
        height: 400px; 
        width: 100%;
    }

    .row-out  { background-color: #f8d7da !important; }
    .row-low  { background-color: #fff3cd !important; }
    .row-good { background-color: #d1e7dd !important; }
    .row-out td, .row-low td, .row-good td { vertical-align: middle; }

    @media print {
        /* 1. Strip all margins and paddings to prevent overflow */
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        html, body { 
            height: auto !important; 
            min-height: 0 !important;
            overflow: hidden !important; 
            background: #fff !important; 
        }
        
        /* 2. Hide everything we don't want */
        .no-print, .d-print-none, .navbar, .navbar-custom, footer, #stockChart, .chart-container, .btn { 
            display: none !important; 
        }
        
        /* 3. Make container fit exactly */
        .container, .container-fluid { 
            width: 100% !important; 
            max-width: 100% !important; 
            margin: 0 !important;
        }
        
        /* 4. Remove card styling entirely */
        .card { 
            border: none !important; 
            border-radius: 0 !important; 
            page-break-after: avoid !important; 
            page-break-inside: avoid !important;
        }
        
        .table-responsive { overflow: visible !important; width: 100% !important; }
        table { width: 100% !important; border-collapse: collapse !important; font-size: 11px; }
        table th, table td { border: 1px solid #999 !important; padding: 4px 6px !important; }
        table thead th { background: #1e3c72 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        .row-out td  { background-color: #f8d7da !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .row-low td  { background-color: #fff3cd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .row-good td { background-color: #d1e7dd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        tr { page-break-inside: avoid; }
        .d-flex.justify-content-between { display: block !important; }
        h3 { font-size: 18px !important; margin-bottom: 4px !important; }
        .badge { background: #eee !important; color: #000 !important; border: 1px solid #999; font-size: 9px; padding: 2px 5px !important; }
        tfoot tr { background: #333 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        @page { size: A4; margin: 12mm; }
    }
</style>

<div class="card p-4 mb-4">
    <div class="mb-3 border-bottom pb-3">
        <h3 class="fw-bold mb-1">
            <i class="fas fa-file-invoice text-primary me-2"></i>Stock Report
        </h3>
        <p class="text-muted mb-0 small">Generated on: <?= date('Y-m-d h:i A') ?></p>
        <button onclick="window.print()" class="btn btn-primary btn-sm mt-2 no-print">
            <i class="fas fa-print me-1"></i> Print / PDF
        </button>
    </div>

    <div class="mb-2 small no-print">
        <span class="badge" style="background:#d1e7dd;color:#0a3622;border:1px solid #0a3622;">■ Good Stock (&gt;10)</span>
        <span class="badge" style="background:#fff3cd;color:#664d03;border:1px solid #664d03;">■ Low Stock (1–10)</span>
        <span class="badge" style="background:#f8d7da;color:#58151c;border:1px solid #58151c;">■ Out of Stock (0)</span>
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Buy Price</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand = 0;
            while($row = $res->fetch_assoc()) {
                $qty = (int)$row['quantity'];
                $val = $qty * $row['buy_price'];
                $grand += $val;

                if ($qty == 0) {
                    $row_class   = 'row-out';
                    $status_html = "<span class='badge bg-danger'>Out of Stock</span>";
                } elseif ($qty <= 10) {
                    $row_class   = 'row-low';
                    $status_html = "<span class='badge bg-warning text-dark'>Low Stock</span>";
                } else {
                    $row_class   = 'row-good';
                    $status_html = "<span class='badge bg-success'>In Stock</span>";
                }
            ?>
            <tr class="<?= $row_class ?>">
                <td><?= $row['id'] ?></td>
                <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                <td><span class="badge bg-secondary"><?= $row['cat_name'] ?? 'None' ?></span></td>
                <td class="fw-bold"><?= $qty ?></td>
                <td><?= $status_html ?></td>
                <td>Rs. <?= number_format($row['buy_price'], 2) ?></td>
                <td>Rs. <?= number_format($val, 2) ?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="table-dark fw-bold">
                <td colspan="6" class="text-end">Grand Total:</td>
                <td>Rs. <?= number_format($grand, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="card p-4 no-print d-print-none">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Stock Levels Overview</h5>
    </div>
    <div class="chart-container">
        <canvas id="stockChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    
    const barGradient = ctx.createLinearGradient(0, 0, 0, 400);
    barGradient.addColorStop(0, '#4facfe');
    barGradient.addColorStop(1, '#1e3c72');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($product_names); ?>,
            datasets: [{
                label: 'Available Quantity',
                data: <?php echo json_encode($product_quantities); ?>,
                backgroundColor: barGradient,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.8,
                categoryPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            events: [],
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { 
                        autoSkip: false, 
                        maxRotation: 45, 
                        minRotation: 45,
                        font: { family: 'Segoe UI', size: 11 }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { 
                        color: '#e9ecef', 
                        borderDash: [5, 5]
                    },
                    ticks: {
                        font: { family: 'Segoe UI' },
                        callback: function(value) { return value.toLocaleString(); }
                    }
                }
            },
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>