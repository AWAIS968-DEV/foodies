<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Initialize variables
$name = $email = $subject = $message = '';
$errors = [];
$success = false;

// Get user data if logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    $name = $user['name'] ?? '';
    $email = $user['email'] ?? '';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate input
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    }
    
    // If no validation errors, process the form
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Save the message to a database
        // 2. Send an email notification to the admin
        // 3. Send a confirmation email to the user
        
        // For now, we'll just set a success message
        $success = true;
        
        // Clear form
        $name = $email = $subject = $message = '';
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                <p class="lead text-muted">Have questions or feedback? We'd love to hear from you!</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Message Sent Successfully!</h5>
                            <p class="mb-0">Thank you for contacting us. We'll get back to you as soon as possible.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <h5 class="alert-heading mb-2">Please fix the following issues:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Our Location</h5>
                                    <p class="text-muted mb-0">123 Foodie Street<br>New York, NY 10001</p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-phone-alt text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Phone Number</h5>
                                    <p class="text-muted mb-0">
                                        <a href="tel:+1234567890" class="text-reset">(123) 456-7890</a><br>
                                        <small>Mon-Fri, 9am-9pm EST</small>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Email Address</h5>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:info@foodies.com" class="text-reset">info@foodies.com</a><br>
                                        <small>We'll respond within 24 hours</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Send us a Message</h5>
                            
                            <form method="POST" action="contact.php" id="contactForm" novalidate>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name); ?>" required>
                                    <div class="invalid-feedback">Please enter your name.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email); ?>" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="" disabled selected>Select a subject</option>
                                        <option value="General Inquiry" <?php echo ($subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Order Issue" <?php echo ($subject === 'Order Issue') ? 'selected' : ''; ?>>Order Issue</option>
                                        <option value="Feedback" <?php echo ($subject === 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                        <option value="Partnership" <?php echo ($subject === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                        <option value="Other" <?php echo ($subject === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a subject.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              required><?php echo htmlspecialchars($message); ?></textarea>
                                    <div class="invalid-feedback">Please enter your message (minimum 10 characters).</div>
                                    <div class="form-text">Minimum 10 characters. Currently: <span id="charCount">0</span> characters</div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <div class="text-center">
                        <h5 class="mb-3">Frequently Asked Questions</h5>
                        <p class="text-muted mb-4">Check out our FAQ section for quick answers to common questions.</p>
                        <a href="faq.php" class="btn btn-outline-primary">
                            <i class="far fa-question-circle me-2"></i>View FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps Embed -->
<div class="container-fluid p-0 mt-5">
    <div class="ratio ratio-21x9">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215256912421!2d-73.98784468459378!3d40.75798567932684!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy"
            title="Our Location on Google Maps">
        </iframe>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('contactForm');
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    // Update character count
    if (messageInput && charCount) {
        // Initial count
        charCount.textContent = messageInput.value.length;
        
        // Update on input
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    }
    
    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }
    
    if (messageInput) {
        // Initial resize
        autoResize(messageInput);
        
        // Resize on input
        messageInput.addEventListener('input', function() {
            autoResize(this);
        });
    }
});
</script>

<style>
/* Custom styles for the contact page */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 0.75rem;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

.bg-primary-opacity-10 {
    background-color: rgba(13, 110, 253, 0.1);
}

/* Style for the map container */
.ratio-21x9 {
    --bs-aspect-ratio: calc(9 / 21 * 100%);
}

/* Style for form controls */
.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Style for the textarea */
textarea.form-control {
    min-height: 120px;
    resize: none;
}

/* Style for the submit button */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

/* Style for the FAQ section */
.faq-item {
    border-bottom: 1px solid #eee;
    padding: 1rem 0;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.faq-answer {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
}
</style>

<?php require_once 'includes/footer.php'; ?>
