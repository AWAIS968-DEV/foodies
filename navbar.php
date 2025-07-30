<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isset($_SESSION['user_id']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <!-- Brand/logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fas fa-utensils me-2"></i>
            <span>Foodies</span>
        </a>
        
        <!-- Mobile menu button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" 
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Left-aligned navigation items -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item px-2">
                    <a class="nav-link <?= ($current_page === 'index.php') ? 'active fw-bold' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-home d-lg-none d-inline-block me-2"></i>Home
                    </a>
                </li>
                
                <?php if ($is_logged_in): ?>
                    <li class="nav-item px-2">
                        <a class="nav-link <?= ($current_page === 'dashboard.php') ? 'active fw-bold' : ''; ?>" 
                           href="dashboard.php">
                            <i class="fas fa-tachometer-alt d-lg-none d-inline-block me-2"></i>Dashboard
                        </a>
                    </li>

                <?php endif; ?>
            </ul>
            
            <!-- Right-aligned user controls -->
            <ul class="navbar-nav ms-auto">
                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center px-2" href="#" id="userDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($_SESSION['user_profile_image'])): ?>
                                <img src="uploads/profile_images/<?= htmlspecialchars($_SESSION['user_profile_image']) ?>" 
                                     class="user-avatar rounded-circle flex-shrink-0" 
                                     style="width: 32px; height: 32px; min-width: 32px; object-fit: cover;"
                                     alt="Profile">
                            <?php else: ?>
                                <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                     style="width: 32px; height: 32px; min-width: 32px;">
                                    <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <span class="ms-2 d-none d-lg-inline text-truncate" style="max-width: 120px;"><?= htmlspecialchars($_SESSION['username'] ?? 'Account') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'login.php') ? 'active fw-bold' : ''; ?>" 
                           href="login.php">
                            <i class="fas fa-sign-in-alt d-lg-none d-inline-block me-2"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ms-lg-2 mt-2 mt-lg-0" 
                           href="register.php">
                            <i class="fas fa-user-plus d-lg-none d-inline-block me-2"></i>Sign Up
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Custom navbar styles */
    .navbar {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
    }
    
    /* Adjust navbar toggler for better mobile experience */
    .navbar-toggler {
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
        line-height: 1;
    }
    
    /* Profile dropdown adjustments */
    .dropdown-menu {
        min-width: 12rem;
    }
    
    /* Ensure dropdown is visible on mobile */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 0.5rem 0;
        }
        
        .navbar-nav .nav-item {
            margin: 0.25rem 0;
        }
        
        .dropdown-menu.show {
            position: static !important;
            transform: none !important;
            margin: 0.25rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        /* Make sure the profile link is properly aligned */
        .navbar-nav .dropdown-menu {
            margin-left: 0;
        }
        
        /* Adjust avatar size and alignment on mobile */
        .user-avatar {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            font-size: 0.8rem;
        }
        
        /* Ensure avatar is properly aligned in the navbar */
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
        }
        
        /* Hide username text on small screens */
        .navbar-nav .d-none.d-lg-inline {
            display: none !important;
        }
    }
    
    /* Base navbar padding */
    .navbar {
        padding: 0.5rem 0;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        transform: scale(1.05);
    }
    
    .nav-link {
        position: relative;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border-radius: 4px;
    }
    
    .nav-link:not(.btn):hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .nav-link.active {
        color: #fff !important;
    }
    
    .dropdown-menu {
        border: none;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-top: 10px;
    }
    
    .dropdown-item {
        padding: 0.5rem 1.25rem;
        transition: all 0.2s;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        padding-left: 1.5rem;
    }
    
    /* Mobile menu adjustments */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 1rem 0;
            background-color: #343a40;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            padding: 0.75rem 1rem;
            text-align: left;
        }
    }
</style>
