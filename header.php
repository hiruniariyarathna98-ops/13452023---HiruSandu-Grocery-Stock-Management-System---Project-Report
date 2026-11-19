<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>HiruSandu Grocery - Stock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom px-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-store me-2"></i>HiruSandu Grocery</a>
        
        <button class="navbar-toggler text-white border-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center">
                <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'text-white fw-bold' : '' ?>">Dashboard</a>
                <a href="products.php" class="nav-link <?= $current_page == 'products.php' ? 'text-white fw-bold' : '' ?>">Products</a>
                <a href="categories.php" class="nav-link <?= $current_page == 'categories.php' ? 'text-white fw-bold' : '' ?>">Categories</a>
                <a href="report.php" class="nav-link <?= $current_page == 'report.php' ? 'text-white fw-bold' : '' ?>">Report</a>
                <a href="logout.php" class="btn btn-danger btn-sm ms-lg-3 mt-2 mt-lg-0">Logout</a>
            </div>
        </div>
    </div>
</nav>
<div class="container mt-4">