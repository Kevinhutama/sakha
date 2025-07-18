<?php
// Initialize session
require_once 'includes/session-config.php';

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
    <section class="single-product padding-large" style= "margin-top: <?php echo (isset($_SESSION['cart_success']) || isset($_SESSION['cart_error'])) ? '20px' : '100px'; ?>">
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
                        <li class="select-item me-3 <?php echo $isSelected ? 'active' : ''; ?>" data-val="<?php echo htmlspecialchars($color['color_name']); ?>" data-color-id="<?php echo $color['id']; ?>" title="<?php echo htmlspecialchars($color['color_name']); ?>">
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
                      <li data-value="<?php echo htmlspecialchars($size['size_value']); ?>" data-size-id="<?php echo $size['id']; ?>" class="select-item me-3">
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
                  
                  <!-- Font Style Selection -->
                  <div class="font-style-option mt-3" id="fontStyleSection" style="display: none;">
                    <h5 class="mb-3" style="font-family: 'Poppins', sans-serif;">Font Style</h5>
                    <div class="font-style-wrapper">
                      <select class="form-control" id="fontStyleSelect">
                        <option value="font-01">Font - 01</option>
                        <option value="font-02">Font - 02</option>
                        <option value="font-03">Font - 03</option>
                        <option value="font-04">Font - 04</option>
                        <option value="font-05">Font - 05</option>
                        <option value="font-06">Font - 06</option>
                        <option value="font-07">Font - 07</option>
                        <option value="font-08">Font - 08</option>
                        <option value="font-09">Font - 09</option>
                        <option value="font-10">Font - 10</option>
                      </select>
                      <small class="form-text text-muted mt-2">Choose a font style for your custom name. </br> <a href="#" id="showFontStyles" class="font-reference-link">View Font Styles</a> </small>
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
                <form id="addToCartForm" method="POST" action="cart-handler.php">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="hidden" name="color_id" id="selected_color_id" value="<?php echo $colors[0]['id'] ?? ''; ?>">
                  <input type="hidden" name="size_id" id="selected_size_id" value="<?php echo $sizes[0]['id'] ?? ''; ?>">
                  <input type="hidden" name="pouch_custom_enabled" id="pouch_custom_enabled_input" value="0">
                  <input type="hidden" name="pouch_custom_name" id="pouch_custom_name_input" value="">
                  <input type="hidden" name="sajadah_custom_enabled" id="sajadah_custom_enabled_input" value="0">
                  <input type="hidden" name="sajadah_custom_name" id="sajadah_custom_name_input" value="">
                  <input type="hidden" name="font_style" id="font_style_input" value="">
                  
                  <div class="action-buttons my-4 d-flex flex-wrap">
                    <a href="#" class="btn btn-dark me-2 mb-1">Buy now</a>
                    <button type="submit" class="btn btn-dark">Add to cart</button>
                  </div>
                </form>
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

    <!-- Font Styles Modal -->
    <div id="fontStylesModal" class="font-styles-modal" style="display: none;">
      <div class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close-modal" id="closeFontModal">&times;</button>
          </div>
          <div class="modal-body">
            <div class="font-styles-carousel">
              <?php
              // Get font style images from directory
              $fontStylesDir = 'images/font-styles/';
              if (is_dir($fontStylesDir)) {
                  $images = glob($fontStylesDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                  if ($images) {
                      echo '<div class="font-style-viewer">';
                      echo '<div class="font-style-navigation">';
                      echo '<button id="prevFontBtn" class="nav-btn nav-btn-left" disabled>‹</button>';
                      echo '<div class="font-style-counter"><span id="currentFontIndex">1</span> / <span id="totalFontStyles">' . count($images) . '</span></div>';
                      echo '<button id="nextFontBtn" class="nav-btn nav-btn-right">›</button>';
                      echo '</div>';
                      echo '<div class="font-style-image-container">';
                      foreach ($images as $index => $image) {
                          $isActive = $index === 0 ? 'active' : '';
                          echo '<img src="' . htmlspecialchars($image) . '" alt="Font Style ' . ($index + 1) . '" class="font-style-image ' . $isActive . '" data-index="' . $index . '">';
                      }
                      echo '</div>';
                      echo '</div>';
                  } else {
                      echo '<p class="text-center text-muted">No font style images found.</p>';
                  }
              } else {
                  echo '<p class="text-center text-muted">Font styles directory not found.</p>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

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
    
    /* Font Style Section */
    .font-style-option {
        border-top: 1px solid #eee;
        padding-top: 20px;
        margin-top: 20px;
    }
    
    .font-style-option h5 {
        color: #333;
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 15px;
    }
    
    .font-style-wrapper {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    #fontStyleSelect {
        border: 2px solid #ddd;
        border-radius: 6px;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: 500;
        background-color: white;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    #fontStyleSelect:focus {
        border-color: #333;
        box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        outline: none;
    }
    
    #fontStyleSelect:hover {
        border-color: #333;
        background-color: #fff;
    }
    
    #fontStyleSelect option {
        padding: 10px;
        font-weight: 500;
    }
    
    /* Font Reference Link */
    .font-reference-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        padding: 8px 16px;
        border-radius: 20px;
        text-decoration: none !important;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        border: 2px solid transparent;
        cursor: pointer;
    }
    
    .font-reference-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white !important;
        text-decoration: none !important;
    }
    
    .font-reference-link:active {
        transform: translateY(0);
    }
    
    /* Font Styles Modal */
    .font-styles-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-overlay {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        position: relative;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px 0;
        border-bottom: 0px;
        background-color: #fff;
    }
    
    .modal-header h4 {
        margin: 0;
        color: #333;
        font-weight: 600;
        font-size: 20px;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        padding: 5px;
        line-height: 1;
        transition: color 0.3s ease;
    }
    
    .close-modal:hover {
        color: #333;
    }
    
    .modal-body {
        padding: 25px;
        overflow-y: auto;
        max-height: calc(80vh - 100px);
    }
    
    .font-style-viewer {
        text-align: center;
    }
    
    .font-style-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 0 20px;
    }
    
    .nav-btn {
        background: #333;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .nav-btn:hover:not(:disabled) {
        background: #555;
        transform: scale(1.1);
    }
    
    .nav-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .font-style-counter {
        background: #f8f9fa;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        color: #333;
        border: 2px solid #dee2e6;
    }
    
    .font-style-image-container {
        position: relative;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        overflow: hidden;
    }
    
    .font-style-image {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        display: none;
        transition: opacity 0.3s ease;
    }
    
    .font-style-image.active {
        display: block;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .modal-content {
            margin: 10px;
            max-height: 90vh;
        }
        
        .font-style-navigation {
            padding: 0 10px;
        }
        
        .nav-btn {
            width: 35px;
            height: 35px;
            font-size: 16px;
        }
        
        .font-style-counter {
            font-size: 14px;
            padding: 6px 12px;
        }
        
        .font-style-image-container {
            min-height: 250px;
        }
        
        .font-style-image {
            max-height: 300px;
        }
        
        .modal-header {
            padding: 15px 20px;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .font-reference-link {
            font-size: 12px;
            padding: 6px 12px;
        }
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
            
            // Get selected size value and ID
            var selectedSize = $(this).data('value');
            var selectedSizeId = $(this).data('size-id');
            
            // Update hidden form field
            $('#selected_size_id').val(selectedSizeId);
            
            console.log('Selected size:', selectedSize, 'ID:', selectedSizeId);
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
            toggleFontStyleSection();
        });
        
        $('#enableSajadahName').on('change', function() {
            if ($(this).is(':checked')) {
                $('#sajadahNameInput').slideDown(300);
                $('#sajadahNameText').focus();
            } else {
                $('#sajadahNameInput').slideUp(300);
                $('#sajadahNameText').val('');
            }
            toggleFontStyleSection();
        });
        
        // Function to show/hide font style section
        function toggleFontStyleSection() {
            var pouchEnabled = $('#enablePouchName').is(':checked');
            var sajadahEnabled = $('#enableSajadahName').is(':checked');
            
            if (pouchEnabled || sajadahEnabled) {
                $('#fontStyleSection').slideDown(300);
            } else {
                $('#fontStyleSection').slideUp(300);
            }
        }
        
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
        
        // Font Styles Modal functionality
        let currentFontIndex = 0;
        let totalFontStyles = parseInt($('#totalFontStyles').text()) || 0;
        
        $('#showFontStyles').on('click', function(e) {
            e.preventDefault();
            currentFontIndex = 0;
            updateFontNavigation();
            $('#fontStylesModal').fadeIn(300);
            $('body').css('overflow', 'hidden'); // Prevent background scrolling
        });
        
        // Font navigation functionality
        $('#prevFontBtn').on('click', function() {
            if (currentFontIndex > 0) {
                currentFontIndex--;
                updateFontNavigation();
            }
        });
        
        $('#nextFontBtn').on('click', function() {
            if (currentFontIndex < totalFontStyles - 1) {
                currentFontIndex++;
                updateFontNavigation();
            }
        });
        
        function updateFontNavigation() {
            // Update counter
            $('#currentFontIndex').text(currentFontIndex + 1);
            
            // Update button states
            $('#prevFontBtn').prop('disabled', currentFontIndex === 0);
            $('#nextFontBtn').prop('disabled', currentFontIndex === totalFontStyles - 1);
            
            // Update active image
            $('.font-style-image').removeClass('active');
            $('.font-style-image[data-index="' + currentFontIndex + '"]').addClass('active');
        }
        
        // Close modal functionality
        $('#closeFontModal').on('click', function() {
            $('#fontStylesModal').fadeOut(300);
            $('body').css('overflow', 'auto'); // Restore scrolling
        });
        
        // Close modal when clicking overlay
        $('.modal-overlay').on('click', function(e) {
            if (e.target === this) {
                $('#fontStylesModal').fadeOut(300);
                $('body').css('overflow', 'auto');
            }
        });
        
        // Close modal with ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#fontStylesModal').is(':visible')) {
                $('#fontStylesModal').fadeOut(300);
                $('body').css('overflow', 'auto');
            }
        });
        
        // Keyboard navigation for font styles
        $(document).on('keydown', function(e) {
            if ($('#fontStylesModal').is(':visible') && e.key === 'ArrowLeft' && currentFontIndex > 0) {
                currentFontIndex--;
                updateFontNavigation();
            } else if ($('#fontStylesModal').is(':visible') && e.key === 'ArrowRight' && currentFontIndex < totalFontStyles - 1) {
                currentFontIndex++;
                updateFontNavigation();
            }
        });
        
        // Handle form submission
        $('#addToCartForm').on('submit', function(e) {
            // Update hidden fields with current selections
            
            // Get active color ID
            var activeColorId = $('.color-options .select-item.active').data('color-id') || '';
            $('#selected_color_id').val(activeColorId);
            
            // Get active size ID
            var activeSizeId = $('.swatch .select-item.active').data('size-id') || '';
            $('#selected_size_id').val(activeSizeId);
            
            // Custom name options
            var pouchEnabled = $('#enablePouchName').is(':checked');
            var sajadahEnabled = $('#enableSajadahName').is(':checked');
            
            $('#pouch_custom_enabled_input').val(pouchEnabled ? '1' : '0');
            $('#pouch_custom_name_input').val(pouchEnabled ? $('#pouchNameText').val() : '');
            
            $('#sajadah_custom_enabled_input').val(sajadahEnabled ? '1' : '0');
            $('#sajadah_custom_name_input').val(sajadahEnabled ? $('#sajadahNameText').val() : '');
            
            // Font style
            var fontStyle = '';
            if (pouchEnabled || sajadahEnabled) {
                fontStyle = $('#fontStyleSelect').val();
            }
            $('#font_style_input').val(fontStyle);
            
            // Log form data for debugging
            console.log('Form submission data:', {
                product_id: $('input[name="product_id"]').val(),
                color_id: $('#selected_color_id').val(),
                size_id: $('#selected_size_id').val(),
                quantity: $('#quantity').val(),
                pouch_custom_enabled: $('#pouch_custom_enabled_input').val(),
                pouch_custom_name: $('#pouch_custom_name_input').val(),
                sajadah_custom_enabled: $('#sajadah_custom_enabled_input').val(),
                sajadah_custom_name: $('#sajadah_custom_name_input').val(),
                font_style: $('#font_style_input').val()
            });
            
            // Allow form to submit normally
            return true;
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 