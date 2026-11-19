<?php
require 'db.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $u = trim($_POST['username']);
    $p = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($p, $user['password'])) {
            session_regenerate_id(true); // Security: Prevent session fixation
            $_SESSION['user'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - HiruSandu Grocery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="height:100vh; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
<div class="container" style="max-width:400px;">
    <div class="card p-4 shadow-lg border-0" style="border-radius: 15px;">
        <div class="text-center mb-4">
            <i class="fas fa-boxes-stacked fa-3x text-primary mb-2"></i>
            <h4 class="fw-bold">HiruSandu Grocery</h4>
            <p class="text-muted small">Stock Management System</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
        </form>
    </div>
</div>
</body>
</html>