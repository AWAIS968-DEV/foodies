<?php
require_once 'includes/auth.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';
$name = $email = $username = '';
$errors = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Server-side validation
    if (empty($name)) {
        $errors['name'] = 'Please enter your full name.';
    }
    
    // Username validation
    if (!empty($username)) {
        // Clean up username
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        $username = strtolower($username);
        
        if (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long.';
        } elseif (strlen($username) > 30) {
            $errors['username'] = 'Username cannot exceed 30 characters.';
        } else {
            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $errors['username'] = 'This username is already taken. Please choose another one.';
            }
        }
    } else {
        $errors['username'] = 'Please enter a username.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Please enter your email address.';
    } else {
        // Trim and convert to lowercase for consistency
        $email = strtolower(trim($email));
        
        // Use PHP's built-in filter with a more permissive check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address (e.g., yourname@example.com).';
        }
    }
    
    if (empty($password)) {
        $errors['password'] = 'Please enter a password.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    // If no validation errors, attempt to register
    if (empty($errors)) {
        // Create user account with email, username, and password
        $result = $auth->register($email, $password, $username);
        
        if ($result['success']) {
            // If registration was successful, update the user's full name
            if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $stmt->bind_param('si', $name, $_SESSION['user_id']);
                $stmt->execute();
            }
            
            // Set success message in session to persist across redirect
            $_SESSION['registration_success'] = true;
            
            // Redirect to login page with success message
            header('Location: login.php?registered=1&email=' . urlencode($email));
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Set page title
$page_title = 'Register - Foodies';

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2">Create an Account</h2>
                        <p class="text-muted">Join Foodies and start ordering your favorite meals</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form id="registerForm" method="POST" action="register.php" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                       id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($errors['name']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> Please enter your full name.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                       id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                                       pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($errors['username']); ?>
                                </div>
                            <?php else: ?>
                                <div class="form-text">3-30 characters, letters, numbers, and underscores only</div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> Please enter a valid username.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                                       title="Please enter a valid email address (e.g., yourname@example.com)"
                                       required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($errors['email']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i> Please enter a valid email address.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" name="password" required>
                                <span class="input-group-text password-toggle" id="togglePassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%;" 
                                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="password-requirements mt-2">
                                <small class="d-block text-muted mb-1">Password must contain:</small>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="lengthCheck" disabled>
                                        <label class="form-check-label small" for="lengthCheck">8+ characters</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="uppercaseCheck" disabled>
                                        <label class="form-check-label small" for="uppercaseCheck">Uppercase letter</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="numberCheck" disabled>
                                        <label class="form-check-label small" for="numberCheck">Number</label>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" name="confirm_password" required>
                                <span class="input-group-text password-toggle" id="toggleConfirmPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="mt-2 small" id="passwordMatch"></div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-2">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-primary fw-semibold text-decoration-none">
                                    Sign in here
                                </a>
                            </p>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation and password strength meter
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const togglePassword = document.getElementById('togglePassword');
        
        // Email validation
        if (email) {
            email.addEventListener('input', validateEmail);
            email.addEventListener('blur', validateEmail);
            
            function validateEmail() {
                const emailValue = email.value.trim();
                const emailError = document.getElementById('emailError');
                
                // Simple validation that matches server-side
                if (!emailValue) {
                    setError(email, 'Please enter your email address.');
                    return false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                    setError(email, 'Please enter a valid email address (e.g., yourname@example.com).');
                    return false;
                } else {
                    setSuccess(email);
                    return true;
                }
            }
        }
        
        // Helper functions for validation
        function setError(element, message) {
            const inputGroup = element.closest('.input-group');
            const errorElement = inputGroup.parentElement.querySelector('.invalid-feedback');
            
            if (inputGroup) {
                inputGroup.classList.add('has-validation');
                element.classList.add('is-invalid');
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
            }
        }
        
        function setSuccess(element) {
            const inputGroup = element.closest('.input-group');
            const errorElement = inputGroup.parentElement.querySelector('.invalid-feedback');
            
            if (inputGroup) {
                element.classList.remove('is-invalid');
                element.classList.add('is-valid');
                
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        }
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        
        // Toggle password visibility
        function setupPasswordToggle(toggle, inputId) {
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const input = document.getElementById(inputId);
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle eye icon
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                    
                    // Focus back on the input
                    input.focus();
                });
            }
        }
        
        // Initialize password toggles
        if (togglePassword) setupPasswordToggle(togglePassword, 'password');
        if (toggleConfirmPassword) setupPasswordToggle(toggleConfirmPassword, 'confirm_password');
        
        // Password strength checker
        function checkPasswordStrength(password) {
            if (!password) return 0;
            
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Update requirement checkboxes with animation
            const updateCheckbox = (id, isValid) => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.checked = isValid;
                    checkbox.parentElement.classList.toggle('text-success', isValid);
                    checkbox.parentElement.classList.toggle('text-muted', !isValid);
                }
            };
            
            updateCheckbox('lengthCheck', requirements.length);
            updateCheckbox('uppercaseCheck', requirements.uppercase);
            updateCheckbox('numberCheck', requirements.number);
            
            // Calculate strength score (0-100)
            const strengthScore = Object.values(requirements).filter(Boolean).length;
            const strengthMeter = document.getElementById('passwordStrength');
            if (!strengthMeter) return 0;
            
            let width = 0;
            let strengthClass = '';
            let strengthText = '';
            
            switch(strengthScore) {
                case 0:
                    width = 0;
                    strengthClass = 'bg-danger';
                    strengthText = 'Very Weak';
                    break;
                case 1:
                    width = 25;
                    strengthClass = 'bg-danger';
                    strengthText = 'Weak';
                    break;
                case 2:
                    width = 50;
                    strengthClass = 'bg-warning';
                    strengthText = 'Fair';
                    break;
                case 3:
                    width = 75;
                    strengthClass = 'bg-info';
                    strengthText = 'Good';
                    break;
                case 4:
                    width = 100;
                    strengthClass = 'bg-success';
                    strengthText = 'Strong';
                    break;
            }
            
            // Animate the progress bar
            strengthMeter.style.width = '0';
            setTimeout(() => {
                strengthMeter.style.width = width + '%';
                strengthMeter.className = `progress-bar ${strengthClass} progress-bar-striped progress-bar-animated`;
                strengthMeter.setAttribute('aria-valuenow', width);
                
                // Remove animation after it's done
                setTimeout(() => {
                    strengthMeter.classList.remove('progress-bar-animated', 'progress-bar-striped');
                }, 500);
            }, 10);
            
            return strengthScore;
        }
        
        // Check if passwords match
        function checkPasswordsMatch() {
            const matchElement = document.getElementById('passwordMatch');
            if (!matchElement) return false;
            
            if (!password.value || !confirmPassword.value) {
                matchElement.innerHTML = '';
                confirmPassword.setCustomValidity('');
                return false;
            }
            
            if (password.value === confirmPassword.value) {
                matchElement.innerHTML = `
                    <div class="d-flex align-items-center text-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Passwords match</span>
                    </div>`;
                confirmPassword.setCustomValidity('');
                return true;
            } else {
                matchElement.innerHTML = `
                    <div class="d-flex align-items-center text-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <span>Passwords do not match</span>
                    </div>`;
                confirmPassword.setCustomValidity('Passwords do not match');
                return false;
            }
        }
        
        // Real-time validation for all fields
        function validateField(field) {
            if (!field) return;
            
            const value = field.value.trim();
            const fieldName = field.name;
            const feedbackElement = field.closest('.form-group')?.querySelector('.invalid-feedback');
            
            if (field.required && !value) {
                field.setCustomValidity('This field is required');
                return false;
            }
            
            // Email validation
            if (fieldName === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    field.setCustomValidity('Please enter a valid email address');
                    return false;
                }
            }
            
            field.setCustomValidity('');
            return true;
        }
        
        // Event listeners
        if (password) {
            password.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                if (confirmPassword && confirmPassword.value) {
                    checkPasswordsMatch();
                }
                
                // Update password validation message
                if (this.value.length > 0 && this.value.length < 8) {
                    this.setCustomValidity('Password must be at least 8 characters long');
                } else if (this.value.length > 0 && !/[A-Z]/.test(this.value)) {
                    this.setCustomValidity('Password must contain at least one uppercase letter');
                } else if (this.value.length > 0 && !/[0-9]/.test(this.value)) {
                    this.setCustomValidity('Password must contain at least one number');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordsMatch);
        }
        
        // Add input event listeners to all form fields
        const formFields = form?.querySelectorAll('input, select, textarea');
        formFields?.forEach(field => {
            field.addEventListener('input', () => validateField(field));
            field.addEventListener('blur', () => validateField(field));
        });
        
        // Form submission
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // Validate all fields
                let isValid = true;
                formFields?.forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });
                
                // Check passwords match
                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    checkPasswordsMatch();
                    isValid = false;
                }
                
                // If form is valid, submit it
                if (isValid) {
                    // Add loading state to submit button
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating Account...
                    `;
                    
                    // Submit the form
                    form.submit();
                } else {
                    // Show validation errors
                    form.classList.add('was-validated');
                    
                    // Scroll to first invalid field
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }
                }
            }, false);
        }
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
