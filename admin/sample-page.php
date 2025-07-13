<?php
$page_title = "Sample Page - MaterialM Admin Template";

ob_start();
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Sample Page</h5>
        <p class="mb-0">This is a sample page. You can start with this page to build your application.</p>
        
        <div class="mt-4">
          <h6>Features:</h6>
          <ul>
            <li>Shared navigation components</li>
            <li>Consistent layout across pages</li>
            <li>Easy to maintain and update</li>
            <li>PHP powered admin template</li>
          </ul>
        </div>
        
        <div class="mt-4">
          <h6>Available Pages:</h6>
          <div class="row">
            <div class="col-md-6">
              <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="ui-buttons.php">UI Buttons</a></li>
                <li><a href="ui-alerts.php">UI Alerts</a></li>
                <li><a href="ui-cards.php">UI Cards</a></li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul>
                <li><a href="ui-forms.php">UI Forms</a></li>
                <li><a href="ui-typography.php">Typography</a></li>
                <li><a href="authentication-login.php">Login</a></li>
                <li><a href="authentication-register.php">Register</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?> 