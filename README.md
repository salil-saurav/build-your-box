# Build Your Box for WooCommerce

A complete WordPress WooCommerce plugin that replicates the "Build Your Box" functionality, allowing customers to create custom product boxes with real-time capacity tracking and seamless cart integration.

## Features

✅ **Custom Box Builder Interface** - Beautiful, responsive product grid with category filters and search
✅ **Variable Product Support** - Full support for both simple and variable products with modal variation selector
✅ **Real-Time Capacity Tracking** - Track box capacity by items or weight with visual progress bar
✅ **AJAX-Powered Interactions** - Smooth add/remove products without page reloads
✅ **WooCommerce Integration** - Seamlessly adds custom boxes to cart with all variations preserved
✅ **Admin Settings Panel** - Easy configuration of box settings and capacity rules
✅ **Product Meta Fields** - Enable products for box builder with custom weight/size values
✅ **Responsive Design** - Works perfectly on desktop, tablet, and mobile devices

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher

## Installation

### Method 1: Upload Plugin Files

1. Download the `build-your-box` folder
2. Upload it to your WordPress installation at `/wp-content/plugins/`
3. Go to WordPress Admin → Plugins
4. Find "Build Your Box for WooCommerce" and click "Activate"

### Method 2: Install via WordPress Admin

1. Zip the `build-your-box` folder
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" then "Activate Plugin"

## Quick Start Guide

### 1. Configure Plugin Settings

After activation:

1. Go to **WooCommerce → Build Your Box** in WordPress admin
2. Configure settings:
   - **Box Title**: The heading displayed on box builder page
   - **Box Description**: Description text for customers
   - **Maximum Capacity**: Maximum items or weight allowed
   - **Capacity Type**: Choose between "Number of Items" or "Weight (kg)"
   - **Show Categories**: Enable/disable category filters
   - **Show Filters**: Enable/disable search and sort options

### 2. Enable Products for Box Builder

For each product you want to include:

1. Go to **Products → Edit Product**
2. Find the **"Build Your Box"** tab in Product Data section
3. Check **"Enable for Box Builder"**
4. Set **"Box Weight/Size"** (e.g., 1.5 for 1.5kg)
5. Select a **"Box Category"** for filtering (optional)
6. Update/Publish the product

### 3. Create a Box Builder Page

1. Create a new Page (Pages → Add New)
2. Add the shortcode: `[build_your_box]`
3. Publish the page
4. Customers can now build custom boxes!

### Optional Shortcode Parameters

Customize the box builder with these parameters:

```text
[build_your_box max_capacity="15" capacity_type="weight" show_categories="yes"]
```

**Available Parameters:**

- `max_capacity` - Override default maximum capacity (default: from settings)
- `capacity_type` - "items" or "weight" (default: from settings)
- `show_categories` - "yes" or "no" (default: from settings)

## How It Works

### For Customers

1. **Browse Products** - View all box-eligible products with filters and search
2. **Select Variations** - For variable products, click "Select Options" to choose size, color, etc.
3. **Add to Box** - Click "+ Add to Box" (simple products) or choose from variation modal
4. **Monitor Capacity** - Real-time progress bar shows capacity usage
5. **Review Box** - Sidebar shows selected products with variations, quantities and total price
6. **Add to Cart** - Click "Add Box to Cart" to add complete box to WooCommerce cart
7. **Checkout** - Complete purchase through normal WooCommerce checkout (all variations preserved)

### For Store Owners

1. **Product Management** - Enable/disable products for box builder individually
2. **Capacity Control** - Set limits by item count or total weight
3. **Custom Pricing** - Each product maintains its own price
4. **Order Tracking** - Custom box items appear in order details
5. **Flexible Setup** - Multiple box builder pages with different settings

## File Structure

```bash
build-your-box/
├── build-your-box.php              # Main plugin file
├── README.md                        # Documentation
├── admin/
│   └── class-byb-admin-settings.php # Admin settings panel
├── includes/
│   ├── class-byb-product-meta.php   # Product meta boxes
│   ├── class-byb-ajax-handler.php   # AJAX request handlers
│   ├── class-byb-cart-handler.php   # WooCommerce cart integration
│   └── class-byb-shortcode.php      # Shortcode rendering
├── templates/
│   └── box-builder.php              # Frontend box builder template
└── assets/
    ├── css/
    │   ├── byb-styles.css           # Frontend styles
    │   └── byb-admin-styles.css     # Admin styles
    └── js/
        └── byb-scripts.js           # Frontend JavaScript
```

## Technical Details

### Session Management

The plugin uses PHP sessions to temporarily store box contents before adding to cart. This allows customers to build their box across multiple page views.

### AJAX Endpoints

- `byb_get_products` - Fetch filtered products
- `byb_add_to_box` - Add product to session box
- `byb_remove_from_box` - Remove product from box
- `byb_get_box_contents` - Get current box items
- `byb_add_box_to_cart` - Transfer box to WooCommerce cart

### Cart Integration

Custom boxes are added to cart with special meta data that preserves:

- Individual product details
- Quantities of each item
- Total calculated price
- Box composition for order records

### Capacity Calculation

**By Items**: Counts total number of products added
**By Weight**: Sums product weight × quantity for each item

## Customization

### Styling

Override plugin styles by adding custom CSS to your theme:

```css
/* Change primary button color */
.byb-btn-primary {
    background: #your-color !important;
}

/* Modify product card hover effect */
.byb-product-card:hover {
    transform: translateY(-8px) !important;
}
```

### Hooks & Filters

The plugin provides WordPress hooks for developers:

```php
// Modify max capacity dynamically
add_filter('byb_max_capacity', function($capacity) {
    return 20; // Override to 20
});

// Modify products query
add_filter('byb_products_query_args', function($args) {
    // Customize WP_Query arguments
    return $args;
});
```

## Troubleshooting

### Products Don't Appear in Box Builder

- Ensure products are published
- Check "Enable for Box Builder" is checked in product settings
- Verify WooCommerce is active

### Box Won't Add to Cart

- Check browser console for JavaScript errors
- Verify AJAX nonce is valid (try clearing browser cache)
- Ensure cart page exists and is accessible

### Capacity Not Tracking Correctly

- Verify product weights are set properly
- Check capacity type matches your settings (items vs weight)
- Ensure decimal values use correct format (e.g., 1.5 not 1,5)

### Session Issues

- Some server configurations may have session issues
- Check PHP sessions are enabled on your server
- Contact your hosting provider if sessions aren't working

## Browser Support

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Support & Development

### Version History

**1.0.0** (Current)

- Initial release
- Box builder with capacity tracking
- WooCommerce cart integration
- Admin settings panel
- Responsive design

### Future Enhancements

Planned features for future versions:

- Tiered pricing based on box size
- Multiple box templates
- Save and share box configurations
- Subscription integration
- Product recommendations

## License

GPL v2 or later - <https://www.gnu.org/licenses/gpl-2.0.html>

## Credits

Developed to replicate the functionality of OurCow.com.au's "Build Your Box" feature for WooCommerce stores.

---

## Need Help?

1. Check this README for common issues
2. Review WordPress debug log for errors
3. Ensure all requirements are met
4. Test with default WordPress theme to rule out theme conflicts

### Enjoy building custom boxes for your customers! 📦
