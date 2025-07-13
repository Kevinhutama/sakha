# Carousel Management System - Setup Guide

## Overview
This system allows administrators to manage carousel images for the Sakha store website through a web-based admin panel. The system supports separate images for web and mobile versions, with full CRUD (Create, Read, Update, Delete) functionality.

## Features
- **Admin Panel**: Manage carousel images through a user-friendly interface
- **Responsive Images**: Support for separate web and mobile images
- **Image Upload**: Secure file upload with validation
- **Order Management**: Control the display order of carousel slides
- **Status Control**: Enable/disable carousel images
- **Database Integration**: Store all carousel data in MySQL database

## Database Setup

### 1. Create Database Table
Run the following SQL script to create the carousel_images table:

```sql
-- Execute the carousel.sql file
mysql -u root -p admin_portal < admin/database/carousel.sql
```

Or manually execute the SQL commands in `admin/database/carousel.sql`

### 2. Database Configuration
Update the database configuration in both files:

**For Admin Panel** (`admin/includes/config.php`):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**For Store Website** (`store/index.php`):
```php
$host = 'localhost';
$dbname = 'admin_portal';
$username = 'root';
$password = '';
```

### 3. Move Existing Images
Move any existing banner images to the carousel folder:
```bash
cp store/images/banner-image*.jpg store/images/carousel/
```

**Note**: The initial data includes three carousel images that will be automatically inserted:
- Sajadah with Alquran (`images/carousel/banner-image.jpg`)
- Sajadah with Perfume (`images/carousel/banner-image1.jpg`)
- Shell Shape Decor (`images/carousel/banner-image2.jpg`)

## File Structure
```
admin/
├── carousel-management.php    # Main admin page for carousel management
├── carousel-handler.php       # Backend handler for CRUD operations
├── database/
│   └── carousel.sql          # Database schema and initial data
└── includes/
    └── config.php            # Database configuration

store/
├── index.php                 # Dynamic store homepage (converted from HTML)
├── index.html               # Original static homepage (backup)
└── images/
    └── carousel/            # Upload directory for carousel images
```

## Admin Panel Access

1. **Login**: Access the admin panel at `admin/authentication-login.php`
2. **Navigate**: Go to "Carousel Images" in the sidebar menu
3. **Manage**: Add, edit, delete, or toggle carousel images

## Usage Guide

### Adding New Carousel Images
1. Click "Add New Carousel Image" button
2. Fill in the required information:
   - **Title**: Display title for the carousel slide
   - **Description**: Brief description text
   - **Display Order**: Numeric order for slide sequence
   - **Web Image**: Image for desktop/tablet view (required)
   - **Mobile Image**: Optional separate image for mobile view
   - **Button Text**: Text for the call-to-action button
   - **Button URL**: Link destination for the button
   - **Active**: Toggle to enable/disable the slide
3. Click "Add Carousel Image" to save

### Editing Carousel Images
1. Click the edit (pencil) icon for the desired image
2. Modify the fields as needed
3. Upload new images if desired (old images will be replaced)
4. Click "Update Carousel Image" to save changes

### Managing Display Order
- Each carousel image has a display order number
- Lower numbers appear first in the carousel
- You can edit the display order to rearrange slides

### Toggling Active Status
- Click the toggle button to enable/disable carousel images
- Inactive images won't appear on the website
- Useful for temporary promotions or seasonal content

## Image Requirements

### File Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)

### File Size
- Maximum: 5MB per image
- Recommended: Under 1MB for optimal performance

### Image Dimensions
- **Web Images**: 1920x1080px (recommended)
- **Mobile Images**: 768x1024px (recommended)
- Images will be automatically resized by CSS to fit the carousel container

## Security Features

- **Authentication Required**: All admin functions require login
- **File Type Validation**: Only image files are accepted
- **File Size Limits**: Prevents large file uploads
- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Prevention**: All output is HTML-escaped

## Troubleshooting

### Common Issues

1. **Images not displaying**
   - Check file permissions on `store/images/carousel/` directory
   - Verify database connection settings
   - Ensure images were uploaded successfully

2. **Upload errors**
   - Check PHP upload settings (`upload_max_filesize`, `post_max_size`)
   - Verify directory permissions (755 recommended)
   - Check file size and format requirements

3. **Database connection errors**
   - Verify database credentials in config files
   - Ensure MySQL service is running
   - Check database name exists

### File Permissions
Set appropriate permissions for upload directory:
```bash
chmod 777 store/images/carousel/
```

**Note**: The directory needs full write permissions (777) for the web server to upload files. If you're concerned about security, you can use 755 and change the ownership to the web server user:
```bash
# Alternative: Change ownership to web server user
sudo chown -R _www:_www store/images/carousel/  # macOS
# or
sudo chown -R www-data:www-data store/images/carousel/  # Linux
chmod 755 store/images/carousel/
```

## API Endpoints

The `carousel-handler.php` file accepts the following actions:

- `add`: Create new carousel image
- `edit`: Update existing carousel image
- `delete`: Remove carousel image
- `toggle_status`: Change active status

All responses are in JSON format with `success` and `message` fields.

## Performance Considerations

- **Image Optimization**: Compress images before uploading
- **Caching**: Consider implementing browser caching for images
- **CDN**: For high-traffic sites, consider using a CDN for image delivery
- **Database Indexing**: Indexes are already created for optimal performance

## Backup Recommendations

1. **Database**: Regular backups of the `carousel_images` table
2. **Images**: Backup the `store/images/carousel/` directory
3. **Code**: Version control for all PHP files

## Future Enhancements

Potential improvements for future versions:
- Image cropping/resizing tools
- Bulk upload functionality
- Carousel preview in admin panel
- Advanced scheduling (start/end dates)
- A/B testing capabilities
- Analytics integration

## Support

For technical support or questions:
1. Check the troubleshooting section
2. Review server error logs
3. Verify database and file permissions
4. Test with different image formats and sizes 