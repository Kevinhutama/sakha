<?php
// No special database functionality needed for product detail page
// This is a conversion from product-detail.html to product-detail.php with proper includes
// Color selection and image loading will be handled by backend
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Sakha - Product Detail</title>
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
    
    <!-- <section class="hero-section jarallax d-flex align-items-center justify-content-center padding-medium pb-5" style="background: url(images/hero-img.jpg) no-repeat;">
      <div class="hero-content">
        <div class="container">
          <div class="row">
            <div class="text-center padding-large no-padding-bottom">
              <h1>Product Detail</h1>
              <div class="breadcrumbs">
                <span class="item">
                  <a href="index.php">Home ></a>
                </span>
                <span class="item">
                  <a href="shop.php">Shop ></a>
                </span>
                <span class="item">Product Detail</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
     -->
    <section class="single-product padding-large" style= "margin-top: 100px;">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="row product-preview">
                              <div class="swiper thumb-swiper col-3" style="position:relative; overflow-y: scroll;">
                  <div class="swiper-wrapper d-flex flex-wrap align-content-start" style="width: 100%; position:absolute;">
                    <div class="swiper-slide">
                      <img src="images/products/blossom - 1.webp" alt="" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/bolossom - 2.webp" alt="" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/premium - 1.webp" alt="" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/blossom - 1.webp" alt="" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/bolossom - 2.webp" alt="" class="img-fluid">
                    </div>
                  </div>
                </div>
                              <div class="swiper large-swiper overflow-hidden col-9">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <img src="images/products/blossom - 1.webp" alt="single-product" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/bolossom - 2.webp" alt="single-product" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/premium - 1.webp" alt="single-product" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/blossom - 1.webp" alt="single-product" class="img-fluid">
                    </div>
                    <div class="swiper-slide">
                      <img src="images/products/bolossom - 2.webp" alt="single-product" class="img-fluid">
                    </div>
                  </div>
                </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="product-info">
              <div class="element-header">
                <h3 class="product-title my-3">Blossom Prayer Collection</h3>
              </div>
              <div class="product-price my-3">
                <span class="fs-1 text-primary">RP 100,000</span>
                <del>RP 150,000</del>
              </div>
              <p>Beautiful handcrafted prayer mat made with premium materials. Perfect for daily prayers with elegant floral patterns that bring peace and tranquility to your spiritual moments.</p>
              <hr>
              <div class="cart-wrap">
                <div class="color-options product-select my-3">
                  <div class="color-toggle" data-option-index="0">
                    <h4 class="item-title text-decoration-underline text-uppercase">Color:</h4>
                    <ul class="select-list list-unstyled d-flex mb-0">
                      <li class="select-item me-3" data-val="Pink" title="Pink">
                        <a href="#" class="color-swatch d-flex align-items-center">
                          <span class="color-indicator me-2" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: #FF69B4; border: 2px solid #ddd;"></span>
                          Pink
                        </a>
                      </li>
                      <li class="select-item me-3" data-val="Blue" title="Blue">
                        <a href="#" class="color-swatch d-flex align-items-center">
                          <span class="color-indicator me-2" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: #4169E1; border: 2px solid #ddd;"></span>
                          Blue
                        </a>
                      </li>
                      <li class="select-item me-3" data-val="Green" title="Green">
                        <a href="#" class="color-swatch d-flex align-items-center">
                          <span class="color-indicator me-2" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: #228B22; border: 2px solid #ddd;"></span>
                          Green
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="swatch product-select" data-option-index="1">
                  <h4 class="item-title text-decoration-underline text-uppercase">Size:</h4>
                  <ul class="select-list list-unstyled d-flex mb-0">
                    <li data-value="S" class="select-item me-3">
                      <a href="#">Small</a>
                    </li>
                    <li data-value="M" class="select-item me-3">
                      <a href="#">Medium</a>
                    </li>
                    <li data-value="L" class="select-item me-3">
                      <a href="#">Large</a>
                    </li>
                  </ul>
                </div>
                <div class="custom-name-pouch product-select my-3">
                  <h4 class="item-title text-decoration-underline text-uppercase">Custom Name</h4>
                  <div class="custom-name-option">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" value="" id="enablePouchName">
                      <label class="form-check-label" for="enablePouchName">
                        Add name to pouch (+RP 5,000)
                      </label>
                    </div>
                    <div class="custom-name-input" id="pouchNameInput" style="display: none;">
                      <input type="text" class="form-control" id="pouchNameText" placeholder="Enter name for pouch (max 15 characters)" maxlength="15">
                      <small class="form-text text-muted">Letters, numbers, and spaces only</small>
                    </div>
                  </div>
                  <div class="custom-name-option">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" value="" id="enableSajadahName">
                      <label class="form-check-label" for="enableSajadahName">
                        Add name to sajadah (+RP 5,000)
                      </label>
                    </div>
                    <div class="custom-name-input" id="sajadahNameInput" style="display: none;">
                      <input type="text" class="form-control" id="sajadahNameText" placeholder="Enter name for sajadah (max 20 characters)" maxlength="20">
                      <small class="form-text text-muted">Letters, numbers, and spaces only</small>
                    </div>
                  </div>
                </div>
               
                
                <div class="product-quantity my-3">
                  <div class="item-title">
                    <l>Quantity</l>
                  </div>
                  <div class="stock-button-wrap d-flex flex-wrap align-items-center">
                    <div class="product-quantity">
                      <div class="input-group product-qty" style="max-width: 150px;">
                        <span class="input-group-btn">
                          <button type="button" class="quantity-left-minus" data-type="minus" data-field="">
                            <svg width="16" height="16"><use xlink:href="#minus"></use></svg>
                          </button>
                        </span>
                        <input type="text" id="quantity" name="quantity" class="form-control input-number text-center" value="1" min="1" max="100" required>
                        <span class="input-group-btn">
                          <button type="button" class="quantity-right-plus" data-type="plus" data-field="">
                            <svg width="16" height="16"><use xlink:href="#plus"></use></svg>
                          </button>
                        </span>
                      </div>                          
                    </div>
                  </div>
                </div>
                <div class="action-buttons my-4 d-flex flex-wrap">
                  <a href="#" class="btn btn-dark me-2 mb-1">Buy now</a>
                  <a href="#" class="btn btn-dark">Add to cart</a>
                </div>
              </div>
              <hr>
              <div class="meta-product">
                <div class="meta-item d-flex mb-1">
                  <span class="text-uppercase me-2">SKU:</span>
                  <ul class="select-list list-unstyled d-flex mb-0">
                    <li data-value="S" class="select-item">BPC001</li>
                  </ul>
                </div>
                <div class="meta-item d-flex mb-1">
                  <span class="text-uppercase me-2">Category:</span>
                  <ul class="select-list list-unstyled d-flex mb-0">
                    <li data-value="S" class="select-item">
                      <a href="#">Sajadah</a>,
                    </li>
                    <li data-value="S" class="select-item">
                      <a href="#">Prayer Mat</a>,
                    </li>
                    <li data-value="S" class="select-item">
                      <a href="#">Islamic</a>
                    </li>
                  </ul>
                </div>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>    
    
    <section class="product-tabs">
      <div class="container">
        <div class="row">
          <div class="tabs-listing">
            <nav>
              <div class="nav nav-tabs d-flex py-3" id="nav-tab" role="tablist">
                <button class="nav-link text-uppercase active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Description</button>                
              </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
              <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                <p>Product Description</p>
                <p>The Blossom Prayer Collection features beautifully handcrafted prayer mats made with premium materials. Each mat is carefully designed with elegant floral patterns that bring peace and tranquility to your spiritual moments. The soft texture and durable construction ensure comfort and longevity for daily use.</p>
                <ul class="fw-light">
                  <li>Premium quality materials for comfort and durability</li>
                  <li>Beautiful floral patterns for spiritual ambiance</li>
                  <li>Soft texture suitable for daily prayers</li>
                  <li>Easy to clean and maintain</li>
                </ul>
                <p>Perfect for personal use or as a thoughtful gift for loved ones. The Blossom Prayer Collection represents the finest in Islamic prayer accessories, combining traditional craftsmanship with modern design sensibilities.</p>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section id="products" class="product-store padding-xlarge" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
      <div class="container">
        <div class="display-header d-flex flex-wrap justify-content-between align-items-center pb-4">
          <h3 class="mt-3">Related Products</h3>
          <a href="shop.php" class="btn">View all items</a>
        </div>
        <div class="row">
          <div class="col-md-3 product-card position-relative mb-3">
            <div class="image-holder zoom-effect">
              <img src="images/products/premium - 1.webp" alt="product-item" class="img-fluid zoom-in">
              <div class="cart-concern position-absolute">
                <div class="cart-button">
                  <a href="#" class="btn">Add to Cart</a>
                </div>
              </div>
            </div>
            <div class="card-detail text-center pt-3 pb-2">
              <h5 class="card-title fs-3 text-capitalize">
                <a href="product-detail.php">Premium Prayer Mat</a>
              </h5>
              <span class="item-price text-primary fs-3 fw-light">RP 125,000</span>
            </div>
          </div>
          <div class="col-md-3 product-card position-relative mb-3">
            <div class="image-holder zoom-effect">
              <img src="images/products/azhara - 1.webp" alt="product-item" class="img-fluid zoom-in">
              <div class="cart-concern position-absolute">
                <div class="cart-button">
                  <a href="#" class="btn">Add to Cart</a>
                </div>
              </div>
            </div>
            <div class="card-detail text-center pt-3 pb-2">
              <h5 class="card-title fs-3 text-capitalize">
                <a href="product-detail.php">Azhara Sacred Mat</a>
              </h5>
              <span class="item-price text-primary fs-3 fw-light">RP 135,000</span>
            </div>
          </div>
          <div class="col-md-3 product-card position-relative mb-3">
            <div class="image-holder zoom-effect">
              <img src="images/products/premium - 2.webp" alt="product-item" class="img-fluid zoom-in">
              <div class="cart-concern position-absolute">
                <div class="cart-button">
                  <a href="#" class="btn">Add to Cart</a>
                </div>
              </div>
            </div>
            <div class="card-detail text-center pt-3 pb-2">
              <h5 class="card-title fs-3 text-capitalize">
                <a href="product-detail.php">Premium Black Mat</a>
              </h5>
              <span class="item-price text-primary fs-3 fw-light">RP 150,000</span>
            </div>
          </div>
          <div class="col-md-3 product-card position-relative mb-3">
            <div class="image-holder zoom-effect">
              <img src="images/products/bolossom - 2.webp" alt="product-item" class="img-fluid zoom-in">
              <div class="cart-concern position-absolute">
                <div class="cart-button">
                  <a href="#" class="btn">Add to Cart</a>
                </div>
              </div>
            </div>
            <div class="card-detail text-center pt-3 pb-2">
              <h5 class="card-title fs-3 text-capitalize">
                <a href="product-detail.php">Blossom Sajadah Set</a>
              </h5>
              <span class="item-price text-primary fs-3 fw-light">RP 115,000</span>
            </div>
          </div>
        </div>
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
                      <a href="about.php">About</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="shop.php">Shop</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="contact.php">Contact</a>
                    </li>
                    <li class="menu-item pb-2">
                      <a href="login.php">Account</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1400" data-aos-once="true">
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
              <p>© Copyright 2023 Sakha. All rights reserved.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php include 'includes/login-modal.php'; ?>

    <style>
    .color-swatch {
        text-decoration: none !important;
        color: inherit;
        transition: all 0.3s ease;
    }
    
    .color-swatch:hover {
        color: inherit;
        text-decoration: none;
    }
    
    .color-indicator {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .color-swatch:hover .color-indicator {
        transform: scale(1.1);
        border-color: #333 !important;
    }
    
    .select-item.active .color-indicator {
        border-color: #333 !important;
        border-width: 2px !important;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.3);
        transform: scale(1.2);
        position: relative;
    }
    
    .select-item.active .color-indicator::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 10px;
        font-weight: bold;
        text-shadow: 0 0 2px rgba(0,0,0,0.8);
    }
    
    .select-item.active .color-swatch {
        font-weight: bold;
        color: #333;
    }
    
    /* Size selection styling */
    .swatch .select-item a {
        text-decoration: none;
        color: inherit;
        padding: 8px 16px;
        border: 2px solid #ddd;
        border-radius: 4px;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .swatch .select-item a:hover {
        color: inherit;
        text-decoration: none;
        border-color: #333;
        background-color: #f8f9fa;
    }
    
    .swatch .select-item.active a {
        border-color: #333;
        background-color: #333;
        color: white;
        font-weight: bold;
    }
    
    /* Custom name sections styling */
    .custom-name-pouch, .custom-name-sajadah {
        border-top: 1px solid #eee;
        padding-top: 20px;
        margin-top: 20px;
    }
    
    .custom-name-input {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        margin-top: 10px;
    }
    
    .custom-name-input input[type="text"] {
        border: 2px solid #ddd;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }
    
    .custom-name-input input[type="text"]:focus {
        border-color: #333;
        box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        outline: none;
    }
    
    .form-check-label {
        cursor: pointer;
        font-weight: 500;
    }
    
    .form-check-input:checked {
        background-color: #333;
        border-color: #333;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        border-color: #333;
    }
    
    .custom-name-option .form-text {
        color: #6c757d;
        font-size: 12px;
        margin-top: 5px;
    }
    </style>

    <script src="js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
    <script>
    $(document).ready(function() {
        // Handle color selection
        $('.color-options .select-item').on('click', function(e) {
            e.preventDefault();
            
            // Get selected color value
            var selectedColor = $(this).data('val');
            console.log('Selected color:', selectedColor);
            
            // Create URL with color parameter, preserving other parameters
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('color', selectedColor.toLowerCase());
            
            var currentUrl = window.location.pathname;
            var newUrl = currentUrl + '?' + urlParams.toString();
            
            // Reload page with color parameter
            window.location.href = newUrl;
        });
        
        // Set active color based on URL parameter or default to first
        function setActiveColor() {
            var urlParams = new URLSearchParams(window.location.search);
            var selectedColor = urlParams.get('color');
            
            if (selectedColor) {
                // Find and activate the color from URL parameter
                var colorFound = false;
                $('.color-options .select-item').each(function() {
                    var colorValue = $(this).data('val').toLowerCase();
                    if (colorValue === selectedColor.toLowerCase()) {
                        $(this).addClass('active');
                        colorFound = true;
                        return false; // break the loop
                    }
                });
                
                // If color from URL not found, default to first color
                if (!colorFound) {
                    $('.color-options .select-item:first').addClass('active');
                }
            } else {
                // No color parameter, default to first color
                $('.color-options .select-item:first').addClass('active');
            }
        }
        
        setActiveColor();
        
        // Handle size selection
        $('.swatch .select-item').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all size options
            $('.swatch .select-item').removeClass('active');
            
            // Add active class to clicked item
            $(this).addClass('active');
            
            // Get selected size value
            var selectedSize = $(this).data('value');
            console.log('Selected size:', selectedSize);
        });
        
        // Set first size as default active
        $('.swatch .select-item:first').addClass('active');
        
        // Handle custom name checkboxes
        $('#enablePouchName').on('change', function() {
            if ($(this).is(':checked')) {
                $('#pouchNameInput').slideDown(300);
                $('#pouchNameText').focus();
            } else {
                $('#pouchNameInput').slideUp(300);
                $('#pouchNameText').val('');
            }
        });
        
        $('#enableSajadahName').on('change', function() {
            if ($(this).is(':checked')) {
                $('#sajadahNameInput').slideDown(300);
                $('#sajadahNameText').focus();
            } else {
                $('#sajadahNameInput').slideUp(300);
                $('#sajadahNameText').val('');
            }
        });
        
        // Validate custom name inputs (letters, numbers, spaces only)
        $('#pouchNameText, #sajadahNameText').on('input', function() {
            var value = $(this).val();
            var validValue = value.replace(/[^a-zA-Z0-9 ]/g, '');
            if (value !== validValue) {
                $(this).val(validValue);
            }
        });
        
        // Show character count
        $('#pouchNameText').on('input', function() {
            var length = $(this).val().length;
            var maxLength = $(this).attr('maxlength');
            $(this).next('.form-text').text('Letters, numbers, and spaces only (' + length + '/' + maxLength + ')');
        });
        
        $('#sajadahNameText').on('input', function() {
            var length = $(this).val().length;
            var maxLength = $(this).attr('maxlength');
            $(this).next('.form-text').text('Letters, numbers, and spaces only (' + length + '/' + maxLength + ')');
        });
    });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 