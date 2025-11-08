<?php

if (!defined('ABSPATH')) {
    exit;
}

class BYB_Shortcode {

    public function __construct() {
        add_shortcode('build_your_box', array($this, 'render_shortcode'));
        add_action('init', array($this, 'start_session'));
    }

    public function start_session() {
        if (!session_id()) {
            session_start();
        }
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'max_capacity'    => get_option('byb_max_capacity', 10),
            'capacity_type'   => get_option('byb_capacity_type', 'items'),
            'show_categories' => get_option('byb_show_categories', 'yes'),
            'show_filters'     => get_option('byb_show_filters', 'yes')
        ), $atts);

        wp_localize_script('byb-scripts', 'bybSettings', array(
            'maxCapacity'  => floatval($atts['max_capacity']),
            'capacityType' => sanitize_text_field($atts['capacity_type'])
        ));

        ob_start();
        include BYB_PLUGIN_DIR . 'templates/box-builder.php';
        return ob_get_clean();
    }
}
