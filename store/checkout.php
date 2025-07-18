<?php
// Initialize session
require_once 'includes/session-config.php';

// Include cart functions
require_once 'includes/cart-functions.php';

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

// Redirect to cart if empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

// Page title
$page_title = "Checkout - Sakha";
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
    
    <style>
      .cart-items {
        max-height: 400px;
        overflow-y: auto;
      }
      
      .cart-item {
        transition: background-color 0.2s ease;
      }
      
      .cart-item:hover {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
      }
      
      .cart-item img {
        border: 1px solid #dee2e6;
      }
      
      .cart-item .small {
        line-height: 1.4;
      }
      
      .cart-item .text-success {
        font-size: 0.8rem;
        font-weight: 500;
      }
      
      .cart-item .text-muted {
        font-size: 0.8rem;
      }
      
      .shipping-options {
        max-height: 300px;
        overflow-y: auto;
      }
      
      .shipping-option {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
      }
      
      .shipping-option:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
      }
      
      .shipping-option.selected {
        border-color: #007bff;
        background-color: #e7f3ff;
      }
      
      .courier-selection {
        font-weight: 500;
      }
      
      .courier-selection select {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
      }
      
      .rajaongkir-error {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #dc3545;
      }
      
      .rajaongkir-error .fas {
        margin-right: 5px;
      }
      
      .text-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
      }
      
      .text-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
      }
      
      .shipping-error-icon {
        color: #dc3545;
        margin-right: 5px;
      }
    </style>
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
    <section class="hero-section jarallax d-flex align-items-center justify-content-center padding-medium pb-5" style="background: url(images/hero-img.jpg) no-repeat;">
      <div class="hero-content">
        <div class="container">
          <div class="row">
            <div class="text-center padding-large no-padding-bottom">
              <h1>Checkout</h1>
              <div class="breadcrumbs">
                <span class="item">
                  <a href="index.php">Home ></a>
                </span>
                <span class="item">Checkout</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="shopify-cart checkout-wrap padding-large">
      <div class="container">
        <form class="form-group" id="checkout-form">
          <div class="row d-flex flex-wrap">
            <div class="col-lg-7">
              <h3 class="pb-4">Billing Details</h3>
              <div class="billing-details">
                <div class="py-3">
                  <label for="fname">Name*</label>
                  <input type="text" id="fname" name="firstname" class="w-100">
                </div>

                <div class="py-3">
                  <label for="phone">Phone Number*</label>
                  <input type="tel" id="phone" name="phone" class="w-100" placeholder="e.g. 08123456789">
                </div>

                <div class="py-3">
                  <label for="province">Province *</label>
                  <select id="province" name="province" class="w-100" aria-label="Select Province">
                    <option selected="" hidden="">Select Province</option>
                    <option value="Aceh">Aceh</option>
                    <option value="Bali">Bali</option>
                    <option value="Banten">Banten</option>
                    <option value="Bengkulu">Bengkulu</option>
                    <option value="DI Yogyakarta">DI Yogyakarta</option>
                    <option value="DKI Jakarta">DKI Jakarta</option>
                    <option value="Gorontalo">Gorontalo</option>
                    <option value="Jambi">Jambi</option>
                    <option value="Jawa Barat">Jawa Barat</option>
                    <option value="Jawa Tengah">Jawa Tengah</option>
                    <option value="Jawa Timur">Jawa Timur</option>
                    <option value="Kalimantan Barat">Kalimantan Barat</option>
                    <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                    <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                    <option value="Kalimantan Timur">Kalimantan Timur</option>
                    <option value="Kalimantan Utara">Kalimantan Utara</option>
                    <option value="Kepulauan Bangka Belitung">Kepulauan Bangka Belitung</option>
                    <option value="Kepulauan Riau">Kepulauan Riau</option>
                    <option value="Lampung">Lampung</option>
                    <option value="Maluku">Maluku</option>
                    <option value="Maluku Utara">Maluku Utara</option>
                    <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                    <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                    <option value="Papua">Papua</option>
                    <option value="Papua Barat">Papua Barat</option>
                    <option value="Papua Barat Daya">Papua Barat Daya</option>
                    <option value="Papua Pegunungan">Papua Pegunungan</option>
                    <option value="Papua Selatan">Papua Selatan</option>
                    <option value="Papua Tengah">Papua Tengah</option>
                    <option value="Riau">Riau</option>
                    <option value="Sulawesi Barat">Sulawesi Barat</option>
                    <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                    <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                    <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                    <option value="Sulawesi Utara">Sulawesi Utara</option>
                    <option value="Sumatera Barat">Sumatera Barat</option>
                    <option value="Sumatera Selatan">Sumatera Selatan</option>
                    <option value="Sumatera Utara">Sumatera Utara</option>
                  </select>
                </div>

                <div class="py-3">
                  <label for="map-search">Street Address</label>
                  <input type="text" id="map-search" placeholder="Your address ..." class="w-100 mb-3">
                  
                  <input type="text" id="adr2" name="address2" placeholder="Other details (e.g. yellow store, apartment, suite, landmarks, etc.)" class="w-100" style="margin-bottom: 10px;">
                  <div id="map" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 5px; background-color: #f8f9fa;"></div>
                  <small class="text-muted">Click on the map to select your address. You can also drag the marker to fine-tune your location.</small>
                </div>

                <div class="py-3">
                  <label for="city">City *</label>
                  <select id="city" name="city" class="w-100" aria-label="Select City">
                    <option selected="" hidden="">Select City</option>
                  </select>
                </div>

                <div class="py-3">
                  <label for="zip">Postal Code *</label>
                  <input type="text" id="zip" name="zip" class="w-100" placeholder="e.g. 12345">
                </div>

                <div class="py-3 courier-selection">
                  <label for="courier">Preferred Courier *</label>
                  <select id="courier" name="courier" class="w-100" aria-label="Select Courier">
                    <option selected="" hidden="">Select Courier</option>
                    <option value="jne">JNE</option>
                    <option value="jnt">JNT</option>
                  </select>
                </div>
            
              </div>
            </div>
            <div class="col-lg-5">
              <h3 class="pb-4">Additional Information</h3>
              <div class="billing-details">
                <label for="fname">Order notes (optional)</label>
                <textarea class="w-100" placeholder="Notes about your order. Like special notes for delivery."></textarea>
              </div>
              <div class="your-order mt-5">
                <h3 class="pb-4">Your Order</h3>
                
                <!-- Cart Items -->
                <div class="cart-items mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Items in Cart (<?php echo count($cartItems); ?>)</h5>
                    <a href="cart.php" class="btn btn-sm btn-outline-primary">Edit Cart</a>
                  </div>
                  <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item border-bottom pb-3 mb-3">
                      <div class="row align-items-center">
                        <div class="col-3">
                          <?php 
                          $productImage = !empty($item['primary_image']) ? $item['primary_image'] : 'images/products/default-product.jpg';
                          ?>
                          <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-fluid" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                        </div>
                        <div class="col-6">
                          <h6 class="mb-1 fs-6"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                          <div class="small text-muted">
                            <?php if ($item['color_name']): ?>
                              <span>Color: <?php echo htmlspecialchars($item['color_name']); ?></span>
                            <?php endif; ?>
                            <?php if ($item['size_name']): ?>
                              <span><?php echo $item['color_name'] ? ' | ' : ''; ?>Size: <?php echo htmlspecialchars($item['size_name']); ?></span>
                            <?php endif; ?>
                            <div>Qty: <?php echo $item['quantity']; ?></div>
                            
                            <?php if ($item['pouch_custom_enabled'] && !empty($item['pouch_custom_name'])): ?>
                              <div class="text-success">
                                <i class="fas fa-plus"></i> Pouch: "<?php echo htmlspecialchars($item['pouch_custom_name']); ?>"
                              </div>
                            <?php endif; ?>
                            
                            <?php if ($item['sajadah_custom_enabled'] && !empty($item['sajadah_custom_name'])): ?>
                              <div class="text-success">
                                <i class="fas fa-plus"></i> Sajadah: "<?php echo htmlspecialchars($item['sajadah_custom_name']); ?>"
                              </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['font_style'])): ?>
                              <div class="text-muted">
                                <i class="fas fa-font"></i> Font: <?php echo htmlspecialchars($item['font_style']); ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="col-3 text-end">
                          <span class="fw-bold text-primary"><?php echo formatPrice($item['item_total']); ?></span>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="total-price">
                  <table cellspacing="0" class="table">
                    <tbody>
                      <tr class="subtotal border-top border-bottom border-dark pt-2 pb-2 text-uppercase">
                        <th>Subtotal</th>
                        <td data-title="Subtotal">
                          <span class="price-amount amount text-primary ps-5">
                            <?php echo formatPrice($subtotal); ?>
                          </span>
                        </td>
                      </tr>
                      <?php if ($customAdditions > 0): ?>
                      <tr class="custom-additions border-bottom border-dark pt-2 pb-2 text-uppercase">
                        <th>Custom Additions</th>
                        <td data-title="Custom Additions">
                          <span class="price-amount amount text-primary ps-5">
                            <?php echo formatPrice($customAdditions); ?>
                          </span>
                        </td>
                      </tr>
                      <?php endif; ?>
                      <tr class="shipping-cost border-bottom border-dark pt-2 pb-2 text-uppercase" id="shipping-row" style="display: none;">
                        <th>Shipping Cost</th>
                        <td data-title="Shipping Cost">
                          <div id="shipping-options">
                            <div class="text-muted">Please select city and courier to calculate shipping cost</div>
                          </div>
                        </td>
                      </tr>
                      <tr class="order-total border-bottom border-dark pt-2 pb-2 text-uppercase">
                        <th>Total</th>
                        <td data-title="Total">
                          <span class="price-amount amount text-primary ps-5 fw-bold fs-5" id="final-total">
                            <?php echo formatPrice($total); ?>
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="list-group mt-5 mb-3">
                    <label class="list-group-item p-0 bg-transparent d-flex gap-2 border-0">
                      <input class="form-check-input p-0 flex-shrink-0" type="radio" name="listGroupRadios" id="listGroupRadios1" value="" checked>
                      <span>
                        <div class="fw-300 text-uppercase d-flex align-items-center gap-2">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/QRIS_logo.svg/2560px-QRIS_logo.svg.png" alt="QRIS" style="height: 24px; width: auto;">
                        </div>
                        <!-- <p class="d-block">Pay using QRIS (Quick Response Code Indonesian Standard). Simply scan the QR code with your mobile banking app or e-wallet to complete the payment instantly.</p> -->
                      </span>
                    </label>

                  </div>
                  <button type="submit" name="submit" class="btn btn-dark w-100">Place an order</button>
                </div>
              </div>
            </div>
          </div>
        </form>
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
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
                <div class="footer-menu">
                  <img src="images/main-logo.png" alt="logo" class="mb-2">
                  <p>Nunc tristique facilisis consectetur vivamus ut porta porta aliquam vitae vehicula leo nullam urna lectus.</p>
                </div>
              </div>
              <div class="col-lg-2 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1200" data-aos-once="true">
                <div class="footer-menu">
                  <h4 class="widget-title pb-2">Quick Links</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="about.html">About</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="shop.html">Shop</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="contact.html">Contact</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Account</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1400" data-aos-once="true">
                <div class="footer-menu contact-item">
                  <h4 class="widget-title pb-2">Contact info</h4>
                  <ul class="menu-list list-unstyled">
                    <li class="menu-item pb-2">
                      <a href="#">Tea Berry, Marinette, USA</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="#">+55 111 222 333 44</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="mailto:">yourinfo@gmail.com</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1600" data-aos-once="true">
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
                      <li>
                        <a href="#">
                          <svg class="linkedin">
                            <use xlink:href="#linkedin">
                          </svg>
                        </a>
                      </li>
                      <li>
                        <a href="#">
                          <svg class="youtube">
                            <use xlink:href="#youtube">
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
              <p>© Copyright 2023 Vaso. Design by <a href="https://templatesjungle.com/" target="_blank"><b>TemplatesJungle</b></a></p>
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
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAef_gZXRvK3y4TP666us0NglEXRqcXKmM&libraries=places&callback=initMap" async defer></script>
    
    <!-- Rajaongkir Integration -->
    <script>
        // Configuration
        const PROXY_URL = 'rajaongkir-proxy.php';
        
        let subtotalAmount = <?php echo $total; ?>;
        let selectedShippingCost = 0;
        let totalWeight = 1000; // Default weight in grams (1kg) - you can calculate this based on cart items
        let originCityId = '23'; // Bandung city ID - CHANGE THIS TO YOUR STORE'S CITY ID
        
        // Popular city IDs for reference:
        // Jakarta: 151, Surabaya: 444, Bandung: 23, Medan: 153, Semarang: 398
        // Yogyakarta: 501, Malang: 176, Denpasar: 114, Makassar: 175
        
        // Load cities when province is selected
        document.getElementById('province').addEventListener('change', function() {
            const selectedProvince = this.value;
            clearErrorMessages(); // Clear any previous errors
            
            if (selectedProvince) {
                loadCities(selectedProvince);
            } else {
                document.getElementById('city').innerHTML = '<option selected="" hidden="">Select City</option>';
                hideShippingOptions();
            }
        });
        
        // Calculate shipping when city is selected
        document.getElementById('city').addEventListener('change', function() {
            const selectedCity = this.value;
            const selectedCourier = document.getElementById('courier').value;
            clearErrorMessages(); // Clear any previous errors
            
            if (selectedCity && selectedCourier) {
                calculateShipping(selectedCity, selectedCourier);
            } else if (selectedCity || selectedCourier) {
                showShippingMessage();
            } else {
                hideShippingOptions();
            }
        });
        
        // Calculate shipping when courier is selected
        document.getElementById('courier').addEventListener('change', function() {
            const selectedCourier = this.value;
            const selectedCity = document.getElementById('city').value;
            clearErrorMessages(); // Clear any previous errors
            
            if (selectedCity && selectedCourier) {
                calculateShipping(selectedCity, selectedCourier);
            } else if (selectedCity || selectedCourier) {
                showShippingMessage();
            } else {
                hideShippingOptions();
            }
        });
        
        function loadCities(provinceName) {
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option selected="" hidden="">Loading cities...</option>';
            
            // First, get province ID
            fetch(`${PROXY_URL}?action=provinces`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    if (data.rajaongkir && data.rajaongkir.status.code === 200) {
                        const provinces = data.rajaongkir.results;
                        const province = provinces.find(p => p.province.toLowerCase() === provinceName.toLowerCase());
                        
                        if (province) {
                            // Load cities for this province
                            fetch(`${PROXY_URL}?action=cities&province_id=${province.province_id}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.error) {
                                        throw new Error(data.error);
                                    }
                                    
                                    if (data.rajaongkir && data.rajaongkir.status.code === 200) {
                                        const cities = data.rajaongkir.results;
                                        
                                        citySelect.innerHTML = '<option selected="" hidden="">Select City</option>';
                                        cities.forEach(city => {
                                            const option = document.createElement('option');
                                            option.value = city.city_id;
                                            option.text = `${city.type} ${city.city_name}`;
                                            citySelect.appendChild(option);
                                        });
                                    } else {
                                        const errorMsg = data.rajaongkir ? data.rajaongkir.status.description : 'Unknown API error';
                                        console.error('Failed to load cities:', errorMsg);
                                        citySelect.innerHTML = `<option selected="" hidden="">Error: ${errorMsg}</option>`;
                                        showErrorMessage(`Failed to load cities: ${errorMsg}`);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error loading cities:', error);
                                    citySelect.innerHTML = `<option selected="" hidden="">Error: ${error.message}</option>`;
                                    showErrorMessage(`Error loading cities: ${error.message}`);
                                });
                        } else {
                            console.error('Province not found:', provinceName);
                            citySelect.innerHTML = '<option selected="" hidden="">Province not found</option>';
                            showErrorMessage(`Province "${provinceName}" not found in Rajaongkir database`);
                        }
                    } else {
                        const errorMsg = data.rajaongkir ? data.rajaongkir.status.description : 'Unknown API error';
                        console.error('Failed to load provinces:', errorMsg);
                        citySelect.innerHTML = `<option selected="" hidden="">Error: ${errorMsg}</option>`;
                        showErrorMessage(`Failed to load provinces: ${errorMsg}`);
                    }
                })
                .catch(error => {
                    console.error('Error loading provinces:', error);
                    citySelect.innerHTML = `<option selected="" hidden="">Error: ${error.message}</option>`;
                    showErrorMessage(`Error loading provinces: ${error.message}`);
                });
        }
        
        function calculateShipping(destinationCityId, selectedCourier) {
            const weight = totalWeight;
            
            // Show loading state
            document.getElementById('shipping-row').style.display = 'table-row';
            document.getElementById('shipping-options').innerHTML = '<div class="text-muted">Calculating shipping costs...</div>';
            
            // Get shipping costs for selected courier only
            getShippingCost(originCityId, destinationCityId, weight, selectedCourier)
                .then(result => {
                    displayShippingOptions([result]);
                })
                .catch(error => {
                    console.error('Error calculating shipping:', error);
                    const errorMessage = error.message || 'Unknown error occurred';
                    document.getElementById('shipping-options').innerHTML = `<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error: ${errorMessage}</div>`;
                    showErrorMessage(`Shipping calculation failed: ${errorMessage}`);
                });
        }
        
        function getShippingCost(origin, destination, weight, courier) {
            const formData = new FormData();
            
            // Ensure proper data types
            formData.append('origin', parseInt(origin));
            formData.append('destination', parseInt(destination));
            formData.append('weight', parseInt(weight));
            formData.append('courier', courier.toLowerCase());
            
            // Debug logging
            console.log('Sending shipping request:', {
                origin: parseInt(origin),
                destination: parseInt(destination),
                weight: parseInt(weight),
                courier: courier.toLowerCase()
            });
            
            return fetch(`${PROXY_URL}?action=cost`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.rajaongkir && data.rajaongkir.status.code === 200) {
                    const results = data.rajaongkir.results;
                    if (!results || results.length === 0) {
                        throw new Error(`No shipping services found for ${courier.toUpperCase()}`);
                    }
                    
                    return {
                        courier: courier.toUpperCase(),
                        services: results[0]?.costs || []
                    };
                } else {
                    const errorMsg = data.rajaongkir ? data.rajaongkir.status.description : 'Unknown API error';
                    throw new Error(`${courier.toUpperCase()} shipping cost calculation failed: ${errorMsg}`);
                }
            })
            .catch(error => {
                // Re-throw with more context
                throw new Error(`Failed to get ${courier.toUpperCase()} shipping cost: ${error.message}`);
            });
        }
        
        function displayShippingOptions(shippingResults) {
            let html = '<div class="shipping-options">';
            
            const result = shippingResults[0]; // Since we're only showing one courier now
            
            if (result && result.services.length > 0) {
                html += `<div class="mb-3">
                    <h6 class="text-primary mb-3">${result.courier} Shipping Options</h6>`;
                
                result.services.forEach(service => {
                    const cost = service.cost[0];
                    html += `<div class="shipping-option mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping" id="shipping_${result.courier.toLowerCase()}_${service.service}" value="${cost.value}" data-courier="${result.courier}" data-service="${service.service}" data-etd="${cost.etd}">
                            <label class="form-check-label w-100" for="shipping_${result.courier.toLowerCase()}_${service.service}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">${service.service}</div>
                                        <div class="text-muted small">${service.description}</div>
                                        <div class="text-success small">
                                            <i class="fas fa-clock"></i> Estimasi: ${cost.etd} hari
                                        </div>
                                    </div>
                                    <div class="text-primary fw-bold fs-6">
                                        RP ${parseInt(cost.value).toLocaleString()}
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>`;
                });
                
                html += '</div>';
            } else {
                const courierName = result ? result.courier : 'selected courier';
                html = `<div class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No shipping options available for this destination with ${courierName}
                </div>`;
                
                // Show error message
                if (result) {
                    showErrorMessage(`${result.courier} does not provide shipping services to this destination`);
                }
            }
            
            html += '</div>';
            
            document.getElementById('shipping-options').innerHTML = html;
            
            // Add event listeners to shipping options
            document.querySelectorAll('input[name="shipping"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedShippingCost = parseInt(this.value);
                    updateTotal();
                    
                    // Update visual selection
                    document.querySelectorAll('.shipping-option').forEach(option => {
                        option.classList.remove('selected');
                    });
                    this.closest('.shipping-option').classList.add('selected');
                });
            });
        }
        
        function hideShippingOptions() {
            document.getElementById('shipping-row').style.display = 'none';
            selectedShippingCost = 0;
            updateTotal();
        }
        
        function showShippingMessage() {
            document.getElementById('shipping-row').style.display = 'table-row';
            document.getElementById('shipping-options').innerHTML = '<div class="text-muted">Please select city and courier to calculate shipping cost</div>';
            selectedShippingCost = 0;
            updateTotal();
        }
        
        function updateTotal() {
            const finalTotal = subtotalAmount + selectedShippingCost;
            document.getElementById('final-total').innerHTML = `RP ${finalTotal.toLocaleString()}`;
        }
        
        function formatPrice(price) {
            return `RP ${parseInt(price).toLocaleString()}`;
        }
        
        function showErrorMessage(message) {
            // Remove any existing error messages
            clearErrorMessages();
            
            // Create error alert
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show rajaongkir-error';
            errorAlert.style.position = 'fixed';
            errorAlert.style.top = '20px';
            errorAlert.style.right = '20px';
            errorAlert.style.zIndex = '9999';
            errorAlert.style.maxWidth = '400px';
            errorAlert.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Shipping Error:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.body.appendChild(errorAlert);
            
            // Auto-dismiss after 8 seconds
            setTimeout(() => {
                if (errorAlert.parentNode) {
                    errorAlert.remove();
                }
            }, 8000);
        }
        
        function clearErrorMessages() {
            const existingErrors = document.querySelectorAll('.rajaongkir-error');
            existingErrors.forEach(error => error.remove());
        }
    </script>
    
    <script>
        let map;
        let marker;
        let geocoder;
        let autocomplete;

        function initMap() {
            // Initialize map centered on Bandung, Indonesia
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: -6.9175, lng: 107.6191}
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Initialize autocomplete on search input
            autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('map-search'),
                {
                    types: ['address'],
                    componentRestrictions: {country: 'id'},
                    bounds: {
                        north: 6.0,
                        south: -11.0,
                        east: 141.0,
                        west: 95.0
                    }
                }
            );

            // Set up autocomplete listener
            autocomplete.addListener('place_changed', onPlaceChanged);

            // Set up map click listener
            map.addListener('click', function(event) {
                placeMarkerAndPanTo(event.latLng);
            });

            // Try to get user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLocation);
                }, function() {
                    // Handle location error
                    console.log('Error: The Geolocation service failed.');
                });
            }
        }

        function onPlaceChanged() {
            const place = autocomplete.getPlace();
            
            if (!place.geometry) {
                console.log("No details available for input: '" + place.name + "'");
                return;
            }

            // If the place has a geometry, center the map on it
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            // Place marker
            if (marker) {
                marker.setMap(null);
            }
            marker = new google.maps.Marker({
                position: place.geometry.location,
                map: map,
                draggable: true
            });

            // Make marker draggable and update address on drag
            marker.addListener('dragend', function() {
                updateAddressFromLatLng(marker.getPosition());
            });

            // Update form fields
            updateFormFields(place);
        }

        function placeMarkerAndPanTo(latLng) {
            // Remove existing marker
            if (marker) {
                marker.setMap(null);
            }

            // Add new marker
            marker = new google.maps.Marker({
                position: latLng,
                map: map,
                draggable: true
            });

            // Make marker draggable
            marker.addListener('dragend', function() {
                updateAddressFromLatLng(marker.getPosition());
            });

            // Update address based on clicked location
            updateAddressFromLatLng(latLng);
        }

        function updateAddressFromLatLng(latLng) {
            geocoder.geocode({location: latLng}, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        const place = results[0];
                        updateFormFields(place);
                        document.getElementById('map-search').value = place.formatted_address;
                    } else {
                        console.log('No results found');
                    }
                } else {
                    console.log('Geocoder failed due to: ' + status);
                }
            });
        }

        function updateFormFields(place) {
            // Clear existing values
            document.getElementById('zip').value = '';

            let streetNumber = '';
            let streetName = '';

            // Parse address components
            if (place.address_components) {
                place.address_components.forEach(function(component) {
                    const types = component.types;
                    
                    if (types.includes('street_number')) {
                        streetNumber = component.long_name;
                    }
                    
                    if (types.includes('route')) {
                        streetName = component.long_name;
                    }
                    
                    if (types.includes('postal_code')) {
                        document.getElementById('zip').value = component.long_name;
                    }
                    
                    if (types.includes('administrative_area_level_1')) {
                        // Update province dropdown if needed
                        const provinceSelect = document.getElementById('province');
                        const provinceName = component.long_name;
                        
                        if (provinceSelect) {
                            // Try to find matching option in dropdown
                            for (let option of provinceSelect.options) {
                                if (option.text.toLowerCase().includes(provinceName.toLowerCase()) || 
                                    option.text.toLowerCase().includes(component.short_name.toLowerCase())) {
                                    option.selected = true;
                                    // Trigger change event to load cities
                                    provinceSelect.dispatchEvent(new Event('change'));
                                    break;
                                }
                            }
                        }
                    }
                });
            }

            // Update map-search field with formatted address
            if (place.formatted_address) {
                document.getElementById('map-search').value = place.formatted_address;
            }
        }

        // Province coordinates for map centering
        const provinceCoordinates = {
            'Aceh': {lat: 4.695135, lng: 96.7493993},
            'Bali': {lat: -8.4095178, lng: 115.188916},
            'Banten': {lat: -6.4058172, lng: 106.0640179},
            'Bengkulu': {lat: -3.7928451, lng: 102.2607641},
            'DI Yogyakarta': {lat: -7.8753849, lng: 110.4262088},
            'DKI Jakarta': {lat: -6.208763, lng: 106.845599},
            'Gorontalo': {lat: 0.6999372, lng: 122.4467238},
            'Jambi': {lat: -1.4851831, lng: 102.4380581},
            'Jawa Barat': {lat: -6.9034443, lng: 107.6181927},
            'Jawa Tengah': {lat: -7.150975, lng: 110.1402594},
            'Jawa Timur': {lat: -7.5360639, lng: 112.2384017},
            'Kalimantan Barat': {lat: -0.2787808, lng: 111.4752851},
            'Kalimantan Selatan': {lat: -3.0926415, lng: 115.2837585},
            'Kalimantan Tengah': {lat: -1.6814878, lng: 113.3823545},
            'Kalimantan Timur': {lat: 1.6406296, lng: 116.419389},
            'Kalimantan Utara': {lat: 3.0730929, lng: 116.0413889},
            'Kepulauan Bangka Belitung': {lat: -2.7410513, lng: 106.4405872},
            'Kepulauan Riau': {lat: 3.9456514, lng: 108.1428669},
            'Lampung': {lat: -4.5585849, lng: 105.4068079},
            'Maluku': {lat: -3.2384616, lng: 130.1452734},
            'Maluku Utara': {lat: 1.5709993, lng: 127.8087693},
            'Nusa Tenggara Barat': {lat: -8.6529334, lng: 117.3616476},
            'Nusa Tenggara Timur': {lat: -8.6573819, lng: 121.0793705},
            'Papua': {lat: -4.269928, lng: 138.0803529},
            'Papua Barat': {lat: -1.3361154, lng: 133.1747162},
            'Papua Barat Daya': {lat: -7.6145924, lng: 133.6926084},
            'Papua Pegunungan': {lat: -4.0648911, lng: 138.3207261},
            'Papua Selatan': {lat: -6.2288274, lng: 139.9419031},
            'Papua Tengah': {lat: -3.3890292, lng: 136.3563742},
            'Riau': {lat: 0.2933469, lng: 101.7068294},
            'Sulawesi Barat': {lat: -2.8441371, lng: 119.2320784},
            'Sulawesi Selatan': {lat: -3.6687994, lng: 119.9740534},
            'Sulawesi Tengah': {lat: -1.4300254, lng: 121.4456179},
            'Sulawesi Tenggara': {lat: -4.14491, lng: 122.174605},
            'Sulawesi Utara': {lat: 0.6246932, lng: 123.9750018},
            'Sumatera Barat': {lat: -0.7399397, lng: 100.8000051},
            'Sumatera Selatan': {lat: -3.3194374, lng: 103.914399},
            'Sumatera Utara': {lat: 2.1153547, lng: 99.5450974}
        };

        // Handle province selection
        document.getElementById('province').addEventListener('change', function() {
            const selectedProvince = this.value;
            if (selectedProvince && provinceCoordinates[selectedProvince]) {
                const coordinates = provinceCoordinates[selectedProvince];
                
                // Center map on selected province
                map.setCenter(coordinates);
                map.setZoom(8); // Appropriate zoom level for province view
                
                // Clear existing address search
                document.getElementById('map-search').value = '';
                
                // Remove existing marker
                if (marker) {
                    marker.setMap(null);
                }
                
                // Update autocomplete bounds to focus on selected province
                const bounds = new google.maps.LatLngBounds();
                const center = new google.maps.LatLng(coordinates.lat, coordinates.lng);
                bounds.extend(center);
                
                // Expand bounds for better autocomplete results
                const offset = 0.5; // degrees
                bounds.extend(new google.maps.LatLng(coordinates.lat + offset, coordinates.lng + offset));
                bounds.extend(new google.maps.LatLng(coordinates.lat - offset, coordinates.lng - offset));
                
                if (autocomplete) {
                    autocomplete.setBounds(bounds);
                }
            }
        });

        // Prevent form submission on Enter key press
        document.getElementById('checkout-form').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        // Also prevent Enter key on individual input fields
        document.querySelectorAll('#checkout-form input, #checkout-form select, #checkout-form textarea').forEach(function(element) {
            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html>