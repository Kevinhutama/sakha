<?php
// Shop page with database integration
require_once '../admin/includes/config.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize variables
$products = [];
$totalProducts = 0;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$productsPerPage = 5;
$totalPages = 0;
$startResult = 0;
$endResult = 0;

try {
    // Get total count of active products
    $countQuery = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute();
    $totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calculate pagination values
    $totalPages = ceil($totalProducts / $productsPerPage);
    $offset = ($currentPage - 1) * $productsPerPage;
    $startResult = $offset + 1;
    $endResult = min($offset + $productsPerPage, $totalProducts);
    
    // Query to get products with thumbnails with pagination
    $query = "SELECT p.*, pt.primary_image, pt.secondary_image 
              FROM products p 
              LEFT JOIN product_thumbnails pt ON p.id = pt.product_id 
              WHERE p.status = 'active' 
              ORDER BY p.created_at DESC
              LIMIT $productsPerPage OFFSET $offset";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Shop page error: " . $e->getMessage());
    $products = [];
    $totalProducts = 0;
    $totalPages = 0;
}

// Function to format price
function formatPrice($price) {
    return 'RP ' . number_format($price, 0, ',', '.');
}

// Function to get product image or default
function getProductImage($imagePath) {
    if (empty($imagePath)) {
        return 'images/products/default-product.jpg';
    }
    return $imagePath;
}

// Function to build pagination URLs
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Sakha - Shop</title>
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
    
    <!-- Product Image Hover Effect -->
    <style>
      .product-card .image-holder {
        position: relative;
        overflow: hidden;
      }
      
      .product-card .primary-image {
        display: block;
        width: 100%;
        transition: opacity 0.6s ease-in-out;
      }
      
      .product-card .secondary-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 0.6s ease-in-out;
      }
      
      .product-card .image-holder::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.08);
        opacity: 0;
        transition: opacity 0.6s ease-in-out;
        pointer-events: none;
        z-index: 3;
      }
      
      .product-card .image-holder:hover .primary-image {
        opacity: 0;
      }
      
      .product-card .image-holder:hover .secondary-image {
        opacity: 0.8;
        transition: opacity 0s;
      }
      
      .product-card .image-holder:hover::after {
        opacity: 1;
        transition: opacity 0s;
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
              <h1>Shop</h1>
              <div class="breadcrumbs">
                <span class="item">
                  <a href="index.php">Home ></a>
                </span>
                <span class="item">Shop</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="shopify-grid padding-large">
      <div class="container">
        <div class="row">
          <main class="col-md-9">
            <div class="filter-shop d-flex flex-wrap justify-content-between">
              <div class="showing-product">
                <?php if ($totalProducts > 0): ?>
                  <p>Showing <?php echo $startResult; ?>-<?php echo $endResult; ?> of <?php echo $totalProducts; ?> results</p>
                <?php else: ?>
                  <p>No products found</p>
                <?php endif; ?>
              </div>
              <div class="sort-by">
                <select id="input-sort" class="form-control" data-filter-sort="" data-filter-order="">
                  <option value="">Default sorting</option>
                  <option value="">Name (A - Z)</option>
                  <option value="">Name (Z - A)</option>
                  <option value="">Price (Low-High)</option>
                  <option value="">Price (High-Low)</option>
                  <option value="">Rating (Highest)</option>
                  <option value="">Rating (Lowest)</option>
                  <option value="">Model (A - Z)</option>
                  <option value="">Model (Z - A)</option>   
                </select>
              </div>
            </div>
            <div class="row product-content product-store">
              <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                  <div class="col-lg-4 col-md-6">
                    <?php if (!empty($product['secondary_image'])): ?>
                      <!-- Product with both primary and secondary images -->
                      <div class="product-card mb-3 position-relative">
                        <div class="image-holder">
                          <img src="<?php echo htmlspecialchars(getProductImage($product['primary_image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid primary-image">
                          <img src="<?php echo htmlspecialchars(getProductImage($product['secondary_image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> Secondary" class="img-fluid secondary-image">
                          <div class="cart-concern position-absolute">
                            <div class="cart-button">
                              <a href="#" class="btn">Add to Cart</a>
                            </div>
                          </div>
                        </div>
                        <div class="card-detail text-center pt-3 pb-2">
                          <h5 class="card-title fs-3 text-capitalize">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                          </h5>
                          <span class="item-price text-primary fs-3 fw-light">
                            <?php if ($product['discounted_price'] && $product['discounted_price'] < $product['price']): ?>
                              <?php echo formatPrice($product['discounted_price']); ?>
                              <del class="text-muted ms-2"><?php echo formatPrice($product['price']); ?></del>
                            <?php else: ?>
                              <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                          </span>
                        </div>
                      </div>
                    <?php else: ?>
                      <!-- Product with only primary image -->
                      <div class="product-card position-relative mb-3">
                        <div class="image-holder zoom-effect">
                          <img src="<?php echo htmlspecialchars(getProductImage($product['primary_image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid zoom-in">
                          <div class="cart-concern position-absolute">
                            <div class="cart-button">
                              <a href="#" class="btn">Add to Cart</a>
                            </div>
                          </div>
                        </div>
                        <div class="card-detail text-center pt-3 pb-2">
                          <h5 class="card-title fs-3 text-capitalize">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                          </h5>
                          <span class="item-price text-primary fs-3 fw-light">
                            <?php if ($product['discounted_price'] && $product['discounted_price'] < $product['price']): ?>
                              <?php echo formatPrice($product['discounted_price']); ?>
                              <del class="text-muted ms-2"><?php echo formatPrice($product['price']); ?></del>
                            <?php else: ?>
                              <?php echo formatPrice($product['price']); ?>
                            <?php endif; ?>
                          </span>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-12 text-center">
                  <p>No products found.</p>
                </div>
              <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
            <nav class="navigation paging-navigation text-center padding-medium" role="navigation">
              <div class="pagination loop-pagination d-flex justify-content-center align-items-center">
                <?php if ($currentPage > 1): ?>
                  <a href="<?php echo buildPaginationUrl($currentPage - 1); ?>" class="d-flex pe-2">
                    <svg width="24" height="24"><use xlink:href="#angle-left"></use></svg>
                  </a>
                <?php endif; ?>
                
                <?php
                // Calculate page range to show
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                
                // Show first page if not in range
                if ($startPage > 1) {
                    echo '<a class="page-numbers pe-3" href="' . buildPaginationUrl(1) . '">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-numbers pe-3">...</span>';
                    }
                }
                
                // Show page numbers in range
                for ($i = $startPage; $i <= $endPage; $i++) {
                    if ($i == $currentPage) {
                        echo '<span aria-current="page" class="page-numbers current pe-3">' . $i . '</span>';
                    } else {
                        echo '<a class="page-numbers pe-3" href="' . buildPaginationUrl($i) . '">' . $i . '</a>';
                    }
                }
                
                // Show last page if not in range
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="page-numbers pe-3">...</span>';
                    }
                    echo '<a class="page-numbers pe-3" href="' . buildPaginationUrl($totalPages) . '">' . $totalPages . '</a>';
                }
                ?>
                
                <?php if ($currentPage < $totalPages): ?>
                  <a href="<?php echo buildPaginationUrl($currentPage + 1); ?>" class="d-flex ps-2">
                    <svg width="24" height="24"><use xlink:href="#angle-right"></use></svg>
                  </a>
                <?php endif; ?>
              </div>
            </nav>
            <?php endif; ?>
          </main>
          <aside class="col-md-3">
            <div class="sidebar">
              <div class="widget-menu">
                <div class="widget-search-bar">
                  <form role="search" method="get" class="position-relative d-flex justify-content-between align-items-center border-bottom border-dark py-1">
                    <input class="search-field" placeholder="Search" type="search">
                    <div class="search-icon position-absolute end-0">
                      <a href="#">
                        <svg width="26" height="26" class="search">
                          <use xlink:href="#search"></use>
                        </svg>
                      </a>
                    </div>
                  </form>
                </div> 
              </div>
              <div class="widget-product-categories pt-5">
                <h5 class="widget-title text-decoration-underline text-uppercase">Categories</h5>
                <ul class="product-categories sidebar-list list-unstyled">
                  <li class="cat-item">
                    <a href="/collections/categories">All</a>
                  </li>
                  <li class="cat-item">
                    <a href="">Sajadah</a>
                  </li>
                  <li class="cat-item">
                    <a href="">Al-Quran</a>
                  </li>
                  <li class="cat-item">
                    <a href="">Perfume</a>
                  </li>
                  <li class="cat-item">
                    <a href="">Prayer Beads</a>
                  </li>
                </ul>
              </div>
              
              <div class="widget-price-filter pt-3">
                <h5 class="widget-title text-decoration-underline text-uppercase">Filter By Price</h5>
                <ul class="product-tags sidebar-list list-unstyled">
                  <li class="tags-item">
                    <a href="">Less than RP 100,000</a>
                  </li>
                  <li class="tags-item">
                    <a href="">RP 100,000 - RP 200,000</a>
                  </li>
                  <li class="tags-item">
                    <a href="">RP 200,000 - RP 300,000</a>
                  </li>
                  <li class="tags-item">
                    <a href="">RP 300,000 - RP 400,000</a>
                  </li>
                  <li class="tags-item">
                    <a href="">RP 400,000 - RP 500,000</a>
                  </li>
                </ul>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>
   
    
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