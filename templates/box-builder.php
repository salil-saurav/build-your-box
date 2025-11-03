<?php
if (!defined('ABSPATH')) {
    exit;
}

$max_capacity    = $atts['max_capacity'];
$capacity_type   = $atts['capacity_type'];
$show_categories = $atts['show_categories'] === 'yes';
$box_title       = get_option('byb_box_title', 'Build Your Box');
$box_description = get_option('byb_box_description', 'Create your custom box by selecting products below.');

$categories = get_terms(array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
));
?>

<div class="byb-container">
    <div class="byb-header">
        <h2 class="byb-title"><?php echo esc_html($box_title); ?></h2>
        <p class="byb-description"><?php echo esc_html($box_description); ?></p>
    </div>

    <?php if ($show_categories && !is_wp_error($categories) && !empty($categories)): ?>
        <div class="byb-filters">
            <div class="byb-filter-group">
                <label for="byb-category-filter"><?php _e('Category:', 'build-your-box'); ?></label>
                <select id="byb-category-filter" class="byb-select">
                    <option value=""><?php _e('All Categories', 'build-your-box'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->term_id); ?>">
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="byb-filter-group">
                <label for="byb-sort"><?php _e('Sort:', 'build-your-box'); ?></label>
                <select id="byb-sort" class="byb-select">
                    <option value="title"><?php _e('Name A-Z', 'build-your-box'); ?></option>
                    <option value="price_asc"><?php _e('Price Low to High', 'build-your-box'); ?></option>
                    <option value="price_desc"><?php _e('Price High to Low', 'build-your-box'); ?></option>
                </select>
            </div>

            <div class="byb-filter-group byb-search-wrap">
                <input type="text" id="byb-search" class="byb-search" placeholder="<?php _e('Search products...', 'build-your-box'); ?>">
            </div>
        </div>
    <?php endif; ?>

    <div class="byb-layout">
        <div class="byb-main">
            <div class="byb-products-grid" id="byb-products-grid">
                <div class="byb-loading"><?php _e('Loading products...', 'build-your-box'); ?></div>
            </div>
        </div>

        <div class="byb-sidebar">
            <div class="byb-box-summary" id="byb-box-summary">
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
                            <span id="byb-current-capacity">0</span> /
                            <span id="byb-max-capacity"><?php echo esc_html($max_capacity); ?></span>
                            <?php echo $capacity_type === 'weight' ? 'kg' : ''; ?>
                        </span>
                    </div>
                    <div class="byb-capacity-bar">
                        <div class="byb-capacity-fill" id="byb-capacity-fill" style="width: 0%;"></div>
                    </div>
                </div>

                <div class="byb-box-items" id="byb-box-items">
                    <p class="byb-empty-message"><?php _e('Your box is empty. Start adding products!', 'build-your-box'); ?></p>
                </div>

                <div class="byb-box-total">
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
    </div>
</div>
