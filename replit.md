# Build Your Box for WooCommerce - Replit Setup

## Project Overview
This is a WordPress/WooCommerce plugin that allows customers to build custom product boxes with capacity tracking. The plugin has been set up to run in a full WordPress environment using SQLite instead of MySQL.

## Current Status
- ✅ WordPress 6.8.3 installed
- ✅ WooCommerce 10.3.4 installed
- ✅ SQLite database integration configured
- ✅ Build Your Box plugin activated
- ✅ 6 demo products created
- ✅ Box Builder page created with shortcode

## Known Issues
- ⚠️ WooCommerce Action Scheduler has compatibility issues with SQLite (background tasks may fail)
- The main Box Builder functionality should work despite these background errors

## Access Details

### WordPress Admin
- **URL**: Click the Webview tab or visit the preview URL + `/wp-admin`
- **Username**: `admin`
- **Password**: `admin123`

### Box Builder Demo Page
- **URL**: Preview URL + `/box-builder`
- This page uses the `[build_your_box]` shortcode

## Project Structure
```
/
├── wordpress/              # WordPress core installation
│   ├── wp-content/
│   │   ├── plugins/
│   │   │   ├── build-your-box/  # Symlink to plugin root
│   │   │   ├── woocommerce/
│   │   │   └── sqlite-database-integration/
│   │   └── database/        # SQLite database files
│   └── wp-config.php
├── admin/                   # Plugin admin files
├── includes/                # Plugin core classes
├── templates/               # Frontend templates
├── assets/                  # CSS/JS assets
└── build-your-box.php      # Main plugin file
```

## Plugin Features
- Custom box builder interface
- Real-time capacity tracking (by items or weight)
- WooCommerce cart integration
- Variable product support
- Admin settings panel
- Responsive design

## Configuration
The plugin can be configured at: **WooCommerce → Build Your Box** in WordPress admin

Default settings:
- Max Capacity: 10 items
- Capacity Type: Items (not weight)
- Show Categories: Yes
- Show Filters: Yes

## Recent Changes
- 2025-11-08: Initial Replit environment setup with WordPress, WooCommerce, and SQLite
