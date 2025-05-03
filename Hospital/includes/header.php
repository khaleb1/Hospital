<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body style="
    background: url('/Hospital/assets/img/hospital-bg.png') no-repeat center center fixed;
    background-size: cover;
    background-color:rgb(187, 215, 243);
">
    <nav class="navbar navbar-expand-lg navbar-dark ps-0" style="
        background: url('/Hospital/assets/img/hospital-bg.png') no-repeat center center fixed;
        background-size: cover;
        background-color: rgba(187, 215, 243, 0.9);
    ">
        <div class="container-fluid px-1">  <!-- Changed to container-fluid -->
            <a class="navbar-brand" href="/Hospital/index.php">
                <img src="/Hospital/assets/img/logo.png" alt="Hospital Logo" height="40">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Navigation for logged-in users - removed Dashboard and Home links -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= isset($_SESSION['role']) ? '../logout.php' : 'logout.php' ?>">Logout</a>
                        </li>
                    <?php else: ?>
                        <!-- Removed guest navigation items -->
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Add this divider element -->
    <div style="height: 1px; background: rgba(255,255,255,0.3); box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
    
    <div class="container mt-4" style="background-color: transparent;">
        <!-- content will go here -->
    </div>

