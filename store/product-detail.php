<?php
// No special database functionality needed for product detail page
// This is a conversion from product-detail.html to product-detail.php with proper includes
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
    
    <section class="hero-section jarallax d-flex align-items-center justify-content-center padding-medium pb-5" style="background: url(images/hero-img.jpg) no-repeat;">
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
    
    <section class="single-product padding-large">
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
                        <a href="#">Pink</a>
                      </li>
                      <li class="select-item me-3" data-val="Blue" title="Blue">
                        <a href="#">Blue</a>
                      </li>
                      <li class="select-item me-3" data-val="Green" title="Green">
                        <a href="#">Green</a>
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
                <div class="product-quantity my-3">
                  <div class="item-title">
                    <l>5 in stock</l>
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

    <script src="js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/plugins.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 