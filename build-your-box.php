<?php
/**
 * Plugin Name: Build Your Box for WooCommerce
 * Description: Allows customers to build custom product boxes with capacity tracking, similar to OurCow's Build Your Box functionality
 * Version: 1.0.0
 * Author: Wytlabs
 * Text Domain: build-your-box
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('BYB_VERSION')) {
    define('BYB_VERSION', '1.0.0');
}

if (!defined('BYB_PLUGIN_FILE')) {
    define('BYB_PLUGIN_FILE', __FILE__);
}

if (!defined('BYB_PLUGIN_DIR')) {
    define('BYB_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('BYB_PLUGIN_URL')) {
    define('BYB_PLUGIN_URL', plugin_dir_url(__FILE__));
}

class Build_Your_Box {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once BYB_PLUGIN_DIR . 'includes/class-byb-product-meta.php';
        require_once BYB_PLUGIN_DIR . 'includes/class-byb-ajax-handler.php';
        require_once BYB_PLUGIN_DIR . 'includes/class-byb-cart-handler.php';
        require_once BYB_PLUGIN_DIR . 'includes/class-byb-shortcode.php';
        require_once BYB_PLUGIN_DIR . 'admin/class-byb-admin-settings.php';
    }

    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        new BYB_Product_Meta();
        new BYB_Ajax_Handler();
        new BYB_Cart_Handler();
        new BYB_Shortcode();

        if (is_admin()) {
            new BYB_Admin_Settings();
        }
    }

    public function enqueue_scripts() {

        wp_enqueue_style('byb-styles', BYB_PLUGIN_URL . 'assets/css/byb-styles.css', array(), BYB_VERSION);
        wp_enqueue_script('byb-scripts', BYB_PLUGIN_URL . 'assets/js/byb-scripts.js', array('jquery'), BYB_VERSION, true);

        wp_localize_script('byb-scripts', 'byb_ajax', array(
            'ajax_url'           => admin_url('admin-ajax.php'),
            'nonce'              => wp_create_nonce('byb_nonce'),
            'currency_symbol'    => get_woocommerce_currency_symbol(),
            'currency_position'  => get_option('woocommerce_currency_pos'),
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals'           => wc_get_price_decimals()
        ));
    }

    public function admin_enqueue_scripts($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            wp_enqueue_style('byb-admin-styles', BYB_PLUGIN_URL . 'assets/css/byb-admin-styles.css', array(), BYB_VERSION);
        }
    }

    public function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('This plugin requires WooCommerce to be installed and active.', 'build-your-box'));
        }

        $default_settings = array(
            'byb_max_capacity'    => 10,
            'byb_capacity_type'   => 'items',
            'byb_min_capacity'    => 0,
            'byb_show_categories' => 'yes',
            'byb_show_filters'    => 'yes'
        );

        foreach ($default_settings as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }

        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('Build Your Box requires WooCommerce to be installed and activated.', 'build-your-box'); ?></p>
        </div>
        <?php
    }
}

function BYB() {
    return Build_Your_Box::instance();
}

BYB();
