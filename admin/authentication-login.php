<?php
require_once 'includes/config.php';

$page_title = "Login - MaterialM Admin Template";
$error_message = '';
$success_message = '';

// Start session
startSecureSession();

// Check for logout success message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = 'You have been successfully logged out.';
}

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Database authentication logic - Process BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = $_POST['remember'] ?? false;
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            // Create database connection
            $database = new Database();
            $db = $database->getConnection();
            
            // Create authentication object
            $auth = new AdminAuth($db);
            
            // Attempt to authenticate admin
            $admin = $auth->authenticate($username, $password);
            
            if ($admin) {
                // Authentication successful - set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['login_time'] = time();
                
                // Handle "Remember Me" functionality
                if ($remember) {
                    // Set secure cookie for 30 days
                    $cookie_value = base64_encode($admin['id'] . '|' . $admin['email'] . '|' . hash('sha256', $admin['email'] . 'secret_key'));
                    setcookie('remember_admin', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                }
                
                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                $error_message = 'Invalid email or password. Please check your credentials and try again.';
            }
            
        } catch (Exception $e) {
            $error_message = 'Unable to connect to the database. Please try again later.';
            // Log the actual error for debugging (don't show to user)
            error_log('Login error: ' . $e->getMessage());
        }
    }
}

ob_start();
?>

<div class="col-md-8 col-lg-6 col-xxl-3">
  <div class="card mb-0">
    <div class="card-body">
      <a href="./index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
        <img src="assets/images/logos/logo.svg" alt="">
      </a>
      <p class="text-center">Developed by Growthive</p>
      
      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="ti ti-alert-circle me-2"></i>
          <strong>Login Failed!</strong> <?php echo htmlspecialchars($error_message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($success_message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <div class="mb-3">
          <label for="username" class="form-label">Email Address</label>
          <input type="email" class="form-control <?php echo !empty($error_message) ? 'is-invalid' : ''; ?>" id="username" name="username" aria-describedby="emailHelp" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control <?php echo !empty($error_message) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-4">
          <!-- <div class="form-check">
            <input class="form-check-input primary" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label text-dark" for="remember">
              Remember this Device
            </label>
          </div>
          <a class="text-primary fw-bold" href="#" onclick="alert('Forgot password functionality would be implemented here')">Forgot Password ?</a> -->
        </div>
        <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2" id="loginBtn">
          <span id="loginBtnText">Sign In</span>
          <span id="loginBtnLoader" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
        </button>
        <!-- <div class="d-flex align-items-center justify-content-center">
          <p class="fs-4 mb-0 fw-bold">New to MaterialM?</p>
          <a class="text-primary fw-bold ms-2" href="./authentication-register.php">Create an account</a>
        </div> -->
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    const loginBtnLoader = document.getElementById('loginBtnLoader');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    <?php if (!empty($error_message)): ?>
    // Auto-focus on email field if there's an error
    usernameInput.focus();
    usernameInput.select();
    <?php endif; ?>
    
    // Handle form submission
    loginForm.addEventListener('submit', function(e) {
        // Basic client-side validation
        if (!usernameInput.value.trim() || !passwordInput.value.trim()) {
            e.preventDefault();
            alert('Please enter both email and password.');
            return;
        }
        
        // Show loading state
        loginBtn.disabled = true;
        loginBtnText.textContent = 'Signing In...';
        loginBtnLoader.classList.remove('d-none');
        
        // Remove error classes
        usernameInput.classList.remove('is-invalid');
        passwordInput.classList.remove('is-invalid');
    });
    
    // Clear error classes on input
    usernameInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
    
    passwordInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>

<?php
$content = ob_get_clean();
include 'includes/auth_layout.php';
?> 