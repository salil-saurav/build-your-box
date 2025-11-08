<?php
if (!defined('ABSPATH')) {
   exit;
}

class BYB_Settings_General
{
   public static function register()
   {
      register_setting('byb_settings', 'byb_max_capacity');
      register_setting('byb_settings', 'byb_min_capacity');
      register_setting('byb_settings', 'byb_capacity_type');
      register_setting('byb_settings', 'byb_show_categories');
      register_setting('byb_settings', 'byb_show_filters');
      register_setting('byb_settings', 'byb_box_title');
      register_setting('byb_settings', 'byb_box_description');
      register_setting('byb_settings', 'byb_selected_categories');

      add_settings_section(
         'byb_general_section',
         __('General Settings', 'build-your-box'),
         [__CLASS__, 'section_description'],
         'build-your-box-settings'
      );

      self::add_fields();
   }

   public static function section_description()
   {
      echo '<p>' . __('Configure the settings for the Build Your Box functionality.', 'build-your-box') . '</p>';
   }

   private static function add_fields()
   {
      add_settings_field(
         'byb_box_title',
         __('Box Title', 'build-your-box'),
         ['BYB_Settings_General', 'text_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_box_title', 'default' => 'Build Your Box']
      );

      add_settings_field(
         'byb_box_description',
         __('Box Description', 'build-your-box'),
         ['BYB_Settings_General', 'textarea_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_box_description', 'default' => 'Create your custom box by selecting products below.']
      );

      add_settings_field(
         'byb_selected_categories',
         __('Product Categories', 'build-your-box'),
         ['BYB_Settings_General', 'category_selector_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_selected_categories']
      );

      add_settings_field(
         'byb_max_capacity',
         __('Maximum Capacity', 'build-your-box'),
         ['BYB_Settings_General', 'number_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_max_capacity', 'default' => 10]
      );

      add_settings_field(
         'byb_min_capacity',
         __('Minimum Capacity', 'build-your-box'),
         ['BYB_Settings_General', 'number_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_min_capacity', 'default' => 0]
      );

      add_settings_field(
         'byb_capacity_type',
         __('Capacity Type', 'build-your-box'),
         ['BYB_Settings_General', 'select_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         [
            'name'    => 'byb_capacity_type',
            'options' => [
               'items'  => __('Number of Items', 'build-your-box'),
               'weight' => __('Weight (kg)', 'build-your-box'),
            ]
         ]
      );

      add_settings_field(
         'byb_show_categories',
         __('Show Categories', 'build-your-box'),
         ['BYB_Settings_General', 'checkbox_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_show_categories']
      );

      add_settings_field(
         'byb_show_filters',
         __('Show Filters', 'build-your-box'),
         ['BYB_Settings_General', 'checkbox_field_callback'],
         'build-your-box-settings',
         'byb_general_section',
         ['name' => 'byb_show_filters']
      );
   }

   // Reuse existing callback methods
   public static function text_field_callback($args)
   {
      $value = get_option($args['name'], $args['default'] ?? '');
      echo '<input type="text" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" class="regular-text">';
   }

   public static function textarea_field_callback($args)
   {
      $value = get_option($args['name'], $args['default'] ?? '');
      echo '<textarea name="' . esc_attr($args['name']) . '" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
   }

   public static function number_field_callback($args)
   {
      $value = get_option($args['name'], $args['default'] ?? 0);
      echo '<input type="number" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" min="0" step="0.01">';
   }

   public static function select_field_callback($args)
   {
      $value = get_option($args['name'], '');
      echo '<select name="' . esc_attr($args['name']) . '">';
      foreach ($args['options'] as $key => $label) {
         echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
      }
      echo '</select>';
   }

   public static function checkbox_field_callback($args)
   {
      $value = get_option($args['name'], 'yes');
      echo '<label><input type="checkbox" name="' . esc_attr($args['name']) . '" value="yes" ' . checked($value, 'yes', false) . '> ' . __('Enable', 'build-your-box') . '</label>';
   }

   public static function category_selector_callback($args)
   {
      // Keep your original logic here unchanged
      $selected_categories = get_option($args['name'], []);
      if (!is_array($selected_categories)) {
         $selected_categories = !empty($selected_categories) ? explode(',', $selected_categories) : [];
      }

      $categories = get_terms([
         'taxonomy'   => 'product_cat',
         'hide_empty' => false,
      ]);

      echo '<div class="byb-category-selector">';
      echo '<p class="description">' . __('Select product categories for Build Your Box.', 'build-your-box') . '</p>';
      echo '<div class="byb-category-chips">';
      if (!empty($selected_categories)) {
         foreach ($selected_categories as $cat_id) {
            $term = get_term($cat_id, 'product_cat');
            if ($term && !is_wp_error($term)) {
               echo '<div class="byb-category-chip" data-id="' . esc_attr($cat_id) . '">';
               echo '<span>' . esc_html($term->name) . '</span>';
               echo '<span class="remove">×</span>';
               echo '<input type="hidden" name="byb_selected_categories[]" value="' . esc_attr($cat_id) . '">';
               echo '</div>';
            }
         }
      } else {
         echo '<div class="byb-empty-state">' . __('No categories selected.', 'build-your-box') . '</div>';
      }
      echo '</div>';

      echo '<div class="byb-category-dropdown">';
      echo '<select id="byb_categories_dropdown">';
      echo '<option value="">' . __('-- Select a category --', 'build-your-box') . '</option>';
      foreach ($categories as $cat) {
         echo '<option value="' . esc_attr($cat->term_id) . '" ' . disabled(in_array($cat->term_id, $selected_categories)) . '>' . esc_html($cat->name) . '</option>';
      }
      echo '</select>';
      echo '</div>';
      echo '<input type="hidden" id="byb_selected_categories_hidden" name="' . esc_attr($args['name']) . '" value="' . esc_attr(implode(',', $selected_categories)) . '">';
      echo '</div>';
   }
}
