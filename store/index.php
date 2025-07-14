<?php
// Initialize session
require_once 'includes/session-config.php';

// Database configuration - adjust these values according to your setup
$host = 'localhost';
$dbname = 'admin_portal';
$username = 'root';
$password = '';

// Get carousel images from database
$carousel_images = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM carousel_images WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $carousel_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In case of database error, use empty array (no carousel will be shown)
    $carousel_images = [];
}

// Function to get appropriate image path based on screen size
function getImagePath($image, $isMobile = false) {
    if ($isMobile && !empty($image['mobile_image_path'])) {
        return $image['mobile_image_path'];
    }
    return $image['web_image_path'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Vaso Ecommerce Template</title>
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
    <section id="billboard" class="position-relative overflow-hidden">
      <div class="swiper main-swiper">
        <div class="swiper-wrapper">
          <?php if (!empty($carousel_images)): ?>
            <?php foreach ($carousel_images as $index => $image): ?>
              <div class="swiper-slide" style="background-image: url(<?php echo htmlspecialchars(getImagePath($image)); ?>); background-size: cover; background-repeat: no-repeat; height: 100vh; background-position: center;">
                <div class="container">
                  <div class="row">
                    <div class="<?php echo ($index % 2 == 0) ? 'offset-md-1 col-md-6' : 'offset-md-6 col-md-6'; ?>">
                      <div class="banner-content">
                        <h2><?php echo htmlspecialchars($image['title']); ?></h2>
                        <p class="fs-3"><?php echo htmlspecialchars($image['description']); ?></p>
                        <a href="<?php echo htmlspecialchars($image['button_url']); ?>" class="btn"><?php echo htmlspecialchars($image['button_text']); ?></a>
                      </div>
                    </div>
                    <div class="col-md-5"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Fallback content if no carousel images are available -->
            <div class="swiper-slide" style="background-image: url(images/carousel/banner-image.jpg); background-size: cover; background-repeat: no-repeat; height: 100vh; background-position: center;">
              <div class="container">
                <div class="row">
                  <div class="offset-md-1 col-md-6">
                    <div class="banner-content">
                      <h2>Welcome to Sakha</h2>
                      <p class="fs-3">Your spiritual companion for prayer and worship</p>
                      <a href="shop.php" class="btn">Shop Now</a>
                    </div>
                  </div>
                  <div class="col-md-5"></div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="main-slider-pagination position-absolute text-center"></div>
      </div>
    </section>
    <section id="about" class="padding-xlarge">
      <div class="container">
        <div class="row">
          <div class="offset-md-2 col-md-8">
            <span class="title-accent fs-6 text-uppercase" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">About us</span>
            <h3 class="py-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1500" data-aos-once="true">At Sakha, we understand the sacred importance of prayer in every Muslim's life</h3>
            <p data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1800" data-aos-once="true">Sakha is dedicated to providing high-quality Islamic prayer essentials for the Muslim community. We specialize in beautiful sajadah (prayer mats), sacred Al-Quran collections, and blessed perfumes that enhance your spiritual journey. Our carefully curated products combine traditional craftsmanship with modern convenience, ensuring every item meets the highest standards of quality and authenticity for your daily worship needs.</p>
          </div>
        </div>
      </div>
    </section>
    <section id="products" class="product-store position-relative" style="margin-bottom:100px;">
      <div class="container display-header d-flex flex-wrap justify-content-between pb-4">
        <h3 class="mt-3">Best selling Sajadah</h3>
        <div class="btn-right d-flex flex-wrap align-items-center">
          <!-- <a href="shop.html" class="btn me-5">View all items</a> -->
        </div>
      </div>
      <div class="swiper product-swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/blossom - 1.webp" alt="Blossom Prayer Collection" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Blossom Prayer Collection</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 100,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/premium - 1.webp" alt="Premium Prayer Mat" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Premium Prayer Mat</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 125,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/bolossom - 2.webp" alt="Blossom Sajadah Set" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Blossom Sajadah Set</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 115,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/azhara - 1.webp" alt="Azhara Sacred Mat" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Azhara Sacred Mat</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 135,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
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
                  <a href="single-product.html">Matt Black</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 150,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/premium - 1.webp" alt="Premium Prayer Mat" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Premium Prayer Mat</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 125,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/blossom - 1.webp" alt="Blossom Prayer Collection" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Blossom Prayer Collection</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 100,000</span>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="product-card position-relative">
              <div class="image-holder zoom-effect">
                <img src="images/products/bolossom - 2.webp" alt="Blossom Sajadah Set" class="img-fluid zoom-in">
                <div class="cart-concern position-absolute">
                  <div class="cart-button">
                    <a href="#" class="btn">Add to Cart</a>
                  </div>
                </div>
              </div>
              <div class="card-detail text-center pt-3 pb-2">
                <h5 class="card-title fs-3 text-capitalize">
                  <a href="single-product.html">Blossom Sajadah Set</a>
                </h5>
                <span class="item-price text-primary fs-3 fw-light">RP 115,000</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Navigation buttons positioned over the swiper -->
      <button class="swiper-prev product-carousel-prev" style="position: absolute; top: 50%; left: 20px; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.9); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='translateY(-50%) scale(1.1)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.transform='translateY(-50%) scale(1)'">
        <svg width="24" height="24"><use xlink:href="#angle-left"></use></svg>
      </button>
      <button class="swiper-next product-carousel-next" style="position: absolute; top: 50%; right: 20px; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.9); border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='translateY(-50%) scale(1.1)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.transform='translateY(-50%) scale(1)'">
        <svg width="24" height="24"><use xlink:href="#angle-right"></use></svg>
      </button>
    </section>
    <section id="our-video">
      <div class="video-section jarallax d-flex align-items-center justify-content-center" style="background: url(images/products/new-arrival.webp) no-repeat; background-size: cover; background-position: center;">
        <div class="video-player text-center">
          <a type="button" data-bs-toggle="modal" data-src="https://www.youtube.com/embed/W_tIumKa8VY" data-bs-target="#myModal" class="play-btn position-relative">
            <svg class="position-absolute top-0 bottom-0 start-0 end-0 m-auto" width="41" height="41"><use xlink:href="#play"></use></svg>
            <img src="images/text-pattern.png" alt="pattern" class="text-pattern">
          </a>
        </div>
      </div>
    </section>
    <section id="faqs" class="padding-xlarge">
      <div class="container">
        <div class="row">
          <div class="offset-md-2 col-md-8">
            <h3 class="text-center mb-5" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">Popular Categories</h3>
             
          </div>
        </div>
      </div>
    </section>    
    <section id="banner" data-aos="fade" data-aos-once="true">
      <div class="banner-content-1 position-relative" style="background:url('images/products/premium\ -\ 1.webp') no-repeat left; background-size: cover; height: 100%;">
        <div class="banner-content-text position-absolute" data-aos="fade" data-aos-delay="1000" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
          <h2>Sajadah</h2>
          <a href="shop.php" class="btn">Explore Products</a>
        </div>
      </div>
      <div class="banner-content-2 position-relative" style="background:url('images/products/bolossom\ -\ 2.webp') no-repeat left; background-size: cover; height: 100%;">
        <div class="banner-content-text position-absolute" data-aos="fade" data-aos-delay="1000" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
          <h2>Perfume</h2>
          <a href="shop.php" class="btn">Explore Products</a>
        </div>
      </div>
      <div class="banner-content-3 position-relative" style="background:url('images/products/blossom\ -\ 1.webp') no-repeat left; background-size: cover; height: 100%;">
        <div class="banner-content-text position-absolute" data-aos="fade" data-aos-delay="1000" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
          <h2>Books</h2>
          <a href="shop.php" class="btn">Explore Products</a>
        </div>
      </div>
    </section>
    <!-- <footer id="footer" class="overflow-hidden padding-xlarge pb-0">
      <div class="container">
        <div class="row">
          <div class="footer-top-area pb-5">
            <div class="row d-flex flex-wrap justify-content-between">
              <div class="col-lg-3 col-sm-6 pb-3" data-aos="fade" data-aos-easing="ease-in" data-aos-duration="1000" data-aos-once="true">
                <div class="footer-menu">
                  <img src="images/sakha-logo-text.png" alt="logo" class="mb-2">
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
                      <a href="login.html">Account</a>
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
    </footer> -->
    <!-- <div id="footer-bottom">
      <div class="container">
        <div class="row d-flex flex-wrap justify-content-between">
          <div class="col-12">
            <div class="copyright">
              <p>© Copyright 2023 Vaso. Design by <a href="https://templatesjungle.com/" target="_blank"><b>TemplatesJungle</b></a></p>
            </div>
          </div>
        </div>
      </div>
    </div> -->
    
    <!-- Video Popup -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">

          <div class="modal-content">
            
              <div class="modal-body">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><svg class="bi" width="40" height="40"><use xlink:href="#close-sharp"></use></svg></button>
                  <div class="ratio ratio-16x9">
                    <iframe class="embed-responsive-item" src="" id="video"  allowscriptaccess="always" allow="autoplay"></iframe>
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