# MaterialM Admin Template - PHP Version

This is a PHP conversion of the MaterialM Bootstrap Admin Template. The template has been restructured to use shared navigation components and PHP includes for better maintainability.

## Project Structure

```
admin-portal/
├── assets/                 # CSS, JS, images, and other static assets
├── includes/              # Shared PHP components
│   ├── layout.php         # Main layout template (with sidebar & header)
│   ├── auth_layout.php    # Authentication layout (login/register pages)
│   ├── topstrip.php       # Top promotional banner
│   ├── sidebar.php        # Left sidebar navigation
│   └── header.php         # Top header with notifications
├── index.php              # Dashboard page
├── sample-page.php        # Sample page template
├── ui-buttons.php         # UI Buttons page
├── authentication-login.php    # Login page
├── authentication-register.php # Register page
└── README.md              # This file
```

## Features

- **Shared Navigation**: All navigation components are centralized in the `includes/` folder
- **Template Inheritance**: Uses PHP output buffering for clean template structure
- **Responsive Design**: Bootstrap-based responsive admin template
- **Modular Architecture**: Easy to maintain and extend
- **Authentication Pages**: Separate layout for login/register pages
- **PHP Powered**: Server-side functionality with form handling

## Getting Started

### Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx) or local development server

### Installation

1. Place the files in your web server directory (e.g., `htdocs` for XAMPP)
2. Access the template through your web browser:
   ```
   http://localhost/admin-portal/index.php
   ```

### Demo Credentials
For the login page, use:
- **Email**: admin@example.com
- **Password**: password

## Creating New Pages

### For Regular Admin Pages

Create a new PHP file following this pattern:

```php
<?php
$page_title = "Your Page Title - MaterialM Admin Template";

// Optional: Add custom CSS
$additional_css = '
<link rel="stylesheet" href="custom.css">
';

// Optional: Add custom JavaScript
$additional_js = '
<script src="custom.js"></script>
';

ob_start();
?>

<!-- Your page content here -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Your Content</h5>
        <p>Page content goes here...</p>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?>
```

### For Authentication Pages

Use the auth layout for login/register type pages:

```php
<?php
$page_title = "Your Auth Page - MaterialM Admin Template";

ob_start();
?>

<div class="col-md-8 col-lg-6 col-xxl-3">
  <!-- Your auth form content -->
</div>

<?php
$content = ob_get_clean();
include 'includes/auth_layout.php';
?>
```

## Customization

### Modifying Navigation

1. **Sidebar**: Edit `includes/sidebar.php` to add/remove menu items
2. **Header**: Edit `includes/header.php` to modify top navigation
3. **Top Strip**: Edit `includes/topstrip.php` to change promotional banner

### Adding New Menu Items

In `includes/sidebar.php`, add new menu items following this pattern:

```html
<li class="sidebar-item">
  <a class="sidebar-link" href="./your-page.php" aria-expanded="false">
    <iconify-icon icon="solar:your-icon"></iconify-icon>
    <span class="hide-menu">Your Page</span>
  </a>
</li>
```

### Styling

- Main styles are in `assets/css/styles.min.css`
- Add custom CSS using the `$additional_css` variable in your pages
- Icons use Solar Icons via Iconify

## Available Pages

- **Dashboard** (`index.php`) - Main admin dashboard
- **UI Buttons** (`ui-buttons.php`) - Button components showcase
- **Sample Page** (`sample-page.php`) - Template for new pages
- **Login** (`authentication-login.php`) - User login form
- **Register** (`authentication-register.php`) - User registration form

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This template is based on MaterialM by WrapPixel. Please check the original license terms.

## Support

For issues and questions:
1. Check the original MaterialM documentation
2. Review the PHP code structure in this README
3. Ensure all file paths are correct relative to your installation

## Notes

- This is a demonstration conversion - implement proper security measures for production use
- Authentication logic is simplified for demo purposes
- Add proper database integration for real applications
- Consider adding session management for user authentication 