<?php

if (!defined('ABSPATH')) {
    exit;
}

// Require modular settings files
require_once BYB_PLUGIN_DIR . 'admin/settings/class-byb-settings-general.php';
require_once BYB_PLUGIN_DIR . 'admin/settings/class-byb-settings-discount.php';

class BYB_Admin_Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    public function enqueue_admin_styles($hook)
    {
        if ($hook !== 'woocommerce_page_build-your-box-settings') {
            return;
        }

        wp_enqueue_script('byb-admin-scripts', BYB_PLUGIN_URL . 'admin/assets/script.js', [], BYB_VERSION, true);
        wp_enqueue_style('byb-admin-style', BYB_PLUGIN_URL . 'assets/css/byb-admin-styles.css', [], BYB_VERSION);
    }

    public function add_menu_page()
    {
        add_submenu_page(
            'woocommerce',
            __('Build Your Box Settings', 'build-your-box'),
            __('Build Your Box', 'build-your-box'),
            'manage_woocommerce',
            'build-your-box-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        // Register both sections
        BYB_Settings_General::register();
        BYB_Settings_Discount::register();
    }

    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="byb-settings-notice">
                <h3><?php _e('How to Use Build Your Box', 'build-your-box'); ?></h3>
                <ol>
                    <li><?php _e('Add the shortcode <code>[build_your_box]</code> to any page or post.', 'build-your-box'); ?></li>
                    <li><?php _e('Enable "Build Your Box" in product settings.', 'build-your-box'); ?></li>
                    <li><?php _e('Set product weight/size for capacity calculation.', 'build-your-box'); ?></li>
                    <li><?php _e('Customers can build their custom boxes on your site!', 'build-your-box'); ?></li>
                </ol>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('byb_settings');
                do_settings_sections('build-your-box-settings');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }
}
