<?php
// Include config if not already included
if (!class_exists('Database')) {
    require_once __DIR__ . '/config.php';
}
?>
<!-- Sidebar Start -->
<aside class="left-sidebar">
  <!-- Sidebar scroll-->
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="./index.php" class="text-nowrap logo-img">
        <img src="assets/images/logos/logo.svg" alt="" />
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-8"></i>
      </div>
    </div>
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Home</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./index.php" aria-expanded="false">
            <iconify-icon icon="solar:atom-line-duotone"></iconify-icon>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./notifications.php" aria-expanded="false">
            <iconify-icon icon="solar:bell-line-duotone"></iconify-icon>
            <span class="hide-menu">Notifications</span>
            <?php 
            // Show notification count badge in sidebar
            if (isLoggedIn()) {
                try {
                    $database = new Database();
                    $db = $database->getConnection();
                    $notificationManager = new NotificationManager($db);
                    $sidebarUnreadCount = $notificationManager->getUnreadCount($_SESSION['admin_id']);
                    
                    if ($sidebarUnreadCount > 0) {
                        echo '<span class="badge bg-danger ms-2 fs-1 rounded-pill">' . ($sidebarUnreadCount > 9 ? '9+' : $sidebarUnreadCount) . '</span>';
                    }
                } catch (Exception $e) {
                    // Silently fail for sidebar
                }
            }
            ?>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./carousel-management.php" aria-expanded="false">
            <iconify-icon icon="solar:widget-5-line-duotone"></iconify-icon>
            <span class="hide-menu">Carousel Images</span>
          </a>
        </li>
        <!-- ---------------------------------- -->
        <!-- Dashboard -->
        <!-- ---------------------------------- -->
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between" target="_blank"
            href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/index2.html" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:widget-add-line-duotone" class=""></iconify-icon>
              </span>
              <span class="hide-menu">eCommerce</span>
            </div>
            <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between" target="_blank"
            href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/index.html" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:chart-line-duotone" class=""></iconify-icon>
              </span>
              <span class="hide-menu">Analytics</span>
            </div>
            <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between" target="_blank"
            href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/index3.html" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:layers-line-duotone" class=""></iconify-icon>
              </span>
              <span class="hide-menu">CRM</span>
            </div>
            <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:home-angle-line-duotone"></iconify-icon>
              </span>
              <span class="hide-menu">Front Pages</span>
            </div>
            
          </a>
          <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-landingpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Homepage</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-aboutpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">About Us</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-blogpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Blog</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-blogdetailpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Blog Details</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-contactpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Contact Us</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-portfoliopage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Portfolio</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/frontend-pricingpage.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Pricing</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
          </ul>
        </li>

        <li>
          <span class="sidebar-divider lg"></span>
        </li>
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">UI</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./ui-buttons.php" aria-expanded="false">
            <iconify-icon icon="solar:layers-minimalistic-bold-duotone"></iconify-icon>
            <span class="hide-menu">Buttons</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./ui-alerts.php" aria-expanded="false">
            <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
            <span class="hide-menu">Alerts</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./ui-card.php" aria-expanded="false">
            <iconify-icon icon="solar:bookmark-square-minimalistic-line-duotone"></iconify-icon>
            <span class="hide-menu">Card</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./ui-forms.php" aria-expanded="false">
            <iconify-icon icon="solar:file-text-line-duotone"></iconify-icon>
            <span class="hide-menu">Forms</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./ui-typography.php" aria-expanded="false">
            <iconify-icon icon="solar:text-field-focus-line-duotone"></iconify-icon>
            <span class="hide-menu">Typography</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:cardholder-line-duotone"></iconify-icon>
              </span>
              <span class="hide-menu">Ui Elements</span>
            </div>
            
          </a>
          <ul aria-expanded="false" class="collapse first-level">
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/ui-accordian.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Accordian</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/ui-badge.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Badge</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/ui-dropdowns.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Dropdowns</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link justify-content-between" target="_blank"
                href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/ui-modals.html">
                <div class="d-flex align-items-center gap-3">
                  <span class="d-flex">
                    <span class="icon-small"></span>
                  </span>
                  <span class="hide-menu">Modals</span>
                </div>
                <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
              </a>
            </li>
            <!-- More submenu items... -->
          </ul>
        </li>

        <li>
          <span class="sidebar-divider lg"></span>
        </li>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Auth</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./authentication-login.php" aria-expanded="false">
            <iconify-icon icon="solar:login-3-line-duotone"></iconify-icon>
            <span class="hide-menu">Login</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./authentication-register.php" aria-expanded="false">
            <iconify-icon icon="solar:user-plus-rounded-line-duotone"></iconify-icon>
            <span class="hide-menu">Register</span>
          </a>
        </li>

        <li>
          <span class="sidebar-divider lg"></span>
        </li>
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Extra</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between" target="_blank"
            href="https://bootstrapdemos.wrappixel.com/materialM/dist/main/icon-solar.html" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex">
                <iconify-icon icon="solar:sticker-smile-circle-2-line-duotone" class=""></iconify-icon>
              </span>
              <span class="hide-menu">Solar Icon</span>
            </div>
            <span class="hide-menu badge bg-secondary-subtle text-secondary fs-1 py-1">Pro</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./icon-tabler.php" aria-expanded="false">
            <iconify-icon icon="solar:sticker-smile-circle-2-line-duotone"></iconify-icon>
            <span class="hide-menu">Tabler Icon</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="./sample-page.php" aria-expanded="false">
            <iconify-icon icon="solar:planet-3-line-duotone"></iconify-icon>
            <span class="hide-menu">Sample Page</span>
          </a>
        </li>
      </ul>
      <div
        class="unlimited-access d-flex align-items-center hide-menu bg-secondary-subtle position-relative mb-7 mt-4 p-3 rounded-3">
        <div class="flex-shrink-0">
          <h6 class="fw-semibold fs-4 mb-6 text-dark w-75 lh-sm">Check Pro Version</h6>
          <a href="https://www.wrappixel.com/templates/materialm-admin-dashboard-template/?ref=376#demos" target="_blank"
            class="btn btn-secondary fs-2 fw-semibold lh-sm">Check</a>
        </div>
        <div class="unlimited-access-img">
          <img src="assets/images/backgrounds/rupee.png" alt="" class="img-fluid">
        </div>
      </div>
    </nav>
    <!-- End Sidebar navigation -->
  </div>
  <!-- End Sidebar scroll-->
</aside>
<!--  Sidebar End --> 