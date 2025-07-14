<?php
// Product Detail Page with Database Integration
require_once '../admin/includes/config.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get product ID or slug from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$selected_color = isset($_GET['color']) ? trim($_GET['color']) : '';

// Initialize variables
$product = null;
$colors = [];
$sizes = [];
$images = [];
$categories = [];
$selected_color_data = null;

try {
    // Query product by ID or slug
    if ($product_id > 0) {
        $query = "SELECT * FROM products WHERE id = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
    } elseif (!empty($product_slug)) {
        $query = "SELECT * FROM products WHERE slug = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_slug]);
    } else {
        // No product identifier provided
        header('Location: shop.php');
        exit();
    }
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If product not found, redirect to shop
    if (!$product) {
        header('Location: shop.php');
        exit();
    }
    
    // Get product colors
    $query = "SELECT * FROM product_colors WHERE product_id = ? AND status = 'active' ORDER BY sort_order, id";
    $stmt = $db->prepare($query);
    $stmt->execute([$product['id']]);
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product sizes
    $query = "SELECT * FROM product_sizes WHERE product_id = ? AND status = 'active' ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute([$product['id']]);
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product images (filtered by color if specified, or default to first color)
    if (!empty($selected_color)) {
        // First, get the color data to validate the color exists
        $color_query = "SELECT * FROM product_colors WHERE product_id = ? AND (color_name = ? OR color_code = ?) AND status = 'active' LIMIT 1";
        $color_stmt = $db->prepare($color_query);
        $color_stmt->execute([$product['id'], $selected_color, $selected_color]);
        $selected_color_data = $color_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selected_color_data) {
            // Get images for the specific color
            $query = "SELECT pi.* FROM product_images pi 
                      JOIN product_colors pc ON pi.color_id = pc.id 
                      WHERE pi.product_id = ? AND pc.id = ? AND pi.status = 'active' 
                      ORDER BY pi.is_primary DESC, pi.id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product['id'], $selected_color_data['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no images found for this color, fall back to first color's images
            if (empty($images) && !empty($colors)) {
                $query = "SELECT pi.* FROM product_images pi 
                          JOIN product_colors pc ON pi.color_id = pc.id 
                          WHERE pi.product_id = ? AND pc.id = ? AND pi.status = 'active' 
                          ORDER BY pi.is_primary DESC, pi.id";
                $stmt = $db->prepare($query);
                $stmt->execute([$product['id'], $colors[0]['id']]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            // Color not found, show first color's images if available
            if (!empty($colors)) {
                $query = "SELECT pi.* FROM product_images pi 
                          JOIN product_colors pc ON pi.color_id = pc.id 
                          WHERE pi.product_id = ? AND pc.id = ? AND pi.status = 'active' 
                          ORDER BY pi.is_primary DESC, pi.id";
                $stmt = $db->prepare($query);
                $stmt->execute([$product['id'], $colors[0]['id']]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // No colors available, show all images
                $query = "SELECT * FROM product_images WHERE product_id = ? AND status = 'active' ORDER BY is_primary DESC, id";
                $stmt = $db->prepare($query);
                $stmt->execute([$product['id']]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } else {
        // No color specified, show first color's images by default
        if (!empty($colors)) {
            $query = "SELECT pi.* FROM product_images pi 
                      JOIN product_colors pc ON pi.color_id = pc.id 
                      WHERE pi.product_id = ? AND pc.id = ? AND pi.status = 'active' 
                      ORDER BY pi.is_primary DESC, pi.id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product['id'], $colors[0]['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // No colors available, show all images
            $query = "SELECT * FROM product_images WHERE product_id = ? AND status = 'active' ORDER BY is_primary DESC, id";
            $stmt = $db->prepare($query);
            $stmt->execute([$product['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Get product categories
    $query = "SELECT c.* FROM categories c 
              JOIN product_categories pc ON c.id = pc.category_id 
              WHERE pc.product_id = ? AND c.status = 'active' 
              ORDER BY c.name";
    $stmt = $db->prepare($query);
    $stmt->execute([$product['id']]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update page title
    $page_title = htmlspecialchars($product['name']) . ' - Sakha';
    
} catch (PDOException $e) {
    // Log error and redirect to shop
    error_log("Product detail error: " . $e->getMessage());
    header('Location: shop.php');
    exit();
}

// Function to format price
function formatPrice($price) {
    return 'RP ' . number_format($price, 0, ',', '.');
}

// Function to get primary image or default
function getPrimaryImage($images) {
    if (empty($images)) {
        return 'images/products/default-product.jpg';
    }
    
    foreach ($images as $image) {
        if ($image['is_primary']) {
            return $image['image_path'];
        }
    }
    
    return $images[0]['image_path'];
}

// Function to build URL with color parameter
function buildColorUrl($color_name, $product_slug, $product_id) {
    $params = [];
    
    if (!empty($product_slug)) {
        $params['slug'] = $product_slug;
    } elseif ($product_id > 0) {
        $params['id'] = $product_id;
    }
    
    if (!empty($color_name)) {
        $params['color'] = $color_name;
    }
    
    return 'product-detail.php?' . http_build_query($params);
}
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
                    <?php if (!empty($images)): ?>
                      <?php foreach ($images as $image): ?>
                        <div class="swiper-slide">
                          <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="swiper-slide">
                        <img src="images/products/default-product.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                              <div class="swiper large-swiper overflow-hidden col-9">
                  <div class="swiper-wrapper">
                    <?php if (!empty($images)): ?>
                      <?php foreach ($images as $image): ?>
                        <div class="swiper-slide">
                          <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="swiper-slide">
                        <img src="images/products/default-product.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="product-info">
              <div class="element-header">
                <h3 class="product-title my-3"><?php echo htmlspecialchars($product['name']); ?></h3>
              </div>
              <div class="product-price my-3">
                <?php if ($product['discounted_price'] && $product['discounted_price'] < $product['price']): ?>
                  <span class="fs-1 text-primary"><?php echo formatPrice($product['discounted_price']); ?></span>
                  <del><?php echo formatPrice($product['price']); ?></del>
                <?php else: ?>
                  <span class="fs-1 text-primary"><?php echo formatPrice($product['price']); ?></span>
                <?php endif; ?>
              </div>
              <p><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></p>
              <hr>
              <div class="cart-wrap">
                <?php if (!empty($colors)): ?>
                <div class="color-options product-select my-3">
                  <div class="color-toggle" data-option-index="0">
                    <h4 class="item-title text-decoration-underline text-uppercase">Color:</h4>
                    <ul class="select-list list-unstyled d-flex mb-0">
                      <?php foreach ($colors as $index => $color): ?>
                        <?php 
                        $isSelected = (!empty($selected_color) && 
                                     (strtolower($color['color_name']) === strtolower($selected_color) || 
                                      strtolower($color['color_code']) === strtolower($selected_color)));
                        $colorUrl = buildColorUrl($color['color_name'], $product_slug, $product_id);
                        ?>
                        <li class="select-item me-3 <?php echo $isSelected ? 'active' : ''; ?>" data-val="<?php echo htmlspecialchars($color['color_name']); ?>" title="<?php echo htmlspecialchars($color['color_name']); ?>">
                          <a href="<?php echo htmlspecialchars($colorUrl); ?>" class="color-swatch d-flex align-items-center">
                            <span class="color-indicator me-2" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: <?php echo htmlspecialchars($color['color_code']); ?>; border: 2px solid <?php echo $isSelected ? '#333' : '#ddd'; ?>;"></span>
                            <?php echo htmlspecialchars($color['color_name']); ?>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($sizes)): ?>
                <div class="swatch product-select" data-option-index="1">
                  <h4 class="item-title text-decoration-underline text-uppercase">Size:</h4>
                  <ul class="select-list list-unstyled d-flex mb-0">
                    <?php foreach ($sizes as $size): ?>
                      <li data-value="<?php echo htmlspecialchars($size['size_value']); ?>" class="select-item me-3">
                        <a href="#"><?php echo htmlspecialchars($size['size_name']); ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>
                <?php if ($product['custom_name_enabled']): ?>
                <div class="custom-name-pouch product-select my-3">
                  <h4 class="item-title text-decoration-underline text-uppercase">Custom Name</h4>
                  <div class="custom-name-option">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" value="" id="enablePouchName">
                      <label class="form-check-label" for="enablePouchName">
                        Add name to pouch (+<?php echo formatPrice($product['pouch_custom_price']); ?>)
                      </label>
                    </div>
                    <div class="custom-name-input" id="pouchNameInput" style="display: none;">
                      <input type="text" class="form-control" id="pouchNameText" placeholder="Enter name for pouch (max 30 characters)" maxlength="30">
                      <small class="form-text text-muted">All characters allowed</small>
                    </div>
                  </div>
                  <div class="custom-name-option">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" value="" id="enableSajadahName">
                      <label class="form-check-label" for="enableSajadahName">
                        Add name to sajadah (+<?php echo formatPrice($product['sajadah_custom_price']); ?>)
                      </label>
                    </div>
                    <div class="custom-name-input" id="sajadahNameInput" style="display: none;">
                      <input type="text" class="form-control" id="sajadahNameText" placeholder="Enter name for sajadah (max 40 characters)" maxlength="40">
                      <small class="form-text text-muted">All characters allowed</small>
                    </div>
                  </div>
                </div>
                <?php endif; ?>
               
                
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
                <?php if (!empty($categories)): ?>
                <div class="meta-item d-flex mb-1">
                  <span class="text-uppercase me-2">Category:</span>
                  <ul class="select-list list-unstyled d-flex mb-0">
                    <?php foreach ($categories as $index => $category): ?>
                      <li data-value="<?php echo htmlspecialchars($category['slug']); ?>" class="select-item">
                        <a href="shop.php?category=<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a><?php if ($index < count($categories) - 1): ?>,<?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <?php endif; ?>
                
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
                <div class="product-description">
                  <?php if (!empty($product['description'])): ?>
                    <?php echo $product['description']; ?>
                  <?php else: ?>
                    <p>Product description not available.</p>
                  <?php endif; ?>
                </div>
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
    
    /* Color selection active state */
    .color-options .select-item.active .color-swatch {
        background-color: #f8f9fa;
        border-radius: 4px;
        padding: 4px 8px;
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
        
        // Custom name inputs - no character validation, all characters allowed
        
        // Show character count
        $('#pouchNameText').on('input', function() {
            var length = $(this).val().length;
            var maxLength = $(this).attr('maxlength');
            $(this).next('.form-text').text('All characters allowed (' + length + '/' + maxLength + ')');
        });
        
        $('#sajadahNameText').on('input', function() {
            var length = $(this).val().length;
            var maxLength = $(this).attr('maxlength');
            $(this).next('.form-text').text('All characters allowed (' + length + '/' + maxLength + ')');
        });
    });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 