<?php
// Check if user is logged in
// Session should already be started by the parent page

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$userAvatar = isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : '';
?>

<header id="header" class="site-header">
  <nav id="header-nav" class="navbar navbar-expand-lg px-3">
    <div class="container">
      <a class="navbar-brand d-lg-none" href="index.php">
        <img src="images/sakha-logo-text.png" class="logo">
      </a>
      <button class="navbar-toggler d-flex d-lg-none order-3 p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#bdNavbar" aria-controls="bdNavbar" aria-expanded="false" aria-label="Toggle navigation">Menu</button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="bdNavbar" aria-labelledby="bdNavbarOffcanvasLabel">
        <div class="offcanvas-header px-4 pb-0">
          <a class="navbar-brand" href="index.php">
            <img src="images/sakha-logo-text.png" class="logo">
          </a>
          <button type="button" class="btn-close btn-close-black" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdNavbar"></button>
        </div>
        <div class="offcanvas-body">
          <ul id="navbar" class="navbar-nav w-100 d-flex justify-content-between align-items-center">
            
            <ul class="list-unstyled d-lg-flex justify-content-md-between align-items-center">
              <li class="nav-item">
                <a class="nav-link ms-0" href="index.php">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link ms-0" href="shop.php">Shop</a>
              </li>
            </ul>
            
            <a class="navbar-brand d-none d-lg-block me-0" href="index.php">
              <img src="images/sakha-logo-text.png" class="logo">
            </a>

            <ul class="list-unstyled d-lg-flex justify-content-between align-items-center">
              <li class="nav-item search-item">
                <div id="search-bar" class="border-right d-none d-lg-block">
                  <form action="" autocomplete="on">
                    <input id="search" class="text-dark" name="search" type="text" placeholder="Search Here...">
                    <a type="submit" class="nav-link me-0" href="#" style="opacity: 0">Search</a>
                  </form>
                </div>
              </li>
              
              <?php if ($isLoggedIn): ?>
                <!-- User is logged in - show user profile and logout -->
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle me-0 user-profile-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user" style="font-size: 18px;"></i>
                    <?php if ($userName): ?>
                      <span class="d-none d-lg-inline ms-1"><?php echo htmlspecialchars($userName); ?></span>
                    <?php endif; ?>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="logout(); return false;"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                  </ul>
                </li>
              <?php else: ?>
                <!-- User is not logged in - show login button -->
                <li class="nav-item">
                  <a class="nav-link me-0" href="#" data-bs-toggle="modal" data-bs-target="#loginModal" title="Login">
                    <i class="fas fa-sign-in-alt" style="font-size: 18px;"></i>
                    <span class="d-none d-lg-inline ms-1">Login</span>
                  </a>
                </li>
              <?php endif; ?>
              
              <?php if ($isLoggedIn): ?>
                <!-- User is logged in - show cart -->
                <li class="cart-dropdown nav-item dropdown">
                  <a class="nav-link dropdown-toggle me-0" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" title="Cart">
                    <i class="fas fa-shopping-cart" style="font-size: 18px;"></i>
                    <span class="cart-count badge bg-primary rounded-pill ms-1">2</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-3">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                      <span class="text-primary">Your cart</span>
                      <span class="badge bg-primary rounded-pill">2</span>
                    </h4>
                    <ul class="list-group mb-3">
                      <li class="list-group-item bg-transparent border-dark d-flex justify-content-between lh-sm">
                        <div>
                          <h5 class="card-title fs-3 text-capitalize">
                            <a href="single-product.html">Red Sajadah</a>
                          </h5>
                          <small class="text-body-secondary">Soft texture matt coated.</small>
                        </div>
                        <span class="text-primary">$120</span>
                      </li>
                      <li class="list-group-item bg-transparent border-dark d-flex justify-content-between lh-sm">
                        <div>
                          <h5 class="card-title fs-3 text-capitalize">
                            <a href="single-product.html">Shiny Pot</a>
                          </h5>
                          <small class="text-body-secondary">This pot is ceramic.</small>
                        </div>
                        <span class="text-primary">$870</span>
                      </li>
                      <li class="list-group-item bg-transparent border-dark d-flex justify-content-between">
                        <span class="text-uppercase"><b>Total (USD)</b></span>
                        <strong>$990</strong>
                      </li>
                    </ul>
                    <div class="d-flex flex-wrap justify-content-center">
                      <a class="w-100 btn btn-dark mb-1" href="cart.php">View Cart</a>
                      <a class="w-100 btn btn-primary" href="checkout.php">Go to checkout</a>
                    </div>
                  </div>
                </li>
              <?php endif; ?>
            </ul>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</header>

<style>
.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #dee2e6;
}

.user-profile-link {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.user-profile-link:hover {
  text-decoration: none;
}

.cart-count {
  font-size: 11px;
  min-width: 18px;
  height: 18px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.nav-link {
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.nav-link:hover {
  color: #333 !important;
  transform: translateY(-1px);
}

.dropdown-item {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  transition: all 0.3s ease;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
  transform: translateX(5px);
}

.dropdown-menu {
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

@media (max-width: 991px) {
  .nav-link span {
    display: inline !important;
    margin-left: 8px;
  }
}
</style> 