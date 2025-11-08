#!/bin/bash

set -e

export PATH="$HOME/bin:$PATH"

WP="php -d memory_limit=512M $HOME/bin/wp"

WP_DIR="wordpress"
WP_URL="http://0.0.0.0:5000"
WP_TITLE="Build Your Box Demo"
WP_ADMIN="admin"
WP_PASS="admin123"
WP_EMAIL="admin@example.com"

echo "=== WordPress Setup for Build Your Box Plugin ==="

if [ ! -d "$WP_DIR" ]; then
    echo "Downloading WordPress core..."
    $WP core download --path=$WP_DIR --version=latest
else
    echo "WordPress already downloaded."
fi

cd $WP_DIR

if [ ! -f "wp-config.php" ]; then
    echo "Installing SQLite integration plugin..."
    if [ ! -d "wp-content/plugins/sqlite-database-integration" ]; then
        $WP plugin install sqlite-database-integration --activate --allow-root
    fi
    
    echo "Creating wp-config.php..."
    $WP config create \
        --dbname=wordpress \
        --dbuser=root \
        --dbpass='' \
        --dbhost=localhost \
        --skip-check \
        --allow-root
    
    echo "Installing WordPress..."
    $WP core install \
        --url="$WP_URL" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN" \
        --admin_password="$WP_PASS" \
        --admin_email="$WP_EMAIL" \
        --skip-email \
        --allow-root
else
    echo "WordPress already configured."
fi

echo "Symlinking Build Your Box plugin..."
if [ ! -L "wp-content/plugins/build-your-box" ]; then
    ln -sf "$(pwd)/../" "wp-content/plugins/build-your-box"
fi

echo "Installing WooCommerce..."
if ! $WP plugin is-installed woocommerce --allow-root 2>/dev/null; then
    $WP plugin install woocommerce --activate --allow-root
else
    $WP plugin activate woocommerce --allow-root 2>/dev/null || true
fi

echo "Activating Build Your Box plugin..."
$WP plugin activate build-your-box --allow-root

echo "Configuring WooCommerce..."
$WP option update woocommerce_store_address "123 Demo Street" --allow-root
$WP option update woocommerce_store_city "Demo City" --allow-root
$WP option update woocommerce_default_country "US:CA" --allow-root
$WP option update woocommerce_store_postcode "12345" --allow-root
$WP option update woocommerce_currency "USD" --allow-root
$WP option update woocommerce_product_type "simple" --allow-root

echo "Configuring Build Your Box settings..."
$WP option update byb_max_capacity "10" --allow-root
$WP option update byb_capacity_type "items" --allow-root
$WP option update byb_show_categories "yes" --allow-root
$WP option update byb_show_filters "yes" --allow-root

echo "Creating demo products..."
PRODUCT_IDS=$($WP post list --post_type=product --format=ids --allow-root)

if [ -z "$PRODUCT_IDS" ]; then
    echo "Creating sample products..."
    
    for i in {1..8}; do
        PRODUCT_ID=$($WP post create --post_type=product \
            --post_title="Demo Product $i" \
            --post_content="This is a demo product for Build Your Box." \
            --post_status=publish \
            --porcelain \
            --allow-root)
        
        $WP post meta update $PRODUCT_ID _regular_price $(( 10 + $i * 2 )) --allow-root
        $WP post meta update $PRODUCT_ID _price $(( 10 + $i * 2 )) --allow-root
        $WP post meta update $PRODUCT_ID _stock_status "instock" --allow-root
        $WP post meta update $PRODUCT_ID _manage_stock "no" --allow-root
        
        $WP post meta update $PRODUCT_ID _byb_enabled "yes" --allow-root
        $WP post meta update $PRODUCT_ID _byb_weight "1" --allow-root
        
        echo "Created product: Demo Product $i (ID: $PRODUCT_ID)"
    done
else
    echo "Demo products already exist."
fi

echo "Creating Box Builder page..."
if ! $WP post list --post_type=page --name=box-builder --allow-root | grep -q "box-builder"; then
    $WP post create --post_type=page \
        --post_title="Build Your Box" \
        --post_name="box-builder" \
        --post_content="[build_your_box]" \
        --post_status=publish \
        --allow-root
    echo "Box Builder page created."
else
    echo "Box Builder page already exists."
fi

echo ""
echo "=== Setup Complete! ==="
echo ""
echo "WordPress Admin:"
echo "  URL: $WP_URL/wp-admin"
echo "  Username: $WP_ADMIN"
echo "  Password: $WP_PASS"
echo ""
echo "Box Builder Page:"
echo "  URL: $WP_URL/box-builder"
echo ""
