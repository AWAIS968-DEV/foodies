<?php
require_once 'includes/auth.php';

// Require login to access profile
$auth->requireLogin('login.php');

// Get current user data
$user = $auth->getCurrentUser();

$success = '';
$error = '';

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $upload_dir = 'uploads/profile_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $file_name = 'user_' . $user['id'] . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        $error = 'Only JPG, JPEG, PNG & GIF files are allowed.';
    } 
    // Check file size (max 2MB)
    elseif ($_FILES['profile_image']['size'] > 2097152) {
        $error = 'File is too large. Maximum size is 2MB.';
    } 
    else {
        // Delete old profile image if it exists
        if (!empty($user['profile_image']) && file_exists($upload_dir . $user['profile_image'])) {
            unlink($upload_dir . $user['profile_image']);
        }
        
        // Try to upload new file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Update database with new image path
            $db = $auth->getDbConnection();
            $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $file_name, $user['id']);
            
            if ($stmt->execute()) {
                // Update all relevant session variables
                $_SESSION['user_profile_image'] = $file_name;
                
                // Update user data in the current request
                $user['profile_image'] = $file_name;
                
                // Clear any cached user data
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate(realpath('includes/auth.php'), true);
                }
                
                // Get fresh user data
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                // Update session with fresh data
                $_SESSION['user'] = $user;
                
                $success = 'Profile image updated successfully!';
                
                // Redirect to refresh the page and update all instances
                // Add a timestamp to prevent caching
                header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1&t=" . time());
                exit();
            } else {
                // Delete the uploaded file if database update fails
                unlink($target_file);
                $error = 'Failed to update profile image in database.';
            }
        } else {
            $error = 'Error uploading file. Please try again.';
        }
    }
}

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate input
    if (empty($name) || empty($email)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Get database connection from auth class
        $db = $auth->getDbConnection();
        
        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email is already taken by another account.';
        } else {
            // Update user profile with updated_at timestamp
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user['id']);
            
            if ($stmt->execute()) {
                // Update session with new email if changed
                if ($email !== $user['email']) {
                    $_SESSION['user_email'] = $email;
                }
                
                // Refresh user data
                $user = $auth->getCurrentUser();
                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Process password change form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            
            if (password_verify($current_password, $user_data['password_hash'])) {
                // Update password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_password_hash, $user['id']);
                
                if ($stmt->execute()) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to update password. Please try again.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        } else {
            $error = 'User not found.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Foodies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --dark-color: #2d3436;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            display: block;
            border: 3px solid var(--primary-color);
            padding: 3px;
        }
        
        .profile-name {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .profile-email {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1.25rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 107, 0.15);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #ff5252;
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .password-input-group {
            position: relative;
        }
        
        .nav-pills .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            padding: 1.5rem 0;
        }
    </style>
</head>
<body>
    <!-- Profile Content -->
    <div class="profile-container mt-5">
        <div class="profile-header text-center">
            <div class="d-flex justify-content-center position-relative mx-auto" style="width: fit-content;">
                <div class="position-relative">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                             class="profile-avatar rounded-circle border border-3 border-white shadow-sm" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             alt="Profile" 
                             id="profile-avatar">
                    <?php else: ?>
                        <div class="profile-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle border border-3 border-white shadow-sm" 
                             style="width: 150px; height: 150px; font-size: 4rem;">
                            <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle p-2" 
                            data-bs-toggle="modal" data-bs-target="#uploadImageModal"
                            style="width: 40px; height: 40px;">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>
            <h1 class="profile-name"><?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : 'User'; ?></h1>
            <p class="profile-email"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'user@example.com'; ?></p>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                    <i class="fas fa-user-edit me-1"></i> Edit Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="pill" data-bs-target="#password" type="button" role="tab">
                    <i class="fas fa-key me-1"></i> Change Password
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
            <!-- Edit Profile Tab -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-edit me-2"></i> Personal Information
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php" id="profileForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-key me-2"></i> Change Password
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php" id="passwordForm">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="password-input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <span class="password-toggle" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="password-input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <span class="password-toggle" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="form-text">Password must be at least 8 characters long.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="password-input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div id="passwordMatch" class="form-text"></div>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1" aria-labelledby="uploadImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadImageModalLabel">Upload Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" enctype="multipart/form-data" id="profileImageForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Choose an image (JPG, PNG, GIF, max 2MB)</label>
                            <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/*" required>
                            <div class="form-text">Square images work best. Maximum file size: 2MB</div>
                        </div>
                        <div class="text-center">
                            <div id="imagePreview" class="mt-3"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `
                        <div class="border rounded p-2 d-inline-block">
                            <img src="${event.target.result}" class="img-fluid" style="max-height: 200px;" alt="Preview">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Check if passwords match
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');
            
            function checkPasswords() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value === confirmPassword.value) {
                        passwordMatch.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Passwords match</span>';
                        confirmPassword.setCustomValidity('');
                        return true;
                    } else {
                        passwordMatch.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
                        confirmPassword.setCustomValidity('Passwords do not match');
                        return false;
                    }
                } else {
                    passwordMatch.innerHTML = '';
                    return false;
                }
            }
            
            newPassword.addEventListener('input', checkPasswords);
            confirmPassword.addEventListener('input', checkPasswords);
            
            // Form validation
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(event) {
                    if (!checkPasswords()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
