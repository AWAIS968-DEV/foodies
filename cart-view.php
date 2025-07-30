<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total_items' => 0,
        'subtotal' => 0.00,
        'tax' => 0.00,
        'delivery_fee' => 0.00,
        'total' => 0.00
    ];
}

// Handle remove item from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $item_id = (int)$_GET['remove'];
    
    if (isset($_SESSION['cart']['items'][$item_id])) {
        // Remove the item from the cart
        unset($_SESSION['cart']['items'][$item_id]);
        
        // Recalculate cart totals
        updateCartTotals();
        
        // Redirect to prevent form resubmission
        header('Location: cart-view.php');
        exit;
    }
}

// Handle update quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $item_id => $quantity) {
        $item_id = (int)$item_id;
        $quantity = (int)$quantity;
        
        if (isset($_SESSION['cart']['items'][$item_id])) {
            if ($quantity > 0) {
                // Update quantity
                $_SESSION['cart']['items'][$item_id]['quantity'] = $quantity;
            } else {
                // Remove item if quantity is 0
                unset($_SESSION['cart']['items'][$item_id]);
            }
        }
    }
    
    // Recalculate cart totals
    updateCartTotals();
    
    // Redirect to prevent form resubmission
    header('Location: cart-view.php');
    exit;
}

// Function to update cart totals
function updateCartTotals() {
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($_SESSION['cart']['items'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $subtotal += $item_total;
        $total_items += $item['quantity'];
    }
    
    // Calculate tax (10% of subtotal)
    $tax_rate = 0.10;
    $tax = $subtotal * $tax_rate;
    
    // Calculate delivery fee ($5.00 for orders under $50, free otherwise)
    $delivery_fee = ($subtotal < 50.00) ? 5.00 : 0.00;
    
    // Calculate total
    $total = $subtotal + $tax + $delivery_fee;
    
    // Update cart totals
    $_SESSION['cart']['total_items'] = $total_items;
    $_SESSION['cart']['subtotal'] = $subtotal;
    $_SESSION['cart']['tax'] = $tax;
    $_SESSION['cart']['delivery_fee'] = $delivery_fee;
    $_SESSION['cart']['total'] = $total;
}

// Get cart items
$cart_items = $_SESSION['cart']['items'] ?? [];
$subtotal = $_SESSION['cart']['subtotal'] ?? 0.00;
$tax = $_SESSION['cart']['tax'] ?? 0.00;
$delivery_fee = $_SESSION['cart']['delivery_fee'] ?? 0.00;
$total = $_SESSION['cart']['total'] ?? 0.00;
$total_items = $_SESSION['cart']['total_items'] ?? 0;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Your Cart</h2>
                <a href="menu.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
            
            <?php if (empty($cart_items)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                        <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" id="cartForm">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50%;">Item</th>
                                            <th class="text-center" style="width: 20%;">Quantity</th>
                                            <th class="text-end" style="width: 20%;">Price</th>
                                            <th style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $index => $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'images/placeholder.jpg'); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                             class="rounded me-3" style="width: 80px; height: 60px; object-fit: cover;">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <p class="text-muted small mb-0">
                                                                <?php echo '$' . number_format($item['price'], 2); ?>
                                                                <?php if (!empty($item['special_requests'])): ?>
                                                                    <br>
                                                                    <span class="text-muted">
                                                                        <i class="fas fa-comment-alt me-1"></i>
                                                                        <?php echo htmlspecialchars($item['special_requests']); ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($item['modifiers'])): ?>
                                                                    <br>
                                                                    <?php foreach ($item['modifiers'] as $modifier): ?>
                                                                        <span class="text-muted small">
                                                                            + <?php echo htmlspecialchars($modifier['name']); ?> 
                                                                            ($<?php echo number_format($modifier['price'], 2); ?>)
                                                                        </span><br>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm" style="width: 110px;">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn" data-action="decrease">-</button>
                                                        <input type="number" name="quantities[<?php echo $index; ?>]" 
                                                               class="form-control text-center quantity-input" 
                                                               value="<?php echo $item['quantity']; ?>" min="1" 
                                                               data-item-id="<?php echo $index; ?>">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-btn" data-action="increase">+</button>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    $<?php echo number_format(($item['price'] * $item['quantity']), 2); ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="?remove=<?php echo $index; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to remove this item?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-2"></i>Update Cart
                                </button>
                                <a href="menu.php" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>Add More Items
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo $total_items; ?> items):</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>
                                <?php if ($delivery_fee > 0): ?>
                                    $<?php echo number_format($delivery_fee, 2); ?>
                                    <?php if ($subtotal > 0 && $subtotal < 50): ?>
                                        <small class="text-muted">(Free delivery on orders over $50)</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-success">Free</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="mb-0">Total:</h5>
                            <h5 class="mb-0">$<?php echo number_format($total, 2); ?></h5>
                        </div>
                        
                        <div class="d-grid">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                Proceed to Checkout
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="small text-muted mb-0">
                                <i class="fas fa-lock me-1"></i>
                                Secure Checkout
                            </p>
                            <div class="mt-2">
                                <img src="images/payment-methods.png" alt="Payment Methods" style="height: 24px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Code -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Promo Code</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter promo code">
                            <button class="btn btn-outline-secondary" type="button">Apply</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <!-- Customer Support -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="me-3 text-primary">
                            <i class="fas fa-phone-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6>Call Us</h6>
                            <p class="mb-0">
                                <a href="tel:+1234567890">(123) 456-7890</a><br>
                                <small class="text-muted">Available 24/7</small>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="fas fa-comment-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6>Chat With Us</h6>
                            <p class="mb-0">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#chatModal">Start a chat</a><br>
                                <small class="text-muted">We're online 9am-9pm EST</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Return Policy -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Delivery Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="me-3 text-primary">
                            <i class="fas fa-truck fa-2x"></i>
                        </div>
                        <div>
                            <h6>Free Delivery</h6>
                            <p class="mb-0 small">
                                Free delivery on all orders over $50. Otherwise, a $5 delivery fee applies.
                            </p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <h6>Delivery Time</h6>
                            <p class="mb-0 small">
                                Estimated delivery time is 30-45 minutes. You can track your order in real-time.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recently Viewed -->
            <?php if (!empty($cart_items)): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">You May Also Like</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php
                            // Sample recommended items - in a real app, this would come from a recommendation system
                            $recommended_items = [
                                ['id' => 101, 'name' => 'Garlic Bread', 'price' => 4.99, 'image' => 'images/garlic-bread.jpg'],
                                ['id' => 102, 'name' => 'Chocolate Brownie', 'price' => 5.99, 'image' => 'images/brownie.jpg'],
                                ['id' => 103, 'name' => 'Caesar Salad', 'price' => 8.99, 'image' => 'images/salad.jpg']
                            ];
                            
                            foreach ($recommended_items as $item):
                            ?>
                                <a href="menu-item.php?id=<?php echo $item['id']; ?>" class="list-group-item list-group-item-action border-0 px-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="rounded me-3" style="width: 64px; height: 48px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <p class="mb-0 text-primary">$<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                        <div class="ms-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary add-to-cart-btn" 
                                                    data-item-id="<?php echo $item['id']; ?>"
                                                    data-item-name="<?php echo htmlspecialchars($item['name']); ?>"
                                                    data-item-price="<?php echo $item['price']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to Cart Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="modalItemImage" src="" alt="" class="img-fluid rounded mb-3" style="max-height: 200px;">
                    <h4 id="modalItemName"></h4>
                    <h5 class="text-primary mb-4">$<span id="modalItemPrice">0.00</span></h5>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <div class="input-group" style="max-width: 150px;">
                        <button type="button" class="btn btn-outline-secondary" id="modalDecreaseQty">-</button>
                        <input type="number" class="form-control text-center" id="modalQuantity" value="1" min="1">
                        <button type="button" class="btn btn-outline-secondary" id="modalIncreaseQty">+</button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="specialRequests" class="form-label">Special Instructions</label>
                    <textarea class="form-control" id="specialRequests" rows="2" 
                              placeholder="Any special requests? (e.g., no onions, extra sauce)"></textarea>
                </div>
                
                <div id="itemModifiers">
                    <!-- Modifiers will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addToCartBtn">
                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const input = this.closest('.input-group').querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (action === 'increase') {
                input.value = value + 1;
            } else if (action === 'decrease' && value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    // Modal quantity buttons
    const modalQuantityInput = document.getElementById('modalQuantity');
    const modalDecreaseBtn = document.getElementById('modalDecreaseQty');
    const modalIncreaseBtn = document.getElementById('modalIncreaseQty');
    
    if (modalDecreaseBtn && modalIncreaseBtn) {
        modalDecreaseBtn.addEventListener('click', function() {
            let value = parseInt(modalQuantityInput.value);
            if (value > 1) {
                modalQuantityInput.value = value - 1;
            }
        });
        
        modalIncreaseBtn.addEventListener('click', function() {
            let value = parseInt(modalQuantityInput.value);
            modalQuantityInput.value = value + 1;
        });
    }
    
    // Add to cart button in recommended items
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const itemPrice = parseFloat(this.getAttribute('data-item-price'));
            const itemImage = this.closest('.card').querySelector('img')?.src || '';
            
            // Set modal content
            document.getElementById('modalItemName').textContent = itemName;
            document.getElementById('modalItemPrice').textContent = itemPrice.toFixed(2);
            document.getElementById('modalItemImage').src = itemImage;
            document.getElementById('modalItemImage').alt = itemName;
            
            // Reset form
            document.getElementById('modalQuantity').value = 1;
            document.getElementById('specialRequests').value = '';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
            modal.show();
            
            // Load modifiers (in a real app, this would be an AJAX call)
            loadItemModifiers(itemId);
        });
    });
    
    // Function to load item modifiers (sample implementation)
    function loadItemModifiers(itemId) {
        // In a real app, this would be an AJAX call to get modifiers for the item
        const modifiersContainer = document.getElementById('itemModifiers');
        
        // Sample modifiers data
        const modifiers = [
            {
                id: 'sauce',
                name: 'Sauce',
                required: false,
                multiple: false,
                options: [
                    { id: 'bbq', name: 'BBQ Sauce', price: 0.50 },
                    { id: 'garlic', name: 'Garlic Sauce', price: 0.50 },
                    { id: 'hot', name: 'Hot Sauce', price: 0.50 }
                ]
            },
            {
                id: 'extras',
                name: 'Extras',
                required: false,
                multiple: true,
                options: [
                    { id: 'cheese', name: 'Extra Cheese', price: 1.50 },
                    { id: 'bacon', name: 'Bacon', price: 2.00 },
                    { id: 'mushrooms', name: 'Mushrooms', price: 1.00 }
                ]
            }
        ];
        
        // Render modifiers
        let html = '';
        
        modifiers.forEach(modifier => {
            html += `
                <div class="mb-3">
                    <label class="form-label">${modifier.name} ${modifier.required ? '<span class="text-danger">*</span>' : ''}</label>
                    <div class="list-group">
            `;
            
            if (modifier.multiple) {
                // Checkbox group for multiple selections
                modifier.options.forEach(option => {
                    html += `
                        <label class="list-group-item">
                            <input class="form-check-input me-2" type="checkbox" 
                                   name="modifiers[${modifier.id}][]" 
                                   value="${option.id}"
                                   data-price="${option.price}">
                            ${option.name} (+$${option.price.toFixed(2)})
                        </label>
                    `;
                });
            } else {
                // Radio buttons for single selection
                modifier.options.forEach(option => {
                    html += `
                        <label class="list-group-item">
                            <input class="form-check-input me-2" type="radio" 
                                   name="modifiers[${modifier.id}]" 
                                   value="${option.id}"
                                   data-price="${option.price}">
                            ${option.name} ${option.price > 0 ? '(+$' + option.price.toFixed(2) + ')' : ''}
                        </label>
                    `;
                });
            }
            
            html += `
                    </div>
                </div>
            `;
        });
        
        modifiersContainer.innerHTML = html;
    }
    
    // Add to cart button in modal
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            // In a real app, this would send an AJAX request to add the item to the cart
            const itemName = document.getElementById('modalItemName').textContent;
            const quantity = parseInt(document.getElementById('modalQuantity').value);
            const specialRequests = document.getElementById('specialRequests').value;
            
            // Get selected modifiers
            const modifiers = [];
            document.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked').forEach(input => {
                const modifierGroup = input.name.match(/modifiers\[(.*?)\]/)[1];
                const modifierId = input.value;
                const modifierName = input.closest('label').textContent.trim();
                const modifierPrice = parseFloat(input.getAttribute('data-price'));
                
                modifiers.push({
                    id: modifierId,
                    name: modifierName,
                    price: modifierPrice
                });
            });
            
            // Show success message
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.style.zIndex = '1100';
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        Added ${quantity} x ${itemName} to your cart
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Hide the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addToCartModal'));
            modal.hide();
            
            // In a real app, you would update the cart count in the header
            updateCartCount(1);
        });
    }
    
    // Function to update cart count in header
    function updateCartCount(change) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const currentCount = parseInt(cartCount.textContent) || 0;
            const newCount = Math.max(0, currentCount + change);
            cartCount.textContent = newCount;
            
            if (newCount > 0) {
                cartCount.classList.remove('d-none');
            } else {
                cartCount.classList.add('d-none');
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
