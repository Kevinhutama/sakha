<?php
$page_title = "Register - MaterialM Admin Template";

ob_start();
?>

<div class="col-md-8 col-lg-6 col-xxl-3">
  <div class="card mb-0">
    <div class="card-body">
      <a href="./index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
        <img src="assets/images/logos/logo.svg" alt="">
      </a>
      <p class="text-center">Create your account</p>
      <form method="POST" action="">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-4">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="mb-4">
          <div class="form-check">
            <input class="form-check-input primary" type="checkbox" value="1" id="terms" name="terms" required>
            <label class="form-check-label text-dark" for="terms">
              I agree to the <a href="#" class="text-primary">Terms & Conditions</a>
            </label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign Up</button>
        <div class="d-flex align-items-center justify-content-center">
          <p class="fs-4 mb-0 fw-bold">Already have an account?</p>
          <a class="text-primary fw-bold ms-2" href="./authentication-login.php">Sign In</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// Simple registration logic (in real application, use proper validation and database)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = $_POST['terms'] ?? false;
    
    if (empty($name) || empty($email) || empty($password)) {
        echo '<script>alert("Please fill in all fields!");</script>';
    } else if ($password !== $confirm_password) {
        echo '<script>alert("Passwords do not match!");</script>';
    } else if (!$terms) {
        echo '<script>alert("Please accept the terms and conditions!");</script>';
    } else {
        // In real app, save to database
        echo '<script>alert("Registration successful! Redirecting to login..."); window.location.href = "authentication-login.php";</script>';
        exit;
    }
}

$content = ob_get_clean();
include 'includes/auth_layout.php';
?> 