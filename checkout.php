<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
$auth->requireLogin('login.php?redirect=checkout.php');

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    header('Location: menu.php');
    exit;
}

// Get user data
$user = $auth->getCurrentUser();
$user_id = $user['id'];

// Check for form resubmission
if (isset($_SESSION['order_submitted']) && $_SESSION['order_submitted'] === true) {
    // Clear the session flag
    unset($_SESSION['order_submitted']);
    
    // If this is a GET request after a redirect from a successful POST, redirect to order confirmation
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_SESSION['last_order_id'])) {
            $order_id = $_SESSION['last_order_id'];
            unset($_SESSION['last_order_id']);
            header('Location: order-confirmation.php?order_id=' . $order_id);
            exit();
        } else {
            // If for some reason we don't have an order ID, redirect to orders page
            header('Location: orders.php');
            exit();
        }
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $special_instructions = filter_input(INPUT_POST, 'special_instructions', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (!$email) $errors[] = 'Valid email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Delivery address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($postal_code)) $errors[] = 'Postal code is required';
    if (empty($payment_method)) $errors[] = 'Payment method is required';
    
    // If no validation errors, process the order
    if (empty($errors)) {
        try {
            // Start transaction
            $db = getDBConnection();
            $db->begin_transaction();
            
            // 1. Create order record
            $stmt = $db->prepare("
                INSERT INTO orders (user_id, status, total_amount, delivery_address, city, postal_code, 
                                  payment_method, special_instructions, contact_phone, contact_email)
                VALUES (?, 'New', ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $total_amount = $_SESSION['cart']['total'];
            $stmt->bind_param('idsssssss', 
                $user_id, 
                $total_amount,
                $address,
                $city,
                $postal_code,
                $payment_method,
                $special_instructions,
                $phone,
                $email
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create order');
            }
            
            $order_id = $db->insert_id;
            
            // 2. Add order items
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, menu_item_id, quantity, price, special_requests)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($_SESSION['cart']['items'] as $item) {
                $stmt->bind_param('iiids', 
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price'],
                    $item['special_requests']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add order items');
                }
                
                // Add order item modifiers if any
                if (!empty($item['modifiers'])) {
                    $item_id = $db->insert_id;
                    $mod_stmt = $db->prepare("
                        INSERT INTO order_item_modifiers (order_item_id, modifier_id, price)
                        VALUES (?, ?, ?)
                    ");
                    
                    foreach ($item['modifiers'] as $modifier) {
                        $mod_stmt->bind_param('iid', 
                            $item_id,
                            $modifier['id'],
                            $modifier['price']
                        );
                        
                        if (!$mod_stmt->execute()) {
                            throw new Exception('Failed to add item modifiers');
                        }
                    }
                }
            }
            
            // Commit transaction
            $db->commit();
            
            // 5. Clear cart and redirect to thank you page
            unset($_SESSION['cart']);
            
            // Store order ID in session for the confirmation page
            $_SESSION['last_order_id'] = $order_id;
            
            // Set a flag to prevent form resubmission
            $_SESSION['order_submitted'] = true;
            
            // Redirect to thank you page with a 303 status code to prevent resubmission
            header('Location: order-confirmation.php?order_id=' . $order_id, true, 303);
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            if (isset($db)) {
                $db->rollback();
            }
            $errors[] = 'An error occurred while processing your order. Please try again.';
            error_log('Order processing error: ' . $e->getMessage());
        }
    }
}

// Get user's default address if available
$default_address = [
    'name' => $user['name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'address' => $user['address'] ?? '',
    'city' => $user['city'] ?? '',
    'postal_code' => $user['postal_code'] ?? ''
];

// Calculate order summary
$subtotal = $_SESSION['cart']['subtotal'] ?? 0;
$tax_rate = 0.10; // 10% tax
$tax = $subtotal * $tax_rate;
$delivery_fee = 5.00; // Fixed delivery fee
$total = $subtotal + $tax + $delivery_fee;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Checkout</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5>Please fix the following errors:</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="checkoutForm" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Delivery Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($default_address['name']); ?>" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($default_address['email']); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($default_address['phone']); ?>" required>
                            <div class="invalid-feedback">Please enter your phone number.</div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Delivery Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" 
                                      required><?php echo htmlspecialchars($default_address['address']); ?></textarea>
                            <div class="invalid-feedback">Please enter your delivery address.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($default_address['city']); ?>" required>
                                <div class="invalid-feedback">Please enter your city.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($default_address['postal_code']); ?>" required>
                                <div class="invalid-feedback">Please enter your postal code.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="special_instructions" name="special_instructions" 
                                     rows="2" placeholder="Any special delivery instructions?"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="credit_card" value="credit_card" checked required>
                            <label class="form-check-label" for="credit_card">
                                <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="paypal" value="paypal">
                            <label class="form-check-label" for="paypal">
                                <i class="fab fa-paypal me-2"></i>PayPal
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" 
                                   id="cash_on_delivery" value="cash_on_delivery">
                            <label class="form-check-label" for="cash_on_delivery">
                                <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                            </label>
                        </div>
                        
                        <!-- Credit Card Form (initially hidden) -->
                        <div id="creditCardForm" class="mt-4">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" 
                                           placeholder="1234 5678 9012 3456">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" 
                                           placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" 
                                       placeholder="John Doe">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Place Order - $<?php echo number_format($total, 2); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <?php foreach ($_SESSION['cart']['items'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                    <div class="text-muted small">
                                        <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?>
                                        <?php if (!empty($item['special_requests'])): ?>
                                            <div class="text-muted small">
                                                <i>Note: <?php echo htmlspecialchars($item['special_requests']); ?></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['modifiers'])): ?>
                                            <div class="text-muted small">
                                                <?php foreach ($item['modifiers'] as $modifier): ?>
                                                    + <?php echo htmlspecialchars($modifier['name']); ?> 
                                                    ($<?php echo number_format($modifier['price'], 2); ?>)<br>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Tax (<?php echo ($tax_rate * 100); ?>%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Delivery Fee:</span>
                        <span>$<?php echo number_format($delivery_fee, 2); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                    
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Your personal data will be used to process your order and for other purposes described in our privacy policy.
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="small">
                        <i class="fas fa-phone-alt me-2"></i> Call us: <a href="tel:+1234567890">(123) 456-7890</a>
                    </p>
                    <p class="small mb-0">
                        <i class="fas fa-envelope me-2"></i> Email: <a href="mailto:support@foodies.com">support@foodies.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict';
    
    // Fetch the form we want to apply custom Bootstrap validation styles to
    var form = document.getElementById('checkoutForm');
    
    // Toggle credit card form based on payment method
    var paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    var creditCardForm = document.getElementById('creditCardForm');
    
    function toggleCreditCardForm() {
        var selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        creditCardForm.style.display = (selectedMethod === 'credit_card') ? 'block' : 'none';
    }
    
    // Add event listeners to payment method radio buttons
    paymentMethods.forEach(function(method) {
        method.addEventListener('change', toggleCreditCardForm);
    });
    
    // Initial toggle
    toggleCreditCardForm();
    
    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
    
    // Format credit card number
    var cardNumber = document.getElementById('card_number');
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            var matches = value.match(/\d{4,16}/g);
            var match = matches && matches[0] || '';
            var parts = [];
            
            for (var i = 0, len = match.length; i < len; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                e.target.value = parts.join(' ');
            }
        });
    }
    
    // Format expiry date
    var expiryDate = document.getElementById('expiry_date');
    if (expiryDate) {
        expiryDate.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            e.target.value = value;
        });
    }
})();
</script>

<?php require_once 'includes/footer.php'; ?>
