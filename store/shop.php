<?php
// Shop page with database integration
require_once '../admin/includes/config.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize variables
$products = [];
$categories = [];
$totalProducts = 0;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$productsPerPage = 5;
$totalPages = 0;
$startResult = 0;
$endResult = 0;

// Get filter parameters
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? max(0, floatval($_GET['min_price'])) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

try {
    // Get all active categories
    $categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
    $categoryStmt = $db->prepare($categoryQuery);
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get min and max prices for the price slider
    $priceQuery = "SELECT MIN(COALESCE(discounted_price, price)) as min_price, 
                          MAX(COALESCE(discounted_price, price)) as max_price 
                   FROM products WHERE status = 'active'";
    $priceStmt = $db->prepare($priceQuery);
    $priceStmt->execute();
    $priceRange = $priceStmt->fetch(PDO::FETCH_ASSOC);
    $globalMinPrice = $priceRange['min_price'] ?? 0;
    $globalMaxPrice = $priceRange['max_price'] ?? 1000000;
    
    // Set default max price if not provided
    if ($maxPrice == 0) {
        $maxPrice = $globalMaxPrice;
    }
    
    // Build WHERE clause for filters
    $whereConditions = ["p.status = 'active'"];
    $params = [];
    
    // Category filter
    if (!empty($selectedCategory) && $selectedCategory !== 'all') {
        $whereConditions[] = "EXISTS (SELECT 1 FROM product_categories pc 
                                     JOIN categories c ON pc.category_id = c.id 
                                     WHERE pc.product_id = p.id AND c.slug = ?)";
        $params[] = $selectedCategory;
    }
    
    // Search filter
    if (!empty($searchQuery)) {
        $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchParam = '%' . $searchQuery . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Price filter
    if ($minPrice > 0 || $maxPrice < $globalMaxPrice) {
        $whereConditions[] = "COALESCE(p.discounted_price, p.price) BETWEEN ? AND ?";
        $params[] = $minPrice;
        $params[] = $maxPrice;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count of filtered products
    $countQuery = "SELECT COUNT(*) as total FROM products p WHERE $whereClause";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calculate pagination values
    $totalPages = ceil($totalProducts / $productsPerPage);
    $offset = ($currentPage - 1) * $productsPerPage;
    $startResult = $offset + 1;
    $endResult = min($offset + $productsPerPage, $totalProducts);
    
    // Query to get products with thumbnails with pagination and filters
    $orderBy = buildSortOrder($sortBy);
    $query = "SELECT p.*, pt.primary_image, pt.secondary_image 
              FROM products p 
              LEFT JOIN product_thumbnails pt ON p.id = pt.product_id 
              WHERE $whereClause 
              ORDER BY $orderBy
              LIMIT $productsPerPage OFFSET $offset";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Shop page error: " . $e->getMessage());
    $products = [];
    $categories = [];
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
    // Clean up empty parameters
    $params = array_filter($params, function($value) {
        return $value !== '' && $value !== null;
    });
    return '?' . http_build_query($params);
}

// Function to build sort order clause
function buildSortOrder($sortBy) {
    switch ($sortBy) {
        case 'name_asc':
            return 'p.name ASC';
        case 'name_desc':
            return 'p.name DESC';
        case 'price_asc':
            return 'COALESCE(p.discounted_price, p.price) ASC';
        case 'price_desc':
            return 'COALESCE(p.discounted_price, p.price) DESC';
        default:
            return 'p.created_at DESC';
    }
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
      
      /* Price Range Slider Styles */
      .price-range-slider {
        padding: 10px 0;
      }
      
      .price-input-group {
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      
      .price-label {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
      }
      
      .price-input {
        width: 80px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
        text-align: center;
      }
      
      .slider-container {
        position: relative;
        margin: 20px 0;
      }
      
      .price-slider {
        height: 5px;
        position: relative;
        background: #ddd;
        border-radius: 5px;
      }
      
      .price-slider .progress {
        height: 5px;
        left: 0%;
        right: 0%;
        position: absolute;
        border-radius: 5px;
        background: #333;
      }
      
      .range-input {
        position: relative;
      }
      
      .range-input input {
        position: absolute;
        width: 100%;
        height: 5px;
        top: -5px;
        background: none;
        pointer-events: none;
        -webkit-appearance: none;
        -moz-appearance: none;
      }
      
      .range-input input::-webkit-slider-thumb {
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #333;
        pointer-events: auto;
        -webkit-appearance: none;
        box-shadow: 0 0 6px rgba(0,0,0,0.2);
        cursor: pointer;
      }
      
      .range-input input::-moz-range-thumb {
        height: 20px;
        width: 20px;
        border-radius: 50%;
        background: #333;
        pointer-events: auto;
        -moz-appearance: none;
        box-shadow: 0 0 6px rgba(0,0,0,0.2);
        cursor: pointer;
        border: none;
      }
      
      .price-display {
        font-weight: bold;
        color: #333;
        font-size: 14px;
      }
      
      /* Active category styling */
      .product-categories .cat-item a.active {
        color: #333;
        font-weight: bold;
        text-decoration: underline;
      }
      
      .product-categories .cat-item a:hover {
        color: #333;
        text-decoration: underline;
      }
      
      /* Active filters styling */
      .active-filters {
        padding: 10px 0;
        border-top: 1px solid #eee;
      }
      
      .active-filters .badge {
        font-size: 11px;
        padding: 4px 8px;
      }
      
      .clear-filters {
        font-size: 11px;
        padding: 2px 8px;
      }
      
      /* Enhanced Sort dropdown styling */
      .sort-by {
        min-width: 220px;
      }
      
      .sort-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
      }
      
      .sort-wrapper:hover {
        border-color: #333;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-1px);
      }
      
      .sort-wrapper:focus-within {
        border-color: #333;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
      }
      
      .sort-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        z-index: 2;
        pointer-events: none;
        transition: color 0.3s ease;
      }
      
      .sort-wrapper:hover .sort-icon {
        color: #333;
      }
      
      .sort-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: transparent;
        border: none;
        padding: 10px 25px 7px 45px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        width: 100%;
        outline: none;
        position: relative;
        z-index: 1;
      }
      
      .sort-select:focus {
        outline: none;
        box-shadow: none;
      }
      
      .dropdown-arrow {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        pointer-events: none;
        transition: all 0.3s ease;
      }
      
      .sort-wrapper {
        margin-bottom: 10px!important;
      }
      
      .sort-wrapper:hover .dropdown-arrow {
        color: #333;
        transform: translateY(-50%) scale(1.1);
      }
      
      .sort-wrapper:focus-within .dropdown-arrow {
        transform: translateY(-50%) rotate(180deg);
      }
      
      /* Custom option styling for better browsers */
      .sort-select option {
        padding: 12px 16px;
        font-size: 14px;
        background-color: white;
        color: #333;
        border: none;
      }
      
      .sort-select option:checked {
        background-color: #f8f9fa;
        font-weight: 600;
      }
      
      /* Responsive adjustments */
      @media (max-width: 768px) {
        .sort-by {
          min-width: 180px;
        }
        
        .sort-select {
          padding: 12px 40px 12px 40px;
          font-size: 13px;
        }
        
        .sort-icon {
          left: 12px;
        }
        
        .dropdown-arrow {
          right: 12px;
        }
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
                
                <!-- Active Filters Display -->
                <?php if (!empty($selectedCategory) || !empty($searchQuery) || $minPrice > 0 || $maxPrice < $globalMaxPrice || $sortBy !== 'default'): ?>
                  <div class="active-filters mt-2">
                    <small class="text-muted">Active filters: </small>
                    <?php if (!empty($selectedCategory)): ?>
                      <span class="badge bg-secondary me-1">Category: <?php echo htmlspecialchars($selectedCategory); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($searchQuery)): ?>
                      <span class="badge bg-secondary me-1">Search: "<?php echo htmlspecialchars($searchQuery); ?>"</span>
                    <?php endif; ?>
                    <?php if ($minPrice > 0 || $maxPrice < $globalMaxPrice): ?>
                      <span class="badge bg-secondary me-1">Price: RP <?php echo number_format($minPrice, 0, ',', '.'); ?> - RP <?php echo number_format($maxPrice, 0, ',', '.'); ?></span>
                    <?php endif; ?>
                    <?php if ($sortBy !== 'default'): ?>
                      <span class="badge bg-info me-1">Sort: <?php 
                        switch($sortBy) {
                          case 'name_asc': echo 'Name (A-Z)'; break;
                          case 'name_desc': echo 'Name (Z-A)'; break;
                          case 'price_asc': echo 'Price (Low-High)'; break;
                          case 'price_desc': echo 'Price (High-Low)'; break;
                          default: echo 'Default'; break;
                        }
                      ?></span>
                    <?php endif; ?>
                    <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary btn-sm ms-2 clear-filters">Clear All</a>
                  </div>
                <?php endif; ?>
              </div>
              <div class="sort-by">
                <div class="sort-wrapper position-relative">
                  <div class="sort-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 6h18M7 12h10m-7 6h4"></path>
                    </svg>
                  </div>
                  <select id="input-sort" class="form-control sort-select">
                    <option value="default" <?php echo $sortBy === 'default' ? 'selected' : ''; ?>>Default sorting</option>
                    <option value="name_asc" <?php echo $sortBy === 'name_asc' ? 'selected' : ''; ?>>Name (A - Z)</option>
                    <option value="name_desc" <?php echo $sortBy === 'name_desc' ? 'selected' : ''; ?>>Name (Z - A)</option>
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Price (Low - High)</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Price (High - Low)</option>
                  </select>
                  <div class="dropdown-arrow">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                  </div>
                </div>
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
                    <input class="search-field" name="search" placeholder="Search products..." type="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <!-- Preserve other filters -->
                    <?php if (!empty($selectedCategory)): ?>
                      <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <?php endif; ?>
                    <?php if ($minPrice > 0): ?>
                      <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                    <?php endif; ?>
                    <?php if ($maxPrice < $globalMaxPrice): ?>
                      <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                    <?php endif; ?>
                    <?php if ($sortBy !== 'default'): ?>
                      <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                    <?php endif; ?>
                    <div class="search-icon position-absolute end-0">
                      <button type="submit" class="btn p-0 border-0 bg-transparent">
                        <svg width="26" height="26" class="search">
                          <use xlink:href="#search"></use>
                        </svg>
                      </button>
                    </div>
                  </form>
                </div> 
              </div>
              <div class="widget-product-categories pt-5">
                <h5 class="widget-title text-decoration-underline text-uppercase">Categories</h5>
                <ul class="product-categories sidebar-list list-unstyled">
                  <li class="cat-item">
                    <a href="?<?php echo http_build_query(array_filter(array_merge($_GET, ['category' => '', 'page' => 1]))); ?>" 
                       class="<?php echo empty($selectedCategory) ? 'active' : ''; ?>">All</a>
                  </li>
                  <?php foreach ($categories as $category): ?>
                    <li class="cat-item">
                      <a href="?<?php echo http_build_query(array_filter(array_merge($_GET, ['category' => $category['slug'], 'page' => 1]))); ?>" 
                         class="<?php echo ($selectedCategory === $category['slug']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              
              <div class="widget-price-filter pt-3">
                <h5 class="widget-title text-decoration-underline text-uppercase">Filter By Price</h5>
                <div class="price-range-slider">
                  <div class="price-input-wrapper d-flex justify-content-between mb-3">
                    <div class="price-input-group">
                      <span class="price-label">Min:</span>
                      <input type="number" id="min-price" class="price-input" min="<?php echo $globalMinPrice; ?>" max="<?php echo $globalMaxPrice; ?>" value="<?php echo $minPrice; ?>">
                    </div>
                    <div class="price-input-group">
                      <span class="price-label">Max:</span>
                      <input type="number" id="max-price" class="price-input" min="<?php echo $globalMinPrice; ?>" max="<?php echo $globalMaxPrice; ?>" value="<?php echo $maxPrice; ?>">
                    </div>
                  </div>
                  <div class="slider-container">
                    <div class="price-slider">
                      <div class="progress"></div>
                    </div>
                    <div class="range-input">
                      <input type="range" class="range-min" min="<?php echo $globalMinPrice; ?>" max="<?php echo $globalMaxPrice; ?>" value="<?php echo $minPrice; ?>" step="1000">
                      <input type="range" class="range-max" min="<?php echo $globalMinPrice; ?>" max="<?php echo $globalMaxPrice; ?>" value="<?php echo $maxPrice; ?>" step="1000">
                    </div>
                  </div>
                  <div class="price-display mt-2 text-center">
                    <span id="price-display">RP <?php echo number_format($minPrice, 0, ',', '.'); ?> - RP <?php echo number_format($maxPrice, 0, ',', '.'); ?></span>
                  </div>
                  <button type="button" class="btn btn-outline-dark btn-sm mt-2 w-100" id="apply-price-filter">Apply Filter</button>
                </div>
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
    
    <script>
    $(document).ready(function() {
        // Price range slider functionality
        const rangeMin = $('.range-min');
        const rangeMax = $('.range-max');
        const priceInputMin = $('#min-price');
        const priceInputMax = $('#max-price');
        const priceDisplay = $('#price-display');
        const progress = $('.progress');
        
        const globalMin = <?php echo $globalMinPrice; ?>;
        const globalMax = <?php echo $globalMaxPrice; ?>;
        
        function updateSlider() {
            const minVal = parseInt(rangeMin.val());
            const maxVal = parseInt(rangeMax.val());
            
            // Ensure min is not greater than max
            if (minVal > maxVal) {
                if ($(this).hasClass('range-min')) {
                    rangeMin.val(maxVal);
                } else {
                    rangeMax.val(minVal);
                }
            }
            
            const minPercent = ((minVal - globalMin) / (globalMax - globalMin)) * 100;
            const maxPercent = ((maxVal - globalMin) / (globalMax - globalMin)) * 100;
            
            progress.css('left', minPercent + '%');
            progress.css('right', (100 - maxPercent) + '%');
            
            // Update input fields
            priceInputMin.val(rangeMin.val());
            priceInputMax.val(rangeMax.val());
            
            // Update display
            priceDisplay.text('RP ' + parseInt(rangeMin.val()).toLocaleString('id-ID') + ' - RP ' + parseInt(rangeMax.val()).toLocaleString('id-ID'));
        }
        
        function updateFromInputs() {
            const minVal = parseInt(priceInputMin.val()) || globalMin;
            const maxVal = parseInt(priceInputMax.val()) || globalMax;
            
            // Ensure values are within bounds
            const boundedMin = Math.max(globalMin, Math.min(minVal, globalMax));
            const boundedMax = Math.max(globalMin, Math.min(maxVal, globalMax));
            
            // Ensure min is not greater than max
            if (boundedMin > boundedMax) {
                priceInputMin.val(boundedMax);
                priceInputMax.val(boundedMax);
                rangeMin.val(boundedMax);
                rangeMax.val(boundedMax);
            } else {
                priceInputMin.val(boundedMin);
                priceInputMax.val(boundedMax);
                rangeMin.val(boundedMin);
                rangeMax.val(boundedMax);
            }
            
            updateSlider();
        }
        
        // Event listeners
        rangeMin.on('input', updateSlider);
        rangeMax.on('input', updateSlider);
        priceInputMin.on('input', updateFromInputs);
        priceInputMax.on('input', updateFromInputs);
        
        // Initialize slider
        updateSlider();
        
        // Apply filter button
        $('#apply-price-filter').click(function() {
            const minPrice = parseInt(priceInputMin.val());
            const maxPrice = parseInt(priceInputMax.val());
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('min_price', minPrice);
            urlParams.set('max_price', maxPrice);
            urlParams.set('page', 1); // Reset to first page
            
            // Navigate to new URL
            window.location.href = '?' + urlParams.toString();
        });
        
        // Clear filters functionality
        $('.clear-filters').click(function(e) {
            e.preventDefault();
            window.location.href = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
        });
        
        // Format number inputs
        $('#min-price, #max-price').on('blur', function() {
            const value = parseInt($(this).val());
            if (isNaN(value)) {
                $(this).val($(this).attr('min'));
            }
        });
        
        // Sort dropdown functionality
        $('#input-sort').change(function() {
            const selectedSort = $(this).val();
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            if (selectedSort === 'default') {
                urlParams.delete('sort');
            } else {
                urlParams.set('sort', selectedSort);
            }
            
            urlParams.set('page', 1); // Reset to first page
            
            // Navigate to new URL
            window.location.href = '?' + urlParams.toString();
        });
    });
    </script>
    
    <?php include 'includes/auth-scripts.php'; ?>
  </body>
</html> 