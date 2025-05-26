<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Ecommerce'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">
                <i class="bi bi-shop me-2"></i>ShopEasy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-3 mx-1" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-3 mx-1" href="products.php">
                            <i class="bi bi-grid me-1"></i>Products
                        </a>
                    </li>
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 rounded-3 mx-1 bg-warning text-dark" href="../admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i>Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-2">
                        <a class="nav-link position-relative px-3 py-2 rounded-3" href="cart.php">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if (isLoggedIn() && isset($_SESSION['cart_count'])): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $_SESSION['cart_count']; ?>
                                    <span class="visually-hidden">items in cart</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle px-3 py-2 rounded-3 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item py-2" href="orders.php"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light rounded-pill px-3" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light rounded-pill px-3" href="register.php">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>