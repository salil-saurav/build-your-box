<?php

if (!defined('ABSPATH')) {
    exit;
}

class BYB_Product_Meta {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('woocommerce_process_product_meta', array($this, 'save_meta_box'));
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'add_product_tab_content'));
    }

    public function add_product_tab($tabs) {
        $tabs['byb'] = array(
            'label'  => __('Build Your Box', 'build-your-box'),
            'target' => 'byb_product_data',
            'class'  => array('show_if_simple', 'show_if_variable'),
        );
        return $tabs;
    }

    public function add_product_tab_content() {
        global $post;
        ?>
        <div id="byb_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_checkbox(array(
                    'id'          => '_byb_enabled',
                    'label'       => __('Enable for Box Builder', 'build-your-box'),
                    'description' => __('Check this to make this product available in the Build Your Box interface', 'build-your-box'),
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_byb_weight',
                    'label'       => __('Box Weight/Size', 'build-your-box'),
                    'description' => __('Enter the weight or size value for capacity calculation (e.g., 1.5 for 1.5kg)', 'build-your-box'),
                    'desc_tip'    => true,
                    'type'        => 'number',
                    'custom_attributes' => array(
                        'step'          => '0.01',
                        'min'           => '0',
                    ),
                ));

                woocommerce_wp_select(array(
                    'id'          => '_byb_category',
                    'label'       => __('Box Category', 'build-your-box'),
                    'options'     => $this->get_box_categories(),
                    'description' => __('Select a category for filtering in the box builder', 'build-your-box'),
                    'desc_tip'    => true,
                ));
                ?>
            </div>
        </div>
        <?php
    }

    private function get_box_categories() {
        $categories = array('' => __('Select a category', 'build-your-box'));

        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ));

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[$term->term_id] = $term->name;
            }
        }

        return $categories;
    }

    public function add_meta_box() {
        add_meta_box(
            'byb_product_meta',
            __('Build Your Box Settings', 'build-your-box'),
            array($this, 'render_meta_box'),
            'product',
            'side',
            'default'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('byb_save_meta', 'byb_meta_nonce');

        $enabled = get_post_meta($post->ID, '_byb_enabled', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="_byb_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                <?php _e('Available in Box Builder', 'build-your-box'); ?>
            </label>
        </p>
        <?php
    }

    public function save_meta_box($post_id) {
        if (!isset($_POST['byb_meta_nonce']) || !wp_verify_nonce($_POST['byb_meta_nonce'], 'byb_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $enabled = isset($_POST['_byb_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_byb_enabled', $enabled);

        if (isset($_POST['_byb_weight'])) {
            update_post_meta($post_id, '_byb_weight', sanitize_text_field($_POST['_byb_weight']));
        }

        if (isset($_POST['_byb_category'])) {
            update_post_meta($post_id, '_byb_category', sanitize_text_field($_POST['_byb_category']));
        }
    }
}
