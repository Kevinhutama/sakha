<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title w-100 text-center" id="loginModalLabel">Login to Your Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 pb-4">
        <!-- Email Login Form -->
        <form class="mt-3" id="loginForm">
          <div class="mb-3">
            <label for="loginEmail" class="form-label">Email address</label>
            <input type="email" class="form-control" id="loginEmail" placeholder="Enter your email" required>
          </div>
          <div class="mb-3">
            <label for="loginPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label" for="rememberMe">
              Remember me
            </label>
          </div>
          <button type="submit" class="btn btn-dark w-100 mb-3" id="loginBtn">Sign In</button>
          <div id="loginMessage" class="alert" style="display: none;"></div>
        </form>
        
        <div class="text-center">
          <a href="#" class="text-muted small">Forgot password?</a>
          <div class="mt-2">
            <span class="text-muted small">Don't have an account? </span>
            <a href="#" class="text-primary small">Sign up</a>
          </div>
        </div>
        
        <div class="text-center mt-4">
          <hr class="my-3">
          <p class="text-muted small mb-3">Or continue with</p>
          
          <!-- Social Login Icons -->
          <div class="d-flex justify-content-center gap-3">
            <div id="google-signin-button" class="btn btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;" title="Sign in with Google">
              <svg width="24" height="24" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
            </div>
            
            <button type="button" class="btn btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #1877F2; border-color: #1877F2;" onclick="loginWithFacebook()" title="Sign in with Facebook">
              <svg width="24" height="24" viewBox="0 0 24 24">
                <path fill="white" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> 