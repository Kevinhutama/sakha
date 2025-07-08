# Google Authentication Setup Guide

## Overview
This guide will help you set up Google authentication for your Sakha store website. The implementation uses Google Identity Services (the latest Google Sign-In API) for secure OAuth authentication.

## Prerequisites
- A Google Cloud Platform account
- Access to Google Cloud Console
- Your website must be served over HTTPS (required for Google Sign-In)

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Google+ API" and "Google Identity" services

## Step 2: Configure OAuth Consent Screen

1. Navigate to "APIs & Services" > "OAuth consent screen"
2. Choose "External" user type (for public users)
3. Fill in the required information:
   - App name: "Sakha Store"
   - User support email: Your email
   - Developer contact information: Your email
4. Add your domain to "Authorized domains"
5. Save and continue

## Step 3: Create OAuth 2.0 Credentials

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
7. Copy the Client ID (you'll need this in the next step)

## Step 4: Configure Your Application

### Update Client ID in Your Files

Replace `YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com` with your actual Client ID in these files:

**In `store/index.php`:**
```javascript
const GOOGLE_CLIENT_ID = 'your-actual-client-id.apps.googleusercontent.com';
```

**In `store/checkout.php`:**
```javascript
const GOOGLE_CLIENT_ID = 'your-actual-client-id.apps.googleusercontent.com';
```

**In `store/auth-handler.php`:**
```php
$google_client_id = 'your-actual-client-id.apps.googleusercontent.com';
```

### Database Setup

1. Run the SQL script to create the users table:
```bash
mysql -u root -p admin_portal < store/database/users.sql
```

Or import the SQL file through phpMyAdmin.

## Step 5: Test the Implementation

### Local Testing (XAMPP/MAMP)

1. Start your local server (XAMPP/MAMP)
2. Navigate to `http://localhost/sakha/store/index.php`
3. Click the "Account" button to open the login modal
4. Click the Google Sign-In button
5. Complete the Google authentication flow

### Production Testing

1. Upload your files to your web server
2. Ensure your site is served over HTTPS
3. Test the Google Sign-In functionality

## Security Configuration

### Update auth-handler.php

In `store/auth-handler.php`, update these settings:

```php
// Database configuration
$host = 'your-db-host';
$dbname = 'your-db-name';
$username = 'your-db-username';
$password = 'your-db-password';

// Google OAuth configuration
$google_client_id = 'your-actual-client-id.apps.googleusercontent.com';
$google_client_secret = 'your-google-client-secret'; // Optional, not used in current implementation
```

## Features Included

### Google Authentication
- ✅ Secure token verification with Google
- ✅ Automatic user registration for new Google users
- ✅ User session management
- ✅ Integration with existing user system

### Regular Email/Password Authentication
- ✅ Secure password hashing with bcrypt
- ✅ Remember me functionality
- ✅ Session management
- ✅ Login form validation

### User Management
- ✅ User profile storage (name, email, avatar)
- ✅ Login history tracking
- ✅ User status checking
- ✅ Logout functionality

## How It Works

### Google Sign-In Flow
1. User clicks Google Sign-In button
2. Google Identity Services loads authentication popup
3. User completes Google authentication
4. Google returns a JWT token to your application
5. Your backend verifies the token with Google
6. User information is extracted and stored in database
7. User session is created
8. UI is updated to show logged-in state

### Backend Security
- JWT token verification with Google's servers
- Secure database operations with prepared statements
- XSS protection with proper output escaping
- Session management with PHP sessions

## Troubleshooting

### Common Issues

**"Google Sign-In is not available"**
- Check that the Google Sign-In API is loading correctly
- Ensure you're serving the site over HTTPS in production
- Verify your Client ID is correct

**"Invalid Google token"**
- Check that your Client ID matches the one in Google Cloud Console
- Ensure your domain is authorized in Google Cloud Console
- Verify the token hasn't expired

**Database connection errors**
- Check your database credentials in `auth-handler.php`
- Ensure the `users` table exists
- Verify database server is running

### Debug Mode

To enable debug mode, add this to your `auth-handler.php`:

```php
// Add at the top after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Additional Configuration

### Customizing User Interface

You can customize the login experience by:

1. **Styling the Google Sign-In button** in your CSS
2. **Adding more user profile fields** to the database
3. **Implementing user profile pages**
4. **Adding logout buttons** in the navigation

### Production Deployment

Before going live:

1. ✅ Replace all placeholder Client IDs with real ones
2. ✅ Update database credentials for production
3. ✅ Ensure HTTPS is properly configured
4. ✅ Test all authentication flows
5. ✅ Remove debug mode settings

## Support

For issues with this implementation:
1. Check the browser console for JavaScript errors
2. Check your web server error logs
3. Verify Google Cloud Console configuration
4. Test with different browsers

For Google-specific issues, refer to:
- [Google Identity Services Documentation](https://developers.google.com/identity/gsi/web)
- [Google Cloud Console Help](https://cloud.google.com/support)

---

## Quick Start Checklist

- [ ] Create Google Cloud project
- [ ] Configure OAuth consent screen
- [ ] Create OAuth 2.0 credentials
- [ ] Update Client ID in all files
- [ ] Run database SQL script
- [ ] Test locally
- [ ] Deploy to production
- [ ] Test on live site

Your Google authentication is now ready to use! 