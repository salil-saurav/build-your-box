#!/bin/bash

set -e

export PATH="$HOME/bin:$PATH"

WP_DIR="wordpress"
WP_URL="http://0.0.0.0:5000"
WP_TITLE="Build Your Box Demo"
WP_ADMIN="admin"
WP_PASS="admin123"
WP_EMAIL="admin@example.com"

echo "=== WordPress Setup for Build Your Box Plugin ==="

if [ ! -d "$WP_DIR" ]; then
    echo "Downloading WordPress core..."
    wp core download --path=$WP_DIR --version=latest
else
    echo "WordPress already downloaded."
fi

cd $WP_DIR

if [ ! -f "wp-config.php" ]; then
    echo "Installing SQLite integration plugin..."
    if [ ! -d "wp-content/plugins/sqlite-database-integration" ]; then
        wp plugin install sqlite-database-integration --activate --allow-root
    fi
    
    echo "Creating wp-config.php..."
    wp config create \
        --dbname=wordpress \
        --dbuser=root \
        --dbpass='' \
        --dbhost=localhost \
        --skip-check \
        --allow-root
    
    echo "Installing WordPress..."
    wp core install \
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
if ! wp plugin is-installed woocommerce --allow-root; then
    wp plugin install woocommerce --activate --allow-root
else
    wp plugin activate woocommerce --allow-root
fi

echo "Activating Build Your Box plugin..."
wp plugin activate build-your-box --allow-root

echo "Configuring WooCommerce..."
wp option update woocommerce_store_address "123 Demo Street" --allow-root
wp option update woocommerce_store_city "Demo City" --allow-root
wp option update woocommerce_default_country "US:CA" --allow-root
wp option update woocommerce_store_postcode "12345" --allow-root
wp option update woocommerce_currency "USD" --allow-root
wp option update woocommerce_product_type "simple" --allow-root

echo "Configuring Build Your Box settings..."
wp option update byb_max_capacity "10" --allow-root
wp option update byb_capacity_type "items" --allow-root
wp option update byb_show_categories "yes" --allow-root
wp option update byb_show_filters "yes" --allow-root

echo "Creating demo products..."
PRODUCT_IDS=$(wp post list --post_type=product --format=ids --allow-root)

if [ -z "$PRODUCT_IDS" ]; then
    echo "Creating sample products..."
    
    for i in {1..8}; do
        PRODUCT_ID=$(wp post create --post_type=product \
            --post_title="Demo Product $i" \
            --post_content="This is a demo product for Build Your Box." \
            --post_status=publish \
            --porcelain \
            --allow-root)
        
        wp post meta update $PRODUCT_ID _regular_price $(( 10 + $i * 2 )) --allow-root
        wp post meta update $PRODUCT_ID _price $(( 10 + $i * 2 )) --allow-root
        wp post meta update $PRODUCT_ID _stock_status "instock" --allow-root
        wp post meta update $PRODUCT_ID _manage_stock "no" --allow-root
        
        wp post meta update $PRODUCT_ID _byb_enabled "yes" --allow-root
        wp post meta update $PRODUCT_ID _byb_weight "1" --allow-root
        
        echo "Created product: Demo Product $i (ID: $PRODUCT_ID)"
    done
else
    echo "Demo products already exist."
fi

echo "Creating Box Builder page..."
if ! wp post list --post_type=page --name=box-builder --allow-root | grep -q "box-builder"; then
    wp post create --post_type=page \
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
