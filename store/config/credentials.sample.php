<?php
/**
 * Sample credentials configuration file
 * 
 * SETUP INSTRUCTIONS:
 * 1. Copy this file to credentials.php
 * 2. Replace the placeholder values with your actual API keys
 * 3. Never commit the credentials.php file to version control
 */

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your_google_client_id_here');
define('GOOGLE_CLIENT_SECRET', 'your_google_client_secret_here');

// Facebook OAuth Configuration  
define('FACEBOOK_APP_ID', 'your_facebook_app_id_here');
define('FACEBOOK_APP_SECRET', 'your_facebook_app_secret_here');

// Rajaongkir API Configuration
define('RAJAONGKIR_API_KEY', 'your_rajaongkir_api_key_here');
define('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter/');

// Origin City for Shipping Calculation
define('RAJAONGKIR_ORIGIN_CITY', 23); // Bandung city ID

// Add other sensitive credentials here as needed
// Example:
// define('PAYMENT_GATEWAY_API_KEY', 'your_payment_gateway_api_key_here');
// define('EMAIL_SERVICE_API_KEY', 'your_email_service_api_key_here');
?> 