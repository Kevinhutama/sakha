# Authentication Setup Guide for Sakha Store

## Overview
This guide will help you set up comprehensive authentication for your Sakha store website. The implementation supports Google Sign-In, Facebook Login, and regular email/password authentication with a centralized configuration system.

## Prerequisites
- A Google Cloud Platform account (for Google Login)
- A Facebook Developer account (for Facebook Login)
- Access to Google Cloud Console and Facebook Developer Console
- Your website must be served over HTTPS (required for OAuth providers)
- PHP 7.4+ with PDO MySQL extension
- MySQL database

## Quick Start

### 1. Database Setup
```bash
# Import the users table
mysql -u root -p admin_portal < store/database/users.sql
```

### 2. Configuration
Edit `store/config/auth-config.php` and update these values:

```php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your-actual-google-client-id.apps.googleusercontent.com');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'your-actual-facebook-app-id');
define('FACEBOOK_APP_SECRET', 'your-actual-facebook-app-secret');

// Database configuration (if different from defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_portal');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
```

### 3. Test
Navigate to your store and test the authentication methods.

## Detailed Setup

### Google Sign-In Setup

#### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable "Google Identity" services

#### Step 2: Configure OAuth Consent Screen
1. Navigate to "APIs & Services" > "OAuth consent screen"
2. Choose "External" user type (for public users)
3. Fill in the required information:
   - App name: "Sakha Store"
   - User support email: Your email
   - Developer contact information: Your email
4. Add your domain to "Authorized domains"
5. Save and continue

#### Step 3: Create OAuth 2.0 Credentials
1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application" as the application type
4. Add your authorized JavaScript origins:
   - `https://yourdomain.com` (your production domain)
   - `http://localhost` (for local testing)
5. Add authorized redirect URIs:
   - `https://yourdomain.com/store/index.php`
   - `https://yourdomain.com/store/checkout.php`
   - `http://localhost/sakha/store/index.php` (for local testing)
6. Click "Create"
7. Copy the Client ID

### Facebook Login Setup

#### Step 1: Create Facebook App
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select an existing one
3. Choose "Consumer" as the app type
4. Fill in your app details

#### Step 2: Configure Facebook Login
1. In your app dashboard, go to "Products" > "Facebook Login"
2. Click "Settings" under Facebook Login
3. Add your redirect URIs:
   - `https://yourdomain.com/store/index.php`
   - `https://yourdomain.com/store/checkout.php`
   - `http://localhost/sakha/store/index.php` (for local testing)
4. Add your domains to "Valid OAuth Redirect URIs"

#### Step 3: Get App Credentials
1. Go to "Settings" > "Basic"
2. Copy your App ID and App Secret
3. Set your app to "Live" mode when ready for production

### Database Schema

The system uses a comprehensive users table that supports all authentication methods:

```sql
CREATE TABLE users (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  email varchar(255) UNIQUE NOT NULL,
  name varchar(255) NOT NULL,
  password varchar(255) DEFAULT NULL,          -- For regular login
  google_id varchar(255) UNIQUE DEFAULT NULL,  -- For Google login
  facebook_id varchar(255) UNIQUE DEFAULT NULL, -- For Facebook login
  avatar varchar(500) DEFAULT NULL,
  login_type enum('regular', 'google', 'facebook', 'mixed') DEFAULT 'regular',
  email_verified tinyint(1) DEFAULT 0,
  phone varchar(20) DEFAULT NULL,
  date_of_birth date DEFAULT NULL,
  gender enum('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL,
  remember_token varchar(255) DEFAULT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login timestamp NULL DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1
);
```

## Configuration Options

### Centralized Configuration (`store/config/auth-config.php`)

#### Provider Settings
```php
// Enable/disable authentication providers
define('ENABLE_GOOGLE_LOGIN', true);
define('ENABLE_FACEBOOK_LOGIN', true);
define('ENABLE_REGULAR_LOGIN', true);
define('ENABLE_REMEMBER_ME', true);
```

#### Security Settings
```php
// Password hashing cost (higher = more secure but slower)
define('BCRYPT_COST', 12);

// Session and token lifetimes
define('SESSION_LIFETIME', 86400 * 30); // 30 days
define('REMEMBER_TOKEN_LIFETIME', 86400 * 30); // 30 days
define('TOKEN_LENGTH', 32);

// Login security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
```

#### Application URLs
```php
// Update these for your domain
define('APP_URL', 'http://localhost/sakha/store');
define('APP_URL_SECURE', 'https://yourdomain.com/store');
```

## Features

### Multi-Provider Authentication
- ✅ Google Sign-In with JWT verification
- ✅ Facebook Login with access token verification
- ✅ Regular email/password authentication
- ✅ Mixed authentication (users can link multiple methods)

### Security Features
- ✅ Secure password hashing with bcrypt
- ✅ JWT token verification with provider APIs
- ✅ XSS protection with proper output escaping
- ✅ SQL injection protection with prepared statements
- ✅ Session management with configurable lifetimes
- ✅ Remember me functionality with secure tokens

### User Management
- ✅ Comprehensive user profiles
- ✅ Login history tracking
- ✅ User status management
- ✅ Account linking (Google + Facebook + Regular)
- ✅ Email verification support (configurable)

### Developer Features
- ✅ Centralized configuration system
- ✅ Feature flags for enabling/disabling providers
- ✅ Comprehensive error handling
- ✅ Debug mode support
- ✅ Production-ready security settings

## Testing

### Local Testing (XAMPP/MAMP)
1. Start your local server
2. Navigate to `http://localhost/sakha/store/index.php`
3. Click "Account" to open login modal
4. Test each authentication method:
   - Google Sign-In button
   - Facebook Login button
   - Regular email/password form

### Production Testing
1. Upload files to your web server
2. Ensure HTTPS is properly configured
3. Test all authentication flows
4. Verify user data is stored correctly

## Authentication Flow

### Google Sign-In Flow
1. User clicks Google Sign-In button
2. Google Identity Services loads authentication popup
3. User completes Google authentication
4. Google returns JWT token to application
5. Backend verifies token with Google's servers
6. User information extracted and stored/updated in database
7. User session created
8. UI updated to show logged-in state

### Facebook Login Flow
1. User clicks Facebook Login button
2. Facebook SDK loads authentication popup
3. User completes Facebook authentication
4. Facebook returns access token to application
5. Backend verifies token with Facebook's Graph API
6. User information retrieved and stored/updated in database
7. User session created
8. UI updated to show logged-in state

### Regular Login Flow
1. User submits email/password form
2. Backend verifies credentials against database
3. Password verified using bcrypt
4. User session created
5. Optional remember me token generated
6. UI updated to show logged-in state

## Troubleshooting

### Common Issues

**"Google Sign-In is not available"**
- Check that Google Sign-In API is loading correctly
- Ensure you're serving over HTTPS in production
- Verify Client ID is correct in configuration

**"Facebook SDK is not loaded"**
- Check that Facebook SDK is loading correctly
- Ensure App ID is correct in configuration
- Verify domain is whitelisted in Facebook app settings

**"Invalid token" errors**
- Check that your credentials match those in respective consoles
- Ensure your domain is authorized in provider settings
- Verify tokens haven't expired

**Database connection errors**
- Check database credentials in `auth-config.php`
- Ensure `users` table exists
- Verify database server is running

### Debug Mode

Enable debug mode by adding to `auth-handler.php`:

```php
// Add at the top after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Production Deployment Checklist

Before going live:

1. ✅ Replace all placeholder credentials with real ones
2. ✅ Update database credentials for production
3. ✅ Ensure HTTPS is properly configured
4. ✅ Test all authentication flows
5. ✅ Remove debug mode settings
6. ✅ Set appropriate session lifetimes
7. ✅ Configure proper error logging
8. ✅ Test cross-browser compatibility
9. ✅ Verify OAuth redirect URIs are correct
10. ✅ Test remember me functionality

## Advanced Configuration

### Custom Login Types
Add custom login types by modifying the `login_type` enum in the database:

```sql
ALTER TABLE users MODIFY login_type ENUM('regular', 'google', 'facebook', 'mixed', 'custom');
```

### Additional User Fields
The database schema includes fields for future expansion:
- `phone` - User phone number
- `date_of_birth` - User's date of birth
- `gender` - User's gender preference
- `email_verified` - Email verification status

### Email Verification
Enable email verification by setting:
```php
define('ENABLE_EMAIL_VERIFICATION', true);
```

## Support

For issues with this implementation:
1. Check browser console for JavaScript errors
2. Check web server error logs
3. Verify provider console configurations
4. Test with different browsers
5. Check database connectivity and table structure

## API Reference

### Authentication Endpoints

**POST /auth-handler.php**
- `action=google_login` - Google Sign-In
- `action=facebook_login` - Facebook Login
- `action=regular_login` - Email/Password Login
- `action=logout` - Logout
- `action=get_user_status` - Check login status

### Configuration Functions

**getJsAuthConfig()** - Returns JavaScript configuration object
**isLoginProviderEnabled($provider)** - Check if provider is enabled
**getDbConnection()** - Get database connection
**getRedirectUrl($page)** - Get redirect URL for page 