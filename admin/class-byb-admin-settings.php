<?php

if (!defined('ABSPATH')) {
    exit;
}

class BYB_Admin_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_menu_page() {
        add_submenu_page(
            'woocommerce',
            __('Build Your Box Settings', 'build-your-box'),
            __('Build Your Box', 'build-your-box'),
            'manage_woocommerce',
            'build-your-box-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('byb_settings', 'byb_max_capacity');
        register_setting('byb_settings', 'byb_min_capacity');
        register_setting('byb_settings', 'byb_capacity_type');
        register_setting('byb_settings', 'byb_show_categories');
        register_setting('byb_settings', 'byb_show_filters');
        register_setting('byb_settings', 'byb_box_title');
        register_setting('byb_settings', 'byb_box_description');

        add_settings_section(
            'byb_general_section',
            __('General Settings', 'build-your-box'),
            array($this, 'general_section_callback'),
            'build-your-box-settings'
        );

        add_settings_field(
            'byb_box_title',
            __('Box Title', 'build-your-box'),
            array($this, 'text_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_box_title', 'default' => 'Build Your Box')
        );

        add_settings_field(
            'byb_box_description',
            __('Box Description', 'build-your-box'),
            array($this, 'textarea_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_box_description', 'default' => 'Create your custom box by selecting products below.')
        );

        add_settings_field(
            'byb_max_capacity',
            __('Maximum Capacity', 'build-your-box'),
            array($this, 'number_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_max_capacity', 'default' => 10)
        );

        add_settings_field(
            'byb_min_capacity',
            __('Minimum Capacity', 'build-your-box'),
            array($this, 'number_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_min_capacity', 'default' => 0)
        );

        add_settings_field(
            'byb_capacity_type',
            __('Capacity Type', 'build-your-box'),
            array($this, 'select_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array(
                'name'    => 'byb_capacity_type',
                'options' => array(
                    'items'  => __('Number of Items', 'build-your-box'),
                    'weight' => __('Weight (kg)', 'build-your-box'),
                )
            )
        );

        add_settings_field(
            'byb_show_categories',
            __('Show Categories', 'build-your-box'),
            array($this, 'checkbox_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_show_categories')
        );
        add_settings_field(
            'byb_show_filters',
            __('Show Filters', 'build-your-box'),
            array($this, 'checkbox_field_callback'),
            'build-your-box-settings',
            'byb_general_section',
            array('name' => 'byb_show_filters')
        );
    }

    public function general_section_callback() {
        echo '<p>' . __('Configure the settings for the Build Your Box functionality.', 'build-your-box') . '</p>';
    }

    public function text_field_callback($args) {
        $value = get_option($args['name'], $args['default'] ?? '');
        echo '<input type="text" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function textarea_field_callback($args) {
        $value = get_option($args['name'], $args['default'] ?? '');
        echo '<textarea name="' . esc_attr($args['name']) . '" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    public function number_field_callback($args) {
        $value = get_option($args['name'], $args['default'] ?? 0);
        echo '<input type="number" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" min="0" step="0.01">';
    }

    public function select_field_callback($args) {
        $value = get_option($args['name'], '');
        echo '<select name="' . esc_attr($args['name']) . '">';
        foreach ($args['options'] as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function checkbox_field_callback($args) {
        $value = get_option($args['name'], 'yes');
        echo '<label><input type="checkbox" name="' . esc_attr($args['name']) . '" value="yes" ' . checked($value, 'yes', false) . '> ' . __('Enable', 'build-your-box') . '</label>';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="byb-settings-notice">
                <h3><?php _e('How to Use Build Your Box', 'build-your-box'); ?></h3>
                <ol>
                    <li><?php _e('Add the shortcode <code>[build_your_box]</code> to any page or post', 'build-your-box'); ?></li>
                    <li><?php _e('Edit products and enable "Build Your Box" in the product settings', 'build-your-box'); ?></li>
                    <li><?php _e('Set product weight/size for capacity calculation', 'build-your-box'); ?></li>
                    <li><?php _e('Customers can now build custom boxes on your site!', 'build-your-box'); ?></li>
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
