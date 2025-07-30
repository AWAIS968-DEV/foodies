<?php
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<header class="hero-section text-center text-white d-flex align-items-center justify-content-center" style="background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1500&q=80'); min-height: 80vh; background-size: cover; background-position: center; background-attachment: fixed; padding: 2rem 1rem;">
  <div class="container">
    <h1 class="display-4 display-lg-3 fw-bold mb-3 mb-lg-4" id="hero-title">Welcome to Foodies</h1>
    <p class="lead mb-4 px-2 px-md-0" id="hero-desc">Experience 5-Star Dining Like Never Before</p>
    <a href="order-panel.html" class="btn btn-warning btn-lg px-4 py-2">Order Now</a>
    <div class="mt-4">
      <span class="fs-4 fs-lg-3">★★★★★</span>
    </div>
  </div>
</header>

<!-- Featured Recipes Section -->
<section class="py-4 py-lg-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-4 mb-lg-5 display-5 fw-bold">Featured Recipes</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
      
      <!-- Animation CSS -->
      <link href="css/animations.css" rel="stylesheet">
      
      <!-- Custom CSS -->
      <style>
        /* Ensure recipe cards are always visible */
        .recipe-card {
          opacity: 1 !important;
          transform: none !important;
          visibility: visible !important;
          display: block !important;
          margin-bottom: 20px;
          background-color: #fff;
          border-radius: 8px;
          overflow: hidden;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
          transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .recipe-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .recipe-card .card-img-top {
          width: 100%;
          height: 200px;
          object-fit: cover;
        }
        
        .recipe-card .card-body {
          padding: 1.25rem;
          display: flex;
          flex-direction: column;
          height: 100%;
        }
        
        .recipe-card .card-title {
          font-size: 1.1rem;
          margin-bottom: 0.75rem;
          color: #333;
        }
        
        .recipe-card .card-text {
          flex-grow: 1;
          color: #666;
          margin-bottom: 1rem;
        }
        
        /* Fix for dark theme */
        .dark-theme .recipe-card {
          background-color: #2d2d2d !important;
          color: #f0f0f0 !important;
          border: 1px solid #444 !important;
        }
        
        .dark-theme .recipe-card .card-body {
          background-color: #2d2d2d !important;
        }
        
        .dark-theme .recipe-card .card-title {
          color: #ffffff !important;
        }
        
        .dark-theme .recipe-card .card-text {
          color: #ccc !important;
        }
        
        /* Ensure hero section is visible */
        .hero-section {
          opacity: 1 !important;
          transform: none !important;
        }
      </style>
      
      <!-- Recipe Card 1 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/stuff.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Stuffed Chicken Breast" 
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>      
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Stuffed Chicken Breast</h5>
            <p class="card-text text-muted flex-grow-1">Chicken fillets with a cheese & spinach stuffing, served with sautéed vegetables, mash potatoes and creamy sauce.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-semibold text-success">Rs 1,995</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recipe Card 2 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/gourmet-seafood-appetizer-with-fresh-tomato-sauce-generated-by-ai.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Chicken Steak"
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Chicken Steak</h5>
            <p class="card-text text-muted flex-grow-1">Two juicy grilled chicken fillets served with mash potatoes, sautéed vegetables topped with Moroccan sauce.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-semibold text-success">Rs 1,995</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recipe Card 3 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/italian-food.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Stroganoff Pasta"
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Stroganoff Marrow Pasta</h5>
            <p class="card-text text-muted flex-grow-1">A rich and sour cream gravy cooked in our house broth; served with slices of beef tenderloin and pasta.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-semibold text-success">Rs 1,895</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recipe Card 4 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/homemade-food-party.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Mutton Chops"
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Mutton Chops</h5>
            <p class="card-text text-muted flex-grow-1">Succulent mutton chops marinated and grilled to perfection. Served with roasted baby potatoes, sautéed vegetables and mint sauce.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-semibold text-success">Rs 3,995</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recipe Card 5 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/salsa.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Salsa Verde Chicken"
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Salsa Verde Chicken</h5>
            <p class="card-text text-muted flex-grow-1">Grilled chicken fillets marinated in a coriander, lime, jalapeno and herb sauce. Served on a bed of grilled vegetables.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-semibold text-success">Rs 1,995</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recipe Card 6 -->
      <div class="col">
        <div class="card recipe-card h-100 shadow-sm border-0 overflow-hidden">
          <div class="ratio ratio-16x9">
            <?php 
            $imagePath = 'images/buffalo.jpg';
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/foodies/' . $imagePath;
            $imageExists = file_exists($fullPath);
            ?>
            <img src="<?php echo $imagePath; ?>" class="card-img-top object-fit-cover" alt="Buffalo Chicken"
                 style="border: 2px solid <?php echo $imageExists ? 'green' : 'red'; ?>">
            <?php if (!$imageExists): ?>
              <div class="position-absolute top-0 start-0 bg-danger text-white p-1 small">Image not found: <?php echo $imagePath; ?></div>
            <?php endif; ?>
          </div>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-bold fs-5">Buffalo Chicken</h5>
            <p class="card-text text-muted flex-grow-1">24 hour marinated chicken in our in-house buffalo sauce. A perfect blend of spice and herbs, served with ranch dip.</p>
            <div class="d-flex align-items-center justify-content-between mt-3">
              <span class="fw-bold text-success fs-5">Rs 2,195</span>
              <span class="badge bg-warning text-dark">★★★★★</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Test Button for FAQ -->
<div style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button id="testFaqBtn" class="btn btn-warning p-3 rounded-circle shadow-lg">
        <i class="fas fa-question"></i>
    </button>
</div>

<!-- FAQ Section (initially hidden) -->
<section id="faq-section" class="py-5 bg-white position-relative" style="display: none; opacity: 0;">
    <!-- Close Button -->
    <button type="button" id="closeFaqSection" class="btn-close-faq" aria-label="Close FAQ Section">
        <span aria-hidden="true">&times;</span>
    </button>
    
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions / اکثر پوچھے جانے والے سوالات</h2>
            <div class="divider mx-auto" style="width: 80px; height: 4px; background: #ffc107; border-radius: 2px;"></div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ Item 1 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button bg-light-warning text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                How can I place an order? / میں آرڈر کیسے کر سکتا ہوں؟
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-white">
                                <p class="mb-2">You can place an order by selecting food items from the menu, adding them to your cart, and completing checkout.</p>
                                <p class="mb-0 text-muted">آپ مینو سے کھانے منتخب کریں، ٹوکری میں شامل کریں اور آرڈر مکمل کریں۔</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button bg-light-warning text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                What are your delivery hours? / آپ کے ڈیلیوری کے اوقات کیا ہیں؟
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-white">
                                <p class="mb-2">We deliver from 12:00 PM to 12:00 AM daily.</p>
                                <p class="mb-0 text-muted">ہم روزانہ دوپہر 12 سے رات 12 بجے تک ڈیلیوری کرتے ہیں۔</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button bg-light-warning text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Can I cancel my order? / کیا میں اپنا آرڈر منسوخ کر سکتا ہوں؟
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-white">
                                <p class="mb-2">Yes, you can cancel your order within 5 minutes of placing it by contacting our support.</p>
                                <p class="mb-0 text-muted">جی ہاں، آپ آرڈر دینے کے 5 منٹ کے اندر ہماری سپورٹ ٹیم سے رابطہ کر کے منسوخ کر سکتے ہیں۔</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
                        <h3 class="accordion-header" id="headingFour">
                            <button class="accordion-button bg-light-warning text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Do you offer vegetarian options? / کیا آپ کے پاس ویجیٹیرین کھانے کے اختیارات موجود ہیں؟
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-white">
                                <p class="mb-2">Yes, we have a wide variety of vegetarian options available in our menu.</p>
                                <p class="mb-0 text-muted">جی ہاں، ہمارے مینو میں ویجیٹیرین کھانوں کے بہت سے اختیارات موجود ہیں۔</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden shadow-sm">
                        <h3 class="accordion-header" id="headingFive">
                            <button class="accordion-button bg-light-warning text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                How can I pay for my order? / میں اپنے آرڈر کی ادائیگی کیسے کر سکتا ہوں؟
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-white">
                                <p class="mb-2">We accept cash on delivery, credit/debit cards, and mobile wallet payments.</p>
                                <p class="mb-0 text-muted">ہم کیش آن ڈیلیوری، کریڈٹ/ڈیبٹ کارڈز، اور موبائل والیٹ ادائیگیوں کو قبول کرتے ہیں۔</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Smooth scrolling for the page */
    html {
        scroll-behavior: smooth;
    }
    
    /* FAQ Section Styling */
    #faq-section {
        background-color: #fff9e6;
        position: relative;
        padding: 3rem 0;
        opacity: 0;
        display: none;
        transition: opacity 0.3s ease-in-out;
        z-index: 1000; /* Ensure it's above other content */
    }
    
    /* Debug styles - will help identify if FAQ section is present */
    #faq-section[style*="display: block"] {
        border: 3px solid #4CAF50 !important; /* Green border when visible */
    }
    
    /* Close Button Styling */
    #closeFaqSection {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ffc107;
        border-radius: 50%;
        opacity: 1 !important;
        z-index: 10;
        padding: 0;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    #closeFaqSection:hover {
        background-color: #ffab00;
        transform: scale(1.1);
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #fff3cc;
        color: #000;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    
    .accordion-button:not(.collapsed)::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    
    .bg-light-warning {
        background-color: #fff8e1;
    }
    
    /* RTL support for Urdu text */
    [lang="ur"] {
        direction: rtl;
        text-align: right;
    }
</style>

<script>
// FAQ Section Handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - FAQ script running');
    
    // Get elements
    const faqSection = document.getElementById('faq-section');
    const closeFaqBtn = document.getElementById('closeFaqSection');
    const faqLink = document.getElementById('footer-faq-link');
    const testFaqBtn = document.getElementById('testFaqBtn');
    
    // Debug: Log elements
    console.log('FAQ Section:', faqSection);
    console.log('Close Button:', closeFaqBtn);
    console.log('FAQ Link:', faqLink);
    console.log('Test Button:', testFaqBtn);
    
    // Show FAQ section
    function showFaq() {
        console.log('Showing FAQ section');
        if (faqSection) {
            console.log('Setting FAQ section to visible');
            faqSection.style.display = 'block';
            
            // Small delay to allow display to update
            setTimeout(() => {
                faqSection.style.opacity = '1';
                
                // Scroll to section
                faqSection.scrollIntoView({ behavior: 'smooth' });
                
                // Expand first FAQ item
                const firstFaq = document.querySelector('#collapseOne');
                if (firstFaq && !firstFaq.classList.contains('show')) {
                    console.log('Expanding first FAQ item');
                    new bootstrap.Collapse(firstFaq, { toggle: true });
                }
            }, 10);
        } else {
            console.error('FAQ section element not found!');
        }
    }
    
    // Hide FAQ section
    function hideFaq(e) {
        if (e) e.preventDefault();
        console.log('Hiding FAQ section');
        
        if (faqSection) {
            faqSection.style.opacity = '0';
            
            setTimeout(() => {
                faqSection.style.display = 'none';
                // Remove hash from URL
                if (window.location.hash === '#faq-section') {
                    history.pushState('', document.title, window.location.pathname + window.location.search);
                }
            }, 300);
        }
    }
    
    // Set up event listeners
    if (closeFaqBtn) {
        closeFaqBtn.addEventListener('click', hideFaq);
        console.log('Close button event listener added');
    } else {
        console.error('Close button not found!');
    }
    
    // Enhanced FAQ link handling
    function setupFaqLink(link) {
        if (!link) return;
        
        // Remove any existing click handlers to prevent duplicates
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);
        
        newLink.addEventListener('click', function(e) {
            console.log('FAQ link clicked');
            e.preventDefault();
            e.stopPropagation();
            
            // Show FAQ section
            showFaq();
            
            // Update URL without page reload
            if (history.pushState) {
                history.pushState(null, null, '#faq-section');
            } else {
                window.location.hash = 'faq-section';
            }
            
            return false;
        });
        
        console.log('FAQ link handler set up');
        return newLink;
    }
    
    // Set up the footer FAQ link
    const newFaqLink = setupFaqLink(faqLink);
    if (!newFaqLink) {
        console.error('Failed to set up FAQ link!');
    }
    
    // Add test button handler
    if (testFaqBtn) {
        testFaqBtn.addEventListener('click', function() {
            console.log('Test FAQ button clicked');
            showFaq();
        });
    } else {
        console.error('Test button not found!');
    }
    
    // Check URL hash on page load
    if (window.location.hash === '#faq-section') {
        console.log('Found FAQ hash in URL');
        setTimeout(showFaq, 100); // Small delay to ensure everything is loaded
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login to Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="login.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <div class="text-center pb-3">
                <p class="mb-0">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Sign up</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create an Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="register.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">I agree to the <a href="#">Terms & Conditions</a></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </div>
            </form>
            <div class="text-center pb-3">
                <p class="mb-0">Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Add this script at the bottom of your page, before the closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is not logged in
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    // Show login modal if user is not logged in
    if (!isLoggedIn) {
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'), {
            backdrop: 'static', // Prevents closing by clicking outside
            keyboard: false // Prevents closing with ESC key
        });
        loginModal.show();
    }
    
    // Handle modal switching between login and signup
    const loginModalEl = document.getElementById('loginModal');
    const signupModalEl = document.getElementById('signupModal');
    
    loginModalEl.addEventListener('hidden.bs.modal', function () {
        // If login modal is closed and signup modal is not shown, redirect to home
        if (!document.querySelector('.modal.show')) {
            window.location.href = 'index.php';
        }
    });
    
    // Handle links that switch between login and signup modals
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const targetModal = this.getAttribute('data-bs-target');
            const currentModal = this.closest('.modal');
            
            if (currentModal) {
                const modal = bootstrap.Modal.getInstance(currentModal);
                modal.hide();
            }
            
            const targetModalEl = document.querySelector(targetModal);
            if (targetModalEl) {
                const newModal = new bootstrap.Modal(targetModalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                newModal.show();
            }
            
            e.preventDefault();
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
