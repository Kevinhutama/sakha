<!-- Google Sign-In API -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    // Get authentication configuration from PHP
    const authConfig = <?php 
        require_once 'config/auth-config.php';
        echo getJsAuthConfig(); 
    ?>;
    
    // Initialize authentication when page loads
    window.onload = function() {
        initializeGoogleSignIn();
        initializeFacebookSDK();
        checkUserStatus();
    };

    function initializeGoogleSignIn() {
        if (typeof google !== 'undefined' && authConfig.enableGoogleLogin) {
            google.accounts.id.initialize({
                client_id: authConfig.googleClientId,
                callback: handleGoogleSignIn
            });
            
            // Render the Google Sign-In button
            google.accounts.id.renderButton(
                document.getElementById('google-signin-button'),
                {
                    theme: 'outline',
                    size: 'large',
                    width: 50,
                    height: 50,
                    type: 'icon'
                }
            );
        } else if (!authConfig.enableGoogleLogin) {
            document.getElementById('google-signin-button').style.display = 'none';
        } else {
            // Fallback if Google API is not loaded
            document.getElementById('google-signin-button').onclick = function() {
                alert('Google Sign-In is not available. Please check your internet connection.');
            };
        }
    }

    function initializeFacebookSDK() {
        if (!authConfig.enableFacebookLogin) {
            $('button[onclick="loginWithFacebook()"]').hide();
            return;
        }
        
        window.fbAsyncInit = function() {
            FB.init({
                appId: authConfig.facebookAppId,
                cookie: true,
                xfbml: true,
                version: 'v18.0'
            });
        };
        
        // Load Facebook SDK
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }

    function handleGoogleSignIn(response) {
        const credential = response.credential;
        
        // Show loading state
        showLoginMessage('Signing in with Google...', 'info');
        
        // Send the credential to your backend
        $.ajax({
            url: 'auth-handler.php',
            type: 'POST',
            data: {
                action: 'google_login',
                id_token: credential
            },
            success: function(data) {
                if (data.success) {
                    showLoginMessage('Login successful! Redirecting...', 'success');
                    updateUIAfterLogin(data.user);
                    setTimeout(() => {
                        $('#loginModal').modal('hide');
                        location.reload(); // Reload to update UI
                    }, 1000);
                } else {
                    showLoginMessage('Login failed: ' + data.message, 'danger');
                }
            },
            error: function() {
                showLoginMessage('An error occurred during login. Please try again.', 'danger');
            }
        });
    }

    // Handle regular login form submission
    function setupLoginForm() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#loginEmail').val();
            const password = $('#loginPassword').val();
            const remember = $('#rememberMe').is(':checked');
            
            // Show loading state
            $('#loginBtn').prop('disabled', true).text('Signing in...');
            
            $.ajax({
                url: 'auth-handler.php',
                type: 'POST',
                data: {
                    action: 'regular_login',
                    email: email,
                    password: password,
                    remember: remember
                },
                success: function(data) {
                    if (data.success) {
                        showLoginMessage('Login successful! Redirecting...', 'success');
                        updateUIAfterLogin(data.user);
                        setTimeout(() => {
                            $('#loginModal').modal('hide');
                            location.reload(); // Reload to update UI
                        }, 1000);
                    } else {
                        showLoginMessage(data.message, 'danger');
                    }
                },
                error: function() {
                    showLoginMessage('An error occurred during login. Please try again.', 'danger');
                },
                complete: function() {
                    $('#loginBtn').prop('disabled', false).text('Sign In');
                }
            });
        });

        // Clear login form when modal is closed
        $('#loginModal').on('hidden.bs.modal', function () {
            $('#loginForm')[0].reset();
            $('#loginMessage').hide();
        });
    }

    function loginWithFacebook() {
        if (!authConfig.enableFacebookLogin) {
            alert('Facebook login is disabled.');
            return;
        }
        
        if (typeof FB === 'undefined') {
            alert('Facebook SDK is not loaded. Please refresh the page and try again.');
            return;
        }
        
        showLoginMessage('Connecting to Facebook...', 'info');
        
        FB.login(function(response) {
            if (response.authResponse) {
                showLoginMessage('Signing in with Facebook...', 'info');
                
                $.ajax({
                    url: 'auth-handler.php',
                    type: 'POST',
                    data: {
                        action: 'facebook_login',
                        access_token: response.authResponse.accessToken
                    },
                    success: function(data) {
                        if (data.success) {
                            showLoginMessage('Login successful! Redirecting...', 'success');
                            updateUIAfterLogin(data.user);
                            setTimeout(() => {
                                $('#loginModal').modal('hide');
                                location.reload(); // Reload to update UI
                            }, 1000);
                        } else {
                            showLoginMessage('Login failed: ' + data.message, 'danger');
                        }
                    },
                    error: function() {
                        showLoginMessage('An error occurred during login. Please try again.', 'danger');
                    }
                });
            } else {
                showLoginMessage('Facebook login was cancelled', 'warning');
            }
        }, {scope: 'email'});
    }

    function showLoginMessage(message, type) {
        const messageDiv = $('#loginMessage');
        messageDiv.removeClass('alert-success alert-danger alert-info alert-warning');
        messageDiv.addClass('alert-' + type);
        messageDiv.text(message);
        messageDiv.show();
    }

    function updateUIAfterLogin(user) {
        // This function is for UI updates without page reload
        // Used when checking existing login status, not for new logins
        console.log('User is logged in:', user.name || user.email);
    }

    function checkUserStatus() {
        $.ajax({
            url: 'auth-handler.php',
            type: 'POST',
            data: {
                action: 'get_user_status'
            },
            success: function(data) {
                if (data.success && data.logged_in) {
                    updateUIAfterLogin(data.user);
                }
            }
        });
    }

    function logout() {
        $.ajax({
            url: 'auth-handler.php',
            type: 'POST',
            data: {
                action: 'logout'
            },
            success: function(data) {
                if (data.success) {
                    location.reload(); // Reload to update UI
                }
            }
        });
    }

    // Initialize when jQuery is ready
    $(document).ready(function() {
        setupLoginForm();
    });
</script> 