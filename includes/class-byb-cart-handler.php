<?php

if (!defined('ABSPATH')) {
    exit;
}

class BYB_Cart_Handler {

    public function __construct() {
        add_action('wp_ajax_byb_add_box_to_cart', array($this, 'add_box_to_cart'));
        add_action('wp_ajax_nopriv_byb_add_box_to_cart', array($this, 'add_box_to_cart'));
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'display_cart_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        add_action('woocommerce_before_calculate_totals', array($this, 'update_cart_item_price'), 10, 1);
    }

    public function add_box_to_cart() {
        check_ajax_referer('byb_nonce', 'nonce');

        if (!isset($_SESSION)) {
            session_start();
        }

        $box_items = isset($_SESSION['byb_box']) ? $_SESSION['byb_box'] : array();

        if (empty($box_items)) {
            wp_send_json_error(array('message' => __('Your box is empty', 'build-your-box')));
        }

        $box_data = array(
            'byb_custom_box'  => true,
            'byb_box_items'   => $box_items,
            'byb_box_created' => current_time('mysql')
        );

        $first_item         = reset($box_items);
        $first_product_id   = $first_item['product_id'];
        $first_variation_id = $first_item['variation_id'];

        $product = $first_variation_id ? wc_get_product($first_variation_id) : wc_get_product($first_product_id);

        if (!$product) {
            wp_send_json_error(array('message' => __('Invalid product in box', 'build-your-box')));
        }

        if ($first_variation_id && $product->is_type('variation')) {
            $cart_item_key = WC()->cart->add_to_cart($first_product_id, 1, $first_variation_id, $product->get_variation_attributes(), $box_data);
        } else {
            $cart_item_key = WC()->cart->add_to_cart($first_product_id, 1, 0, array(), $box_data);
        }

        if ($cart_item_key) {
            $_SESSION['byb_box'] = array();

            wp_send_json_success(array(
                'message'  => __('Box added to cart successfully!', 'build-your-box'),
                'cart_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to add box to cart', 'build-your-box')));
        }
    }

    public function add_cart_item_data($cart_item_data, $product_id) {
        if (isset($cart_item_data['byb_custom_box'])) {
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }
        return $cart_item_data;
    }

    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['byb_custom_box']) && isset($cart_item['byb_box_items'])) {
            $item_data[] = array(
                'key'   => __('Custom Box', 'build-your-box'),
                'value' => __('Build Your Box', 'build-your-box')
            );

            foreach ($cart_item['byb_box_items'] as $item_key => $item) {
                $product_id   = $item['product_id'];
                $variation_id = $item['variation_id'];
                $quantity     = $item['quantity'];

                $product = $variation_id ? wc_get_product($variation_id) : wc_get_product($product_id);
                if ($product) {
                    $name = $product->get_name();
                    if ($variation_id && $product->is_type('variation')) {
                        $parent = wc_get_product($product_id);
                        $attributes = $product->get_variation_attributes();
                        if (!empty($attributes)) {
                            $attr_text = array();
                            foreach ($attributes as $attr_name => $attr_value) {
                                $attr_text[] = ucfirst(str_replace('attribute_', '', $attr_name)) . ': ' . $attr_value;
                            }
                            $name = $parent->get_name() . ' (' . implode(', ', $attr_text) . ')';
                        }
                    }
                    $item_data[] = array(
                        'key'   => $name,
                        'value' => sprintf(__('Qty: %d', 'build-your-box'), $quantity)
                    );
                }
            }
        }
        return $item_data;
    }

    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['byb_custom_box']) && isset($values['byb_box_items'])) {
            $item->add_meta_data(__('Custom Box', 'build-your-box'), __('Build Your Box', 'build-your-box'));

            foreach ($values['byb_box_items'] as $item_key => $box_item) {
                $product_id   = $box_item['product_id'];
                $variation_id = $box_item['variation_id'];
                $quantity     = $box_item['quantity'];

                $product = $variation_id ? wc_get_product($variation_id) : wc_get_product($product_id);
                if ($product) {
                    $name = $product->get_name();
                    if ($variation_id && $product->is_type('variation')) {
                        $parent = wc_get_product($product_id);
                        $attributes = $product->get_variation_attributes();
                        if (!empty($attributes)) {
                            $attr_text = array();
                            foreach ($attributes as $attr_name => $attr_value) {
                                $attr_text[] = ucfirst(str_replace('attribute_', '', $attr_name)) . ': ' . $attr_value;
                            }
                            $name = $parent->get_name() . ' (' . implode(', ', $attr_text) . ')';
                        }
                    }
                    $item->add_meta_data(
                        $name,
                        sprintf(__('Qty: %d', 'build-your-box'), $quantity)
                    );
                }
            }
        }
    }

    public function update_cart_item_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['byb_custom_box']) && isset($cart_item['byb_box_items'])) {
                $total_price = 0;

                foreach ($cart_item['byb_box_items'] as $item_key => $item) {
                    $product_id   = $item['product_id'];
                    $variation_id = $item['variation_id'];
                    $quantity     = $item['quantity'];

                    $product = $variation_id ? wc_get_product($variation_id) : wc_get_product($product_id);
                    if ($product) {
                        $total_price += floatval($product->get_price()) * intval($quantity);
                    }
                }

                $cart_item['data']->set_price($total_price);
            }
        }
    }
}
