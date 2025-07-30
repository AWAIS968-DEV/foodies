// GSAP Animations for Hero Section
window.addEventListener('DOMContentLoaded', function() {
  // GSAP animation for Featured Recipes cards
  if (document.getElementById('featured-recipes')) {
    gsap.from('#featured-recipes .recipe-card', {
      duration: 1,
      y: 60,
      opacity: 0,
      stagger: 0.15,
      ease: 'power3.out',
      delay: 0.3
    });
  }
  gsap.from('#hero-title', {duration: 1, y: -100, opacity: 0, ease: "bounce.out"});
  gsap.from('#hero-desc', {duration: 1.2, x: -100, opacity: 0, delay: 0.5});
  gsap.from('.btn-warning', {duration: 1, scale: 0.5, opacity: 0, delay: 1});
  gsap.from('.carousel-inner img', {duration: 1, scale: 0.8, opacity: 0, stagger: 0.2, delay: 1.2});

  // Optimized smooth animate menu cards on menu.html
  if (document.getElementById('menu-list')) {
    // Set initial state immediately to prevent flash
    gsap.set('.menu-item', { opacity: 0, y: 30, scale: 0.98 });
    
    // Quick, smooth animation
    gsap.to('.menu-item', {
      duration: 0.6,
      y: 0,
      opacity: 1,
      scale: 1,
      stagger: 0.1,
      ease: 'power2.out',
      delay: 0.1
    });
  }

  // Animate About page image and text
  if (window.location.pathname.includes('about.html')) {
    gsap.from('.img-fluid', {duration: 1, x: -100, opacity: 0});
    gsap.from('.col-lg-6 > h2, .col-lg-6 > p, .col-lg-6 > h4', {
      duration: 1,
      y: 50,
      opacity: 0,
      stagger: 0.2,
      delay: 0.5
    });
  }
});

// jQuery for Contact Form
$(function() {
  $('#contactForm').on('submit', function(e) {
    e.preventDefault();
    $('#formAlert').removeClass('d-none');
    $(this).trigger('reset');
    setTimeout(function() {
      $('#formAlert').addClass('d-none');
    }, 4000);
  });
});

// Recipe Panel Functionality
$(document).ready(function() {
  // Cart data structure
  let cart = {
    items: [],
    subtotal: 0,
    tax: 0,
    total: 0
  };
  
  // Flag to prevent immediate panel closing
  let panelJustOpened = false;

  // Recipe data with categories
  const recipes = {
    'Stuffed Chicken Breast': { price: 1995, category: 'chicken' },
    'Chicken Steak': { price: 2195, category: 'chicken' },
    'Italian Pasta': { price: 1895, category: 'pasta' },
    'Homemade Party Food': { price: 2495, category: 'appetizers' },
    'Fresh Salsa': { price: 1695, category: 'appetizers' },
    'Buffalo Wings': { price: 2395, category: 'chicken' },
    'Grilled Red Snapper': { price: 2695, category: 'seafood' },
    'Seafood Pasta': { price: 2495, category: 'seafood' },
    'Butchers Chicken': { price: 1995, category: 'grilled' }
  };

  // Debug: Check if elements exist
  console.log('Recipe cards found:', $('.recipe-card').length);
  console.log('Plus buttons found:', $('.recipe-card .btn-outline-danger').length);
  console.log('Recipe panel exists:', $('#recipePanel').length);

  // Open recipe panel when recipe card + button is clicked
  $(document).on('click', '.recipe-card .btn-outline-danger', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('Plus button clicked!');
    
    // Get recipe details
    const recipeCard = $(this).closest('.recipe-card');
    const recipeName = recipeCard.find('.card-title').text().trim();
    const recipePrice = parseInt(recipeCard.find('.fw-semibold').text().replace(/[^0-9]/g, ''));
    
    console.log('Recipe:', recipeName, 'Price:', recipePrice);
    
    // Add to cart immediately
    addToCart(recipeName, recipePrice);
    
    // Show panel
    const panel = $('#recipePanel');
    console.log('Panel element:', panel.length);
    
    if (panel.length > 0) {
      // Set flag to prevent immediate closing
      panelJustOpened = true;
      
      panel.removeClass('d-none panel-hidden');
      panel.show(); // Fallback method
      $('body').css('overflow', 'hidden');
      console.log('Panel should be visible now');
      console.log('Panel classes:', panel.attr('class'));
      console.log('Panel display:', panel.css('display'));
      
      // Reset flag after a delay
      setTimeout(function() {
        panelJustOpened = false;
        console.log('Panel opening protection disabled');
      }, 1000);
      
      // GSAP animation for panel entrance (if GSAP is available)
      if (typeof gsap !== 'undefined') {
        gsap.from('.recipe-panel-container', {
          duration: 0.4,
          scale: 0.9,
          opacity: 0,
          y: -20,
          ease: 'power3.out'
        });
      }
    } else {
      console.error('Recipe panel not found in DOM!');
    }
  });

  // TEMPORARILY DISABLED ALL CLOSE HANDLERS FOR DEBUGGING
  console.log('=== ALL CLOSE HANDLERS DISABLED FOR TESTING ===');
  
  // Monitor panel visibility changes
  function monitorPanel() {
    const panel = $('#recipePanel');
    if (panel.length > 0) {
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            console.log('PANEL CLASS CHANGED:', panel.attr('class'));
            console.log('PANEL VISIBLE:', panel.is(':visible'));
            console.log('PANEL DISPLAY:', panel.css('display'));
          }
        });
      });
      
      observer.observe(panel[0], {
        attributes: true,
        attributeFilter: ['class', 'style']
      });
      
      console.log('Panel monitor started');
    }
  }
  
  // Start monitoring
  monitorPanel();
  
  /*
  // Close panel functionality
  $(document).on('click', '#closePanelBtn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Close button clicked');
    closePanel();
  });
  
  // Overlay click handler with protection against immediate closing
  $(document).on('click', '.recipe-panel-overlay', function(e) {
    if (e.target === this && !panelJustOpened) {
      console.log('Overlay clicked - closing panel');
      closePanel();
    } else if (panelJustOpened) {
      console.log('Overlay click ignored - panel just opened');
    }
  });

  // Prevent panel from closing when clicking inside
  $(document).on('click', '.recipe-panel-container', function(e) {
    e.stopPropagation();
    console.log('Clicked inside panel - preventing close');
  });
  */

  // Branch selection
  $(document).on('click', '.branch-item', function() {
    $('.branch-item').removeClass('active');
    $(this).addClass('active');
    
    // Add visual feedback
    if (typeof gsap !== 'undefined') {
      gsap.from(this, {
        duration: 0.3,
        scale: 0.95,
        ease: 'back.out(1.7)'
      });
    }
  });

  // Category selection (for filtering - visual feedback)
  $(document).on('click', '.category-item', function() {
    $(this).toggleClass('selected');
    
    // Add bounce animation
    if (typeof gsap !== 'undefined') {
      gsap.from(this, {
        duration: 0.4,
        scale: 0.9,
        ease: 'elastic.out(1, 0.5)'
      });
    }
  });

  // Dashboard navigation
  $(document).on('click', '.dashboard-item', function() {
    const page = $(this).find('span').text().toLowerCase();
    
    // Add click animation
    if (typeof gsap !== 'undefined') {
      gsap.to(this, {
        duration: 0.1,
        scale: 0.95,
        yoyo: true,
        repeat: 1,
        ease: 'power2.inOut'
      });
    }
    
    // Navigate after animation
    setTimeout(() => {
      switch(page) {
        case 'home':
          window.location.href = 'index.php';
          break;
        case 'menu':
          window.location.href = 'menu.html';
          break;
        case 'about':
          window.location.href = 'about.html';
          break;
        case 'contact':
          window.location.href = 'contact.html';
          break;
        default:
          console.log('Navigation for', page, 'not implemented yet');
      }
    }, 200);
  });

  // Clear cart functionality
  $(document).on('click', '#clearCartBtn', function() {
    if (cart.items.length > 0) {
      // Confirmation with animation
      if (typeof gsap !== 'undefined') {
        gsap.to('.selected-items-list', {
          duration: 0.3,
          scale: 0.95,
          opacity: 0.7,
          yoyo: true,
          repeat: 1,
          onComplete: function() {
            clearCart();
          }
        });
      } else {
        clearCart();
      }
    }
  });

  // Pay button functionality
  $(document).on('click', '#payBtn', function() {
    if (!$(this).prop('disabled')) {
      // Payment animation
      if (typeof gsap !== 'undefined') {
        gsap.to(this, {
          duration: 0.2,
          scale: 0.95,
          yoyo: true,
          repeat: 1,
          onComplete: function() {
            processPayment();
          }
        });
      } else {
        processPayment();
      }
    }
  });

  // Add item to cart
  function addToCart(name, price) {
    const existingItem = cart.items.find(item => item.name === name);
    
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.items.push({
        name: name,
        price: price,
        quantity: 1,
        category: recipes[name]?.category || 'other'
      });
    }
    
    updateCartDisplay();
    updateCategoryCount();
  }

  // Remove item from cart
  function removeFromCart(name) {
    cart.items = cart.items.filter(item => item.name !== name);
    updateCartDisplay();
    updateCategoryCount();
  }

  // Clear entire cart
  function clearCart() {
    cart.items = [];
    updateCartDisplay();
    updateCategoryCount();
  }

  // Update cart display
  function updateCartDisplay() {
    const totalItems = cart.items.reduce((sum, item) => sum + item.quantity, 0);
    $('#totalItemsCount').text(totalItems);
    
    // Calculate totals
    cart.subtotal = cart.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cart.tax = Math.round(cart.subtotal * 0.1);
    cart.total = cart.subtotal + cart.tax;
    
    // Update display
    $('#subtotalAmount').text(`Rs ${cart.subtotal.toLocaleString()}`);
    $('#taxAmount').text(`Rs ${cart.tax.toLocaleString()}`);
    $('#totalAmount').text(`Rs ${cart.total.toLocaleString()}`);
    
    // Update items list
    const itemsList = $('#selectedItemsList');
    if (cart.items.length === 0) {
      itemsList.html('<p class="empty-cart-msg">No items selected yet</p>');
      $('#payBtn').prop('disabled', true);
    } else {
      let itemsHtml = '';
      cart.items.forEach(item => {
        itemsHtml += `
          <div class="cart-item" data-name="${item.name}">
            <div class="cart-item-info">
              <div class="cart-item-name">${item.name} (x${item.quantity})</div>
              <div class="cart-item-price">Rs ${(item.price * item.quantity).toLocaleString()}</div>
            </div>
            <button class="cart-item-remove" onclick="removeItemFromCart('${item.name}')">
              <i class="bi bi-x"></i>
            </button>
          </div>
        `;
      });
      itemsList.html(itemsHtml);
      $('#payBtn').prop('disabled', false);
    }
  }

  // Update category counts
  function updateCategoryCount() {
    const categoryCounts = {};
    
    cart.items.forEach(item => {
      const category = item.category;
      categoryCounts[category] = (categoryCounts[category] || 0) + item.quantity;
    });
    
    $('.category-item').each(function() {
      const category = $(this).data('category');
      const count = categoryCounts[category] || 0;
      $(this).find('.item-count').text(count);
      
      // Add visual feedback for categories with items
      if (count > 0) {
        $(this).addClass('has-items');
      } else {
        $(this).removeClass('has-items');
      }
    });
  }

  // Close panel function
  function closePanel() {
    const panel = $('#recipePanel');
    console.log('Closing panel');
    
    if (typeof gsap !== 'undefined') {
      gsap.to('.recipe-panel-container', {
        duration: 0.3,
        scale: 0.9,
        opacity: 0,
        y: -20,
        ease: 'power3.in',
        onComplete: function() {
          panel.addClass('d-none panel-hidden');
          panel.hide(); // Fallback method
          $('body').css('overflow', 'auto');
        }
      });
    } else {
      panel.addClass('d-none panel-hidden');
      panel.hide(); // Fallback method
      $('body').css('overflow', 'auto');
    }
  }

  // Reset any processing buttons on page load
  function resetProcessingButtons() {
    const payBtn = $('#payBtn');
    if (payBtn.length) {
      // Check if we're coming back from a successful order
      const orderComplete = sessionStorage.getItem('orderComplete');
      const orderProcessing = sessionStorage.getItem('orderProcessing');
      
      if (orderComplete === 'true' || orderProcessing === 'true') {
        // Clear the session storage
        sessionStorage.removeItem('orderComplete');
        sessionStorage.removeItem('orderProcessing');
        
        // Reset the form if it exists
        const form = document.getElementById('checkoutForm');
        if (form) {
          form.reset();
        }
        
        // If we're on the checkout page, we'll let the server handle the redirect
        if (window.location.pathname.includes('checkout.php')) {
          // Force a hard reload to get a fresh page from the server
          window.location.reload(true);
          return;
        }
      }
      
      // Reset button state
      const originalText = payBtn.data('original-text') || 'Place Order';
      payBtn.html(originalText);
      payBtn.prop('disabled', false);
      payBtn.removeClass('processing');
    }
    
    // Add beforeunload event to clear processing state if user navigates away
    window.addEventListener('beforeunload', function() {
      if (payBtn.length && payBtn.hasClass('processing')) {
        sessionStorage.setItem('orderProcessing', 'true');
      }
    });
  }

  // Process payment function
  function processPayment() {
    const payBtn = $('#payBtn');
    
    // Prevent multiple submissions
    if (payBtn.hasClass('processing') || payBtn.prop('disabled')) {
      console.log('Order processing already in progress');
      return false;
    }
    
    // Check if we already have a completed order in session
    if (sessionStorage.getItem('orderComplete') === 'true') {
      console.log('Order already completed, redirecting to orders page');
      window.location.href = 'orders.php';
      return false;
    }
    
    // Store original button state
    const originalText = payBtn.html();
    payBtn.data('original-text', originalText);
    
    // Update button UI
    payBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
    payBtn.prop('disabled', true);
    payBtn.addClass('processing');
    
    // Prevent form submission if this is a form button
    if (payBtn.attr('type') === 'submit') {
      payBtn.closest('form').on('submit', function(e) {
        e.preventDefault();
        return false;
      });
    }
    
    // Store processing state in session
    sessionStorage.setItem('orderProcessing', 'true');
    
    // Prepare order data
    const orderData = {
      items: cart.items.map(item => ({
        id: item.id || 0,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        special_requests: item.specialRequests || ''
      })),
      delivery_address: $('#deliveryAddress').val(),
      payment_method: $('input[name="paymentMethod"]:checked').val(),
      special_instructions: $('#specialInstructions').val()
    };
    
    // Submit order to server
    $.ajax({
      url: 'submit_order.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(orderData),
      success: function(response) {
        if (response.success) {
          // Clear processing state before redirect
          sessionStorage.removeItem('orderProcessing');
          // Redirect to order confirmation page with order ID
          window.location.href = 'order-confirmation.php?order_id=' + response.order_id;
        } else {
          throw new Error(response.message || 'Failed to place order');
        }
      },
      error: function(xhr, status, error) {
        console.error('Order submission failed:', error);
        alert('Failed to place order. Please try again.');
        resetProcessingButtons();
      }
    });
    
    return false;
  }
  
  // Initialize button states on page load
  $(document).ready(function() {
    resetProcessingButtons();
    
    // Check for back/forward navigation
    if (window.performance && performance.navigation.type === 2) {
      // Page was loaded via back/forward button
      resetProcessingButtons();
    }
    
    // Handle beforeunload to clean up state
    $(window).on('beforeunload', function() {
      if (sessionStorage.getItem('orderProcessing') === 'true') {
        sessionStorage.removeItem('orderProcessing');
      }
    });
  });

  // Global function for removing items (called from HTML)
  window.removeItemFromCart = function(itemName) {
    removeFromCart(itemName);
  };

  // KEYBOARD SHORTCUTS DISABLED FOR TESTING
  /*
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && !$('#recipePanel').hasClass('d-none')) {
      closePanel();
    }
  });
  */
  
  // Add manual test function to force panel open
  window.forceShowPanel = function() {
    console.log('=== FORCING PANEL TO SHOW ===');
    const panel = $('#recipePanel');
    panel.removeClass('d-none panel-hidden');
    panel.show();
    panel.css('display', 'flex');
    $('body').css('overflow', 'hidden');
    console.log('Panel forced open - classes:', panel.attr('class'));
    console.log('Panel display:', panel.css('display'));
    console.log('Panel visible:', panel.is(':visible'));
  };

  // Initialize category counts
  updateCategoryCount();
});
