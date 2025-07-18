<?php
// Initialize session
require_once 'includes/session-config.php';

// Database configuration
require_once '../admin/includes/config.php';
require_once 'includes/cart-functions.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
$session_id = session_id();

// Get cart data
$cartItems = getCartItems($user_id, $session_id);
$cartSummary = getCartSummary($user_id, $session_id);
$cartCount = getCartCount($user_id, $session_id);

// Calculate totals
$subtotal = 0;
$customAdditions = 0;
$total = 0;

if ($cartSummary) {
    $subtotal = $cartSummary['subtotal'] ?? 0;
    $customAdditions = $cartSummary['custom_additions'] ?? 0;
    $total = $cartSummary['total_amount'] ?? 0;
}

// Page title
$page_title = "Shopping Cart - Sakha";
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $page_title; ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" type="text/css" href="css/vendor.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Mulish:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;0,1000;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900;1,1000&display=swap" rel="stylesheet">
    <!-- script
    ================================================== -->
    <script src="js/modernizr.js"></script>
  </head>
  <body>
    <?php include 'includes/svg-icons.php'; ?>
    
    <div id="preloader">
      <div id="loader"></div>
    </div>
    
    <?php include 'includes/navigation.php'; ?>
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['cart_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 70px; margin-bottom: 0; border-radius: 0;">
        <div class="container">
          <i class="fas fa-check-circle me-2"></i>
          <?php echo htmlspecialchars($_SESSION['cart_success']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['cart_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 70px; margin-bottom: 0; border-radius: 0;">
        <div class="container">
          <i class="fas fa-exclamation-circle me-2"></i>
          <?php echo htmlspecialchars($_SESSION['cart_error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>
    
    <section class="hero-section jarallax d-flex align-items-center justify-content-center padding-medium pb-5" style="background: url(images/hero-img.jpg) no-repeat; margin-top: <?php echo (isset($_SESSION['cart_success']) || isset($_SESSION['cart_error'])) ? '0' : '70px'; ?>;">
      <div class="hero-content">
        <div class="container">
          <div class="row">
            <div class="text-center padding-large no-padding-bottom">
              <h1>Cart</h1>
              <div class="breadcrumbs">
                <span class="item">
                  <a href="index.php">Home ></a>
                </span>
                <span class="item">Cart</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section class="shopify-cart padding-large">
      <div class="container">
        <?php if (empty($cartItems)): ?>
          <!-- Empty Cart State -->
          <div class="row">
            <div class="col-12 text-center">
              <div class="empty-cart-message padding-large">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc; margin-bottom: 2rem;"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <!-- Cart Items -->
          <div class="row">
            <div class="table-responsive">
              <table class="table">
                <thead class="text-uppercase">
                  <tr>
                    <th scope="col" style="width: 50%;">Product</th>
                    <th scope="col" style="width: 15%;">Quantity</th>
                    <th scope="col" style="width: 20%;">Subtotal</th>
                    <th scope="col" style="width: 15%;"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartItems as $item): ?>
                    <tr class="border-bottom border-dark" data-cart-id="<?php echo $item['id']; ?>">
                      <td class="align-middle border-0" scope="row" style="width: 50%;">
                        <div class="cart-product-detail d-flex align-items-center">
                          <div class="card-image">
                            <?php 
                            $productImage = !empty($item['primary_image']) ? $item['primary_image'] : 'images/products/default-product.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-fluid" style="width: 160px; height: 160px; object-fit: cover;">
                          </div>
                          <div class="card-detail ps-3">
                            <h5 class="card-title fs-3 text-capitalize">
                              <a href="product-detail.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                            </h5>
                            <div class="product-variants mb-1">
                              <?php if ($item['color_name']): ?>
                                <small class="text-muted">Color: <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo htmlspecialchars($item['color_code']); ?>; border: 1px solid #ddd; vertical-align: middle;"></span> <?php echo htmlspecialchars($item['color_name']); ?></small>
                              <?php endif; ?>
                              <?php if ($item['size_name']): ?>
                                <small class="text-muted d-block">Size: <?php echo htmlspecialchars($item['size_name']); ?></small>
                              <?php endif; ?>
                            </div>
                            <?php if ($item['pouch_custom_enabled'] || $item['sajadah_custom_enabled']): ?>
                              <div class="custom-options mb-1">
                                <?php if ($item['pouch_custom_enabled'] && $item['pouch_custom_name']): ?>
                                  <small class="text-success d-block"><i class="fas fa-tag me-1"></i>Pouch: "<?php echo htmlspecialchars($item['pouch_custom_name']); ?>" (+<?php echo formatPrice($item['pouch_custom_price']); ?>)</small>
                                <?php endif; ?>
                                <?php if ($item['sajadah_custom_enabled'] && $item['sajadah_custom_name']): ?>
                                  <small class="text-success d-block"><i class="fas fa-tag me-1"></i>Sajadah: "<?php echo htmlspecialchars($item['sajadah_custom_name']); ?>" (+<?php echo formatPrice($item['sajadah_custom_price']); ?>)</small>
                                <?php endif; ?>
                                <?php if ($item['font_style']): ?>
                                  <small class="text-muted d-block">Font: <?php echo htmlspecialchars($item['font_style']); ?></small>
                                <?php endif; ?>
                              </div>
                            <?php endif; ?>
                            <?php if ($item['discounted_price'] && $item['discounted_price'] < $item['base_price']): ?>
                              <span class="item-price text-primary fs-3 fw-light"><?php echo formatPrice($item['discounted_price']); ?></span>
                              <small class="text-muted"><del><?php echo formatPrice($item['base_price']); ?></del></small>
                            <?php else: ?>
                              <span class="item-price text-primary fs-3 fw-light"><?php echo formatPrice($item['base_price']); ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle border-0"  style="width: 15%;>
                        <form class="update-quantity-form" method="POST" action="cart-update.php">
                          <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                          <input type="hidden" name="action" value="update_quantity">
                          <div class="input-group product-qty" style="max-width: 150px;">
                            <span class="input-group-btn">
                              <button type="button" class="quantity-left-minus" data-type="minus">
                                <svg width="16" height="16"><use xlink:href="#minus"></use></svg>
                              </button>
                            </span>
                            <input type="number" name="quantity" class="form-control input-number text-center" value="<?php echo $item['quantity']; ?>" min="1" max="100" data-original="<?php echo $item['quantity']; ?>">
                            <span class="input-group-btn">
                              <button type="button" class="quantity-right-plus" data-type="plus">
                                <svg width="16" height="16"><use xlink:href="#plus"></use></svg>
                              </button>
                            </span>
                          </div>
                        </form>
                      </td>
                      <td class="align-middle border-0" style="width: 20%;">
                        <span class="item-total text-primary fs-3 fw-medium"><?php echo formatPrice($item['item_total']); ?></span>
                      </td>
                      <td class="align-middle border-0 cart-remove" style="width: 15%;">
                        <form method="POST" action="cart-update.php" style="display: inline;">
                          <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                          <input type="hidden" name="action" value="remove_item">
                          <button type="submit" class="btn btn-link p-0 text-danger" onclick="return confirm('Are you sure you want to remove this item?')">
                            <svg width="32px" height="32px">
                              <use xlink:href="#baseline-clear"></use>
                            </svg>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="cart-bottom d-flex flex-wrap justify-content-between align-items-center pt-2">
                <form action="cart-update.php" method="POST" class="d-flex flex-wrap justify-content-between">
                  <input type="hidden" name="action" value="apply_coupon">
                  <input type="text" name="coupon_code" class="border border-dark p-2 me-1 mb-2" placeholder="Coupon Code">
                  <button type="submit" class="btn btn-dark mb-2">Apply Coupon</button>
                </form>
                <a href="shop.php" class="btn btn-dark mb-2">Continue Shopping</a>
              </div>
            </div>
            
            <!-- Cart Totals -->
            <div class="cart-totals padding-medium">
              <h3 class="pb-4">Cart Total</h3>
              <div class="total-price pb-5">
                <table cellspacing="0" class="table text-uppercase">
                  <tbody>
                    <tr class="subtotal pt-2 pb-2 border-top border-bottom border-dark">
                      <th>Subtotal</th>
                      <td class="align-middle border-0" data-title="Subtotal">
                        <span class="price-amount amount text-primary">
                          <?php echo formatPrice($subtotal); ?>
                        </span>
                      </td>
                    </tr>
                    <?php if ($customAdditions > 0): ?>
                      <tr class="custom-additions pt-2 pb-2 border-bottom border-dark">
                        <th>Custom Additions</th>
                        <td class="align-middle border-0" data-title="Custom Additions">
                          <span class="price-amount amount text-primary">
                            <?php echo formatPrice($customAdditions); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endif; ?>
                    <tr class="order-total pt-2 pb-2 border-bottom border-dark">
                      <th>Total</th>
                      <td class="align-middle border-0" data-title="Total">
                        <span class="price-amount amount text-primary fs-2 fw-bold">
                          <?php echo formatPrice($total); ?>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="button-wrap">
                <a href="shop.php" class="btn btn-dark me-2 mb-2">Continue Shopping</a>
                <button class="btn btn-primary me-2 mb-2" onclick="proceedToCheckout()">Proceed to checkout</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
    
    <section id="newsletter" class="bg-light padding-medium" style="background-image: url(images/hero-img.jpg);">
      <div class="container">
        <div class="newsletter">
          <div class="row">
            <div class="col-lg-6 col-md-12 title mb-4">
              <h2>Subscribe to Our Newsletter</h2>
              <p>Get latest news, updates and deals directly mailed to your inbox</p> 				
            </div>
            <form class="col-lg-6 col-md-12 d-flex align-items-center">
              <div class="d-flex w-75 border-bottom border-dark py-2">
                <input id="newsletter1" type="text" class="form-control border-0 p-0" placeholder="Your email address here">
                <button class="btn border-0 p-0" type="button">Subscribe</button>
              </div>
            </form>
          </div> 			
        </div>
      </div>
    </section>
    
    <footer id="footer" class="overflow-hidden padding-xlarge pb-0">
      <div class="container">
        <div class="row">
          <div class="footer-top-area pb-5">
            <div class="row d-flex flex-wrap justify-content-between">
              <div class="col-lg-3 col-sm-6 pb-3">
                <div class="footer-menu">
                  <img src="images/sakha-logo-text.png" alt="logo" class="mb-2">
                  <p>Premium Islamic products crafted with care and devotion for your spiritual journey.</p>
                </div>
              </div>
              <div class="col-lg-2 col-sm-6 pb-3">
                <div class="footer-menu">
                  <h4 class="widget-title pb-2">Quick Links</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="index.php">Home</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="shop.php">Shop</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="cart.php">Cart</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3">
                <div class="footer-menu contact-item">
                  <h4 class="widget-title pb-2">Contact info</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="#">Jakarta, Indonesia</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="#">+62 21 1234 5678</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="mailto:">info@sakha.com</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3">
                <div class="footer-menu">
                  <h4 class="widget-title pb-2">Social info</h4>
                  <p>You can follow us on our social platforms to get updates.</p>
                  <div class="social-links">
                    <ul class="d-flex list-unstyled">
                      <li>
                        <a href="#">
                          <svg class="facebook">
                            <use xlink:href="#facebook">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="instagram">
                            <use xlink:href="#instagram">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="twitter">
                            <use xlink:href="#twitter">
                          </svg>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr>
      </div>
    </footer>
    
    <div id="footer-bottom">
      <div class="container">
        <div class="row d-flex flex-wrap justify-content-between">
          <div class="col-12">
            <div class="copyright">
              <p>© Copyright 2023 Sakha. All rights reserved.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php include 'includes/login-modal.php'; ?>

    <script src="js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
    <script>
    $(document).ready(function() {
        // Handle quantity change
        $('.quantity-right-plus, .quantity-left-minus').on('click', function(e) {
            e.preventDefault();
            
            var $input = $(this).closest('.product-qty').find('input[name="quantity"]');
            var currentVal = parseInt($input.val());
            var type = $(this).data('type');
            
            if (type === 'plus') {
                $input.val(currentVal + 1);
            } else if (type === 'minus' && currentVal > 1) {
                $input.val(currentVal - 1);
            }
            
            // Auto-submit the form after quantity change
            var originalVal = parseInt($input.data('original'));
            var newVal = parseInt($input.val());
            
            if (originalVal !== newVal) {
                // Debounce the submission
                clearTimeout($input.data('submitTimer'));
                $input.data('submitTimer', setTimeout(function() {
                    $input.closest('.update-quantity-form').submit();
                }, 1000));
            }
        });
        
        // Handle manual quantity input
        $('input[name="quantity"]').on('change', function() {
            var originalVal = parseInt($(this).data('original'));
            var newVal = parseInt($(this).val());
            
            if (originalVal !== newVal && newVal >= 1) {
                $(this).closest('.update-quantity-form').submit();
            }
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    
    function proceedToCheckout() {
        <?php if (!$isLoggedIn): ?>
            alert('Please log in to proceed to checkout.');
            $('#loginModal').modal('show');
        <?php else: ?>
            window.location.href = 'checkout.php';
        <?php endif; ?>
    }
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 