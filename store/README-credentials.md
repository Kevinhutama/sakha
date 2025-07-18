# Credentials Configuration

This document explains how to set up the credentials file for the Sakha store application.

## Overview

The application uses a secure credentials system to store sensitive API keys and secrets. The actual credentials file is excluded from version control to prevent accidental exposure of sensitive data.

## Setup Instructions

### 1. Create the credentials file

Copy the sample credentials file to create your own:

```bash
cd store/config/
cp credentials.sample.php credentials.php
```

### 2. Configure your API keys

Edit `credentials.php` and replace the placeholder values with your actual API keys:

```php
<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your_actual_google_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_actual_google_client_secret');

// Rajaongkir API Configuration  
define('RAJAONGKIR_API_KEY', 'your_actual_rajaongkir_api_key');
// ... other credentials
?>
```

### 3. Verify the setup

The credentials file should now be working with your application. You can test the Rajaongkir integration by visiting:
- `test-rajaongkir.php` - Test Rajaongkir API functionality
- `test-local-cities.php` - Test local city data loading

## Security Notes

🔒 **IMPORTANT SECURITY PRACTICES:**

1. **Never commit credentials.php** - This file is automatically excluded by `.gitignore`
2. **Use environment variables** - Consider using environment variables for production deployments
3. **Rotate API keys regularly** - Change your API keys periodically for security
4. **Limit API key permissions** - Only grant necessary permissions to each API key

## File Structure

```
store/config/
├── credentials.sample.php    # Sample file (safe to commit)
├── credentials.php          # Your actual credentials (excluded from git)
└── auth-config.php         # Authentication configuration
```

## API Keys Required

### Rajaongkir API
- **Purpose**: Shipping cost calculation
- **Get your key**: [Rajaongkir.com](https://rajaongkir.com)
- **Configuration**: `RAJAONGKIR_API_KEY`

### Google OAuth (Optional)
- **Purpose**: Google login integration
- **Get your key**: [Google Cloud Console](https://console.cloud.google.com)
- **Configuration**: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`

### Facebook OAuth (Optional)
- **Purpose**: Facebook login integration  
- **Get your key**: [Facebook Developers](https://developers.facebook.com)
- **Configuration**: `FACEBOOK_APP_ID`, `FACEBOOK_APP_SECRET`

## Troubleshooting

### "credentials.php not found" error
- Make sure you've copied `credentials.sample.php` to `credentials.php`
- Check that the file is in the correct location: `store/config/credentials.php`

### API key errors
- Verify your API keys are correctly set in `credentials.php`
- Check that your API keys have the necessary permissions
- Ensure there are no extra spaces or characters in your API keys

## Environment Variables (Production)

For production deployments, consider using environment variables:

```php
// Example: Use environment variables with fallback to constants
$apiKey = getenv('RAJAONGKIR_API_KEY') ?: RAJAONGKIR_API_KEY;
```

This provides an additional layer of security for production environments. 