<?php
if (!defined('ABSPATH')) {
    exit;
}

$max_capacity    = $atts['max_capacity'];
$capacity_type   = $atts['capacity_type'];
$show_categories = $atts['show_categories'] === 'yes';
$show_filters    = $atts['show_filters'] === 'yes';
$box_title       = get_option('byb_box_title', 'Build Your Box');
$box_description = get_option('byb_box_description', 'Create your custom box by selecting products below.');

// $categories = get_terms(array(
//     'taxonomy'   => 'product_cat',
//     'hide_empty' => true,
// ));
$categories = get_option('byb_selected_categories');

?>

<div class="byb-container">
    <div class="byb-header">
        <h2 class="byb-title"><?php echo esc_html($box_title); ?></h2>
        <p class="byb-description"><?php echo esc_html($box_description); ?></p>
    </div>

    <div class="byb-layout">

        <div class="byb-filters">
            <div class="byb-filter-group byb-search-wrap">
                <label for="byb-search">Search products</label>
                <input type="text" id="byb-search" class="byb-search" placeholder="Search products..." style="padding: 0 35px;">
            </div>
            <?php if ($show_categories && !is_wp_error($categories) && !empty($categories)): ?>

                <div class="byb-filter-group">
                    <label><?php _e('Category:', 'build-your-box'); ?></label>
                    <div class="byb-radio-group">
                        <div class="byb-radio-item">
                            <input type="radio" id="byb-category-all" name="byb-category-filter" value="" checked>
                            <label for="byb-category-all"><?php _e('All Categories', 'build-your-box'); ?></label>
                        </div>
                        <?php
                        $category_ids = explode(',', $categories);
                        foreach ($category_ids as $category):
                            $cat_term = get_term_by('id', $category, 'product_cat');
                            if ($cat_term && !is_wp_error($cat_term)):
                        ?>
                                <div class="byb-radio-item">
                                    <input type="radio" id="byb-category-<?php echo esc_attr($category); ?>" name="byb-category-filter" value="<?php echo esc_attr($category); ?>">
                                    <label for="byb-category-<?php echo esc_attr($category); ?>"><?php echo esc_html($cat_term->name); ?></label>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($show_filters): ?>
                <div class="byb-filter-group">
                    <label><?php _e('Sort:', 'build-your-box'); ?></label>
                    <div class="byb-radio-group">
                        <div class="byb-radio-item">
                            <input type="radio" id="sort-title" name="byb-sort" value="title" checked>
                            <label for="sort-title"><?php _e('Name A-Z', 'build-your-box'); ?></label>
                        </div>
                        <div class="byb-radio-item">
                            <input type="radio" id="sort-price-asc" name="byb-sort" value="price_asc">
                            <label for="sort-price-asc"><?php _e('Price Low to High', 'build-your-box'); ?></label>
                        </div>
                        <div class="byb-radio-item">
                            <input type="radio" id="sort-price-desc" name="byb-sort" value="price_desc">
                            <label for="sort-price-desc"><?php _e('Price High to Low', 'build-your-box'); ?></label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>


        <div class="byb-main">
            <div class="byb-products-grid" id="byb-products-grid">
                <div class="byb-loading"><?php _e('Loading products...', 'build-your-box'); ?></div>
            </div>
        </div>

        <div class="byb-sidebar">
            <div class="byb-box-summary" id="byb-box-summary">
                <div class="byb-close-wrap">
                    <button class="byb-close-btn" aria-label="Close" title="Close"></button>
                </div>

                <h3 class="byb-summary-title"><?php _e('Your Box', 'build-your-box'); ?></h3>

                <div class="byb-capacity-meter">
                    <div class="byb-capacity-info">
                        <span class="byb-capacity-label">
                            <?php
                            if ($capacity_type === 'weight') {
                                _e('Weight:', 'build-your-box');
                            } else {
                                _e('Items:', 'build-your-box');
                            }
                            ?>
                        </span>
                        <span class="byb-capacity-value">
                            <span class="byb-current-capacity">0</span> /
                            <span class="byb-max-capacity"><?php echo esc_html($max_capacity); ?></span>
                            <?php echo $capacity_type === 'weight' ? 'kg' : ''; ?>
                        </span>
                    </div>
                    <div class="byb-capacity-bar">
                        <div class="byb-capacity-fill" style="width: 0%;"></div>
                    </div>

                    <div class="discount-pitch">
                        <p>
                            Add <span class="remaining"></span> more item(s)
                            to get up to <span class="discount"></span>% OFF!
                        </p>
                    </div>


                </div>

                <div class="byb-box-items" id="byb-box-items">
                    <p class="byb-empty-message"><?php _e('Your box is empty. Start adding products!', 'build-your-box'); ?></p>
                </div>

                <div class="byb-box-total">
                    <div class="byb-total-row byb-subtotal-row" style="display: none;">
                        <span><?php _e('Subtotal:', 'build-your-box'); ?></span>
                        <span id="byb-subtotal-price" class="byb-subtotal-price">$0.00</span>
                    </div>
                    <div class="byb-total-row byb-discount-row" style="display: none;">
                        <span><?php _e('Discount:', 'build-your-box'); ?></span>
                        <span id="byb-discount-info" class="byb-discount-info">0%</span>
                    </div>
                    <div class="byb-total-row">
                        <span><?php _e('Total:', 'build-your-box'); ?></span>
                        <span id="byb-total-price" class="byb-total-price">$0.00</span>
                    </div>
                </div>

                <button id="byb-add-to-cart" class="byb-btn byb-btn-primary" disabled>
                    <?php _e('Add Box to Cart', 'build-your-box'); ?>
                </button>

                <button id="byb-clear-box" class="byb-btn byb-btn-secondary">
                    <?php _e('Clear Box', 'build-your-box'); ?>
                </button>
            </div>
        </div>

        <div id="byb_slider_toggle">
            <div class="byb-capacity-meter">
                <div class="byb-capacity-info">
                    <span class="byb-capacity-label">
                        <?php
                        if ($capacity_type === 'weight') {
                            _e('Weight:', 'build-your-box');
                        } else {
                            _e('Items:', 'build-your-box');
                        }
                        ?>
                    </span>
                    <span class="byb-capacity-value">
                        <span class="byb-current-capacity">0</span> /
                        <span class="byb-max-capacity"><?php echo esc_html($max_capacity); ?></span>
                        <?php echo $capacity_type === 'weight' ? 'kg' : ''; ?>
                    </span>
                    <div class="byb-box-total">
                        <div class="byb-total-row">
                            <span id="byb-total-price" class="byb-total-price">$0.00</span>
                        </div>
                    </div>

                </div>
                <div class="byb-capacity-bar">
                    <div class="byb-capacity-fill" style="width: 0%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
