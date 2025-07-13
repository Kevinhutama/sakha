<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title : 'MaterialM Admin Template'; ?></title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <?php if(isset($additional_css)): ?>
    <?php echo $additional_css; ?>
  <?php endif; ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include 'sidebar.php'; ?>
    
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <?php include 'header.php'; ?>
      
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php echo $content; ?>
          
          <div class="py-6 px-6 text-center">
            <p class="mb-0 fs-4">Admin Portal &copy; <?php echo date('Y'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  
  <?php if(isset($additional_js)): ?>
    <?php echo $additional_js; ?>
  <?php endif; ?>
</body>

</html> 