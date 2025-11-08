<?php
if (!defined('ABSPATH')) {
   exit;
}

class BYB_Settings_Discount
{
   public static function register()
   {

      register_setting('byb_settings', 'byb_discount_rules');

      add_settings_section(
         'byb_discount_section',
         __('Discount Rule', 'build-your-box'),
         [__CLASS__, 'section_description'],
         'build-your-box-settings'
      );

      // Add discount-related fields here
      // For example:
      add_settings_field(
         'byb_discount_rules',
         __('Discount Rules', 'build-your-box'),
         [__CLASS__, 'discount_rules_callback'],
         'build-your-box-settings',
         'byb_discount_section',
         ['name' => 'byb_discount_rules']
      );
   }

   public static function section_description()
   { ?>
      <div class="byb-settings-notice">
         <h3><?php _e('How to Configure Discount Rules', 'build-your-box'); ?></h3>
         <p><?php _e('Define quantity-based discount rules for your Build Your Box offers. Each rule specifies the minimum number of products required in the box and the discount percentage that should be applied.', 'build-your-box'); ?></p>

         <h4><?php _e('Format:', 'build-your-box'); ?></h4>
         <p><code>[minimum_product_count | discount_percentage]</code></p>

         <h4><?php _e('Examples:', 'build-your-box'); ?></h4>
         <ul style="list-style: disc; margin-left: 20px;">
            <li><code>6 | 5</code> — <?php _e('Apply a 5% discount when the customer selects at least 6 products.', 'build-your-box'); ?></li>
            <li><code>10 | 10</code> — <?php _e('Apply a 10% discount when the customer selects 10 or more products.', 'build-your-box'); ?></li>
         </ul>

         <p><strong><?php _e('Tips:', 'build-your-box'); ?></strong></p>
         <ul style="list-style: disc; margin-left: 20px;">
            <li><?php _e('Add one rule per line.', 'build-your-box'); ?></li>
            <li><?php _e('Use the pipe symbol (|) to separate quantity and discount values.', 'build-your-box'); ?></li>
            <li><?php _e('Discounts are automatically applied to the total box price based on matching rules.', 'build-your-box'); ?></li>
         </ul>
      </div>
   <?php }

   public static function discount_rules_callback($args)
   {
      $value = get_option($args['name'], '');
   ?>
      <textarea name="<?= esc_attr($args['name']) ?>" rows="8"> <?= esc_html($value) ?> </textarea>
<?php }
}
