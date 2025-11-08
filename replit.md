# Build Your Box for WooCommerce - Replit Setup

## Project Overview

This is a WordPress/WooCommerce plugin that allows customers to build custom product boxes with capacity tracking and quantity-based discounts. The plugin was imported from GitHub and has been set up to run in the Replit environment with a full WordPress + WooCommerce stack.

## Current Setup

- **WordPress Version**: 6.8.3
- **PHP Version**: 8.2
- **Database**: SQLite (via sqlite-database-integration plugin)
- **WooCommerce**: Installed and activated
- **Build Your Box Plugin**: Installed and activated

## Access Information

### WordPress Admin
- **URL**: http://0.0.0.0:5000/wp-admin
- **Username**: admin
- **Password**: admin123

### Frontend
- **Box Builder Page**: http://0.0.0.0:5000/box-builder

## Recent Changes

### Discount Rules Feature (Latest)

Implemented a complete discount rules system that allows admins to configure quantity-based discounts for Build Your Box orders.

**Location**: Admin → WooCommerce → Build Your Box → Discount Rules

**Format**: `minimum_quantity | discount_percentage` (one rule per line)

**Examples**:
```
6 | 5
10 | 10
15 | 15
```

**Implementation Details**:
1. **Backend (PHP)**:
   - Added `calculate_discount()` method in both `class-byb-ajax-handler.php` and `class-byb-cart-handler.php`
   - Parses discount rules from settings
   - Applies highest applicable discount based on item count
   - Discount calculation in AJAX handler for real-time frontend display
   - Discount calculation in Cart handler for WooCommerce cart integration

2. **Frontend (JavaScript)**:
   - Updated `updateBoxDisplay()` in `byb-scripts.js`
   - Shows subtotal, discount percentage/amount, and final total
   - Discount info only displays when applicable

3. **Template Updates**:
   - Added subtotal and discount rows in `templates/box-builder.php`
   - Rows are hidden when no discount applies

**How It Works**:
- Admin sets rules in settings (e.g., "6 | 5" means 5% off for 6+ items)
- When customers build a box, the system checks item count against rules
- Highest applicable discount is automatically applied
- Discount shows in real-time on frontend and in WooCommerce cart
- Discount is preserved through checkout and appears in orders

## Project Structure

```
build-your-box/
├── admin/
│   ├── settings/
│   │   ├── class-byb-settings-general.php
│   │   └── class-byb-settings-discount.php (discount rules settings)
│   └── class-byb-admin-settings.php
├── includes/
│   ├── class-byb-ajax-handler.php (discount calculation for frontend)
│   ├── class-byb-cart-handler.php (discount calculation for cart)
│   ├── class-byb-product-meta.php
│   └── class-byb-shortcode.php
├── templates/
│   └── box-builder.php (frontend template with discount display)
├── assets/
│   ├── css/
│   ├── js/
│   │   └── byb-scripts.js (discount display logic)
└── build-your-box.php (main plugin file)
```

## Setup Instructions

### Initial WordPress Setup

The WordPress environment is automatically configured by the `setup-wordpress.sh` script, which:
1. Downloads WordPress core
2. Installs SQLite database integration
3. Installs and activates WooCommerce
4. Symlinks the Build Your Box plugin
5. Creates demo products
6. Creates the Box Builder page with `[build_your_box]` shortcode

### Running the Project

The workflow is configured to run PHP's built-in server on port 5000:
```bash
cd wordpress && php -S 0.0.0.0:5000
```

### Configuring Discount Rules

1. Log in to WordPress admin
2. Go to WooCommerce → Build Your Box
3. Scroll to "Discount Rules" section
4. Add rules in format: `quantity | percentage` (one per line)
5. Click "Save Changes"

Example configuration:
```
6 | 5
10 | 10
15 | 15
20 | 20
```

This gives:
- 5% off for 6-9 items
- 10% off for 10-14 items
- 15% off for 15-19 items
- 20% off for 20+ items

## Notes

### SQLite Limitations
- WooCommerce's Action Scheduler has some compatibility issues with SQLite
- These errors don't affect the core functionality of the Build Your Box plugin
- Frontend box building and cart functionality work correctly

### Known Issues
- Action Scheduler SQL errors in logs (WooCommerce background tasks)
- These can be safely ignored for development/demo purposes

## Development

To modify the plugin:
1. Edit files in the project root (they're symlinked into WordPress)
2. Restart the workflow if you modify PHP files
3. Refresh the browser if you modify CSS/JS files

## Future Enhancements

Potential features for future versions:
- Tiered pricing based on box size
- Multiple box templates
- Save and share box configurations
- Subscription integration
- Product recommendations
