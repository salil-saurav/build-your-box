<?php

if (!defined('ABSPATH')) {
    exit;
}

class BYB_Ajax_Handler
{

    public function __construct()
    {
        add_action('wp_ajax_byb_get_products', array($this, 'get_products'));
        add_action('wp_ajax_nopriv_byb_get_products', array($this, 'get_products'));

        add_action('wp_ajax_byb_add_to_box', array($this, 'add_to_box'));
        add_action('wp_ajax_nopriv_byb_add_to_box', array($this, 'add_to_box'));

        add_action('wp_ajax_byb_remove_from_box', array($this, 'remove_from_box'));
        add_action('wp_ajax_nopriv_byb_remove_from_box', array($this, 'remove_from_box'));

        add_action('wp_ajax_byb_get_box_contents', array($this, 'get_box_contents'));
        add_action('wp_ajax_nopriv_byb_get_box_contents', array($this, 'get_box_contents'));
    }

    public function get_products()
    {
        check_ajax_referer('byb_nonce', 'nonce');

        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $search   = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $sort     = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'title';
        $page     = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 12;

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_stock_status',
                    'value'   => 'instock',
                    'compare' => '='
                ),
                // Optional: include your custom filter too
                // array(
                //     'key'     => '_byb_enabled',
                //     'value'   => 'yes',
                //     'compare' => '='
                // )
            )
        );


        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category
                )
            );
        }

        if (!empty($search)) {
            $args['s'] = $search;
        }

        switch ($sort) {
            case 'price_asc':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;
            case 'price_desc':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'title':
            default:
                $args['orderby'] = 'title';
                $args['order']   = 'ASC';
                break;
        }

        $query = new WP_Query($args);
        $products = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);

                if (!$product) {
                    continue;
                }

                $image_id = $product->get_image_id();
                $image_url = wp_get_attachment_image_url($image_id, 'medium');

                $product_data = array(
                    'id'         => $product_id,
                    'title'      => get_the_title(),
                    'price'      => $product->get_price(),
                    'price_html' => $product->get_price_html(),
                    'image'      => $image_url ? $image_url : wc_placeholder_img_src(),
                    'weight'     => get_post_meta($product_id, '_byb_weight', true) ?: 1,
                    'categories' => wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names')),
                    'permalink'  => get_permalink($product_id),
                    'type'       => $product->get_type(),
                    'variations' => array(),
                );

                if ($product->is_type('variable')) {
                    $available_variations = $product->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $variation_obj = wc_get_product($variation['variation_id']);
                        if ($variation_obj && $variation_obj->is_in_stock()) {
                            $attr_descriptions = array();
                            foreach ($variation['attributes'] as $taxonomy => $value) {
                                $label = wc_attribute_label(str_replace('attribute_', '', $taxonomy));
                                $attr_descriptions[] = $label . ': ' . ucfirst($value);
                            }

                            $product_data['variations'][] = array(
                                'variation_id' => $variation['variation_id'],
                                'attributes'   => $variation['attributes'],
                                'price'        => $variation_obj->get_price(),
                                'price_html'   => $variation_obj->get_price_html(),
                                'image'        => $variation['image']['url'] ?? $image_url,
                                'description'  => !empty($attr_descriptions) ? implode(', ', $attr_descriptions) : 'Option ' . ($variation['variation_id']),
                                'in_stock'     => $variation['is_in_stock'],
                            );
                        }
                    }
                }

                $products[] = $product_data;
            }
            wp_reset_postdata();
        }

        $total_products = $query->found_posts;
        $total_pages = $query->max_num_pages;

        wp_send_json_success(array(
            'products'       => $products,
            'pagination'     => array(
                'current_page'   => $page,
                'total_pages'    => $total_pages,
                'total_products' => $total_products,
                'per_page'       => $per_page,
                'has_prev'       => $page > 1,
                'has_next'       => $page < $total_pages,
            )
        ));
    }

    public function add_to_box()
    {
        check_ajax_referer('byb_nonce', 'nonce');

        $product_id   = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
        $quantity     = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        if (!$product_id) {
            wp_send_json_error(array('message' => __('Invalid product', 'build-your-box')));
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['byb_box'])) {
            $_SESSION['byb_box'] = array();
        }

        $item_key = $variation_id ? $variation_id : $product_id;

        if (isset($_SESSION['byb_box'][$item_key])) {
            $_SESSION['byb_box'][$item_key]['quantity'] += $quantity;
        } else {
            $_SESSION['byb_box'][$item_key] = array(
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'quantity'     => $quantity
            );
        }

        $total_items = 0;
        foreach ($_SESSION['byb_box'] as $item) {
            $total_items += $item['quantity'];
        }

        wp_send_json_success(array(
            'message'   => __('Product added to box', 'build-your-box'),
            'box_count' => $total_items
        ));
    }

    public function remove_from_box()
    {
        check_ajax_referer('byb_nonce', 'nonce');

        $item_key = isset($_POST['item_key']) ? intval($_POST['item_key']) : 0;

        if (!$item_key) {
            wp_send_json_error(array('message' => __('Invalid item', 'build-your-box')));
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['byb_box'][$item_key])) {
            unset($_SESSION['byb_box'][$item_key]);
        }

        $total_items = 0;
        if (isset($_SESSION['byb_box'])) {
            foreach ($_SESSION['byb_box'] as $item) {
                $total_items += $item['quantity'];
            }
        }

        wp_send_json_success(array(
            'message'   => __('Product removed from box', 'build-your-box'),
            'box_count' => $total_items
        ));
    }

    public function get_box_contents()
    {
        check_ajax_referer('byb_nonce', 'nonce');

        if (!isset($_SESSION)) {
            session_start();
        }

        $box_items = isset($_SESSION['byb_box']) ? $_SESSION['byb_box'] : array();
        $items          = array();
        $total_price    = 0;
        $total_weight   = 0;
        $total_quantity = 0;

        foreach ($box_items as $item_key => $item_data) {
            $product_id   = $item_data['product_id'];
            $variation_id = $item_data['variation_id'];
            $quantity     = $item_data['quantity'];

            $product = $variation_id ? wc_get_product($variation_id) : wc_get_product($product_id);
            if (!$product) {
                continue;
            }

            $parent_product = $variation_id ? wc_get_product($product_id) : $product;
            $weight         = get_post_meta($product_id, '_byb_weight', true) ?: 1;
            $item_total     = $product->get_price() * $quantity;

            $title = $product->get_name();
            if ($variation_id && $product->is_type('variation')) {
                $attributes = $product->get_variation_attributes();
                if (!empty($attributes)) {
                    $attr_text = array();
                    foreach ($attributes as $attr_name => $attr_value) {
                        $attr_text[] = ucfirst(str_replace('attribute_', '', $attr_name)) . ': ' . $attr_value;
                    }
                    $title = $parent_product->get_name() . ' (' . implode(', ', $attr_text) . ')';
                }
            }

            $items[] = array(
                'id'           => $item_key,
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'title'        => $title,
                'quantity'     => $quantity,
                'price'        => $product->get_price(),
                'price_html'   => $product->get_price_html(),
                'weight'       => $weight,
                'total'        => $item_total,
                'total_html'   => wc_price($item_total),
                'image'        => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            );

            $total_price += $item_total;
            $total_weight += $weight * $quantity;
            $total_quantity += $quantity;
        }

        wp_send_json_success(array(
            'items'            => $items,
            'total_price'      => $total_price,
            'total_price_html' => wc_price($total_price),
            'total_weight'     => $total_weight,
            'item_count'       => $total_quantity
        ));
    }
}
