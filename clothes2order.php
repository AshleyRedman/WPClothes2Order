<?php

/**
 * Clothes 2 Order plugin for WordPress
 *
 * @package   clothes2order
 * @link
 * @author    Reuben Porter <porterdmu@gmail.com> & Ashley Redman <ash.redman@outlook.com>
 * @copyright 2021 AR Development
 * @license   GPL v2 or later
 *
 * Plugin Name:  Clothes 2 Order
 * Description:  Clothes 2 Order custom plugin for WordPress
 * Version:      0.1.0
 * Plugin URI:
 * Author:       Reuben Porter & Ashley Redman
 * Author URI:
 * Text Domain:  clothes-2-order
 * Domain Path:
 * Requires PHP: 7.4
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('ABSPATH') || die();

require_once 'inc/constants.php';

/**
 * Init the plugin only when all plugins are loaded & we can check for WC being available
 *
 */
add_action('plugins_loaded', function () {

    if (class_exists('Woocommerce')) {

        // 1. Create a WC Settings admin area
        add_filter('woocommerce_get_sections_products', 'wcUICreate');
        add_filter('woocommerce_get_settings_products', 'wcUISettings', 10, 2);

        if (get_option('clothes-2-order_api_key') && get_option('clothes-2-order_endpoint') && get_option('clothes-2-order_email')) {

            // 2. Check & create specific taxonomy terms to determine which products to check in a basket
            add_action('init', 'createProductCatTerms');

            // 3. Require ACF if it is not already present
            require_once 'inc/acf/acf.php';

            // 4. Update ACF local json path to be within this plugin
            function my_acf_json_save_point($path)
            {
                $path = plugin_dir_path(__FILE__) . '/acf-json';
                return $path;
            }

            add_filter('acf/settings/save_json', 'my_acf_json_save_point');

            // 5. On payment complete, 'run' the basket & post API calls for each basket item if meeting requirement
            add_action('woocommerce_payment_complete', 'processNewOrder');
            add_action('woocommerce_admin_order_data_after_order_details', 'updateOrderUI', 10, 1);
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>' . _('Please ensure you complete the Clothes2Order required settings <a href="/wp-admin/admin.php?page=wc-settings&tab=products&section=clothes-2-order">here</a>') . '</p></div>';
            }, 10, 2);
        }
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>' . _('Woocommerce is required to use the Clothes2Order Plugin!') . '</p></div>';
        }, 10, 2);
    }
});

/**
 * Create a new section under the WC products
 *
 * @param $sections
 *
 * @return mixed
 */
function wcUICreate($sections)
{
    $sections['clothes-2-order'] = __('Clothes 2 Order', 'clothes-2-order');
    return $sections;
}

/**
 * Create the settings to show under the new C20 WC products section
 *
 * @param $settings
 * @param $current_section
 *
 * @return array
 */
function wcUISettings($settings, $current_section): array
{
    if ($current_section == 'clothes-2-order') {

        $settings_c2o = [];

        $settings_c2o[] = [
            'name' => __('Clothes 2 Order Settings', 'clothes-2-order'),
            'type' => 'title',
            'desc' => __('The following options are used to configure the Clothes 2 Order connection & WordPress settings.', 'clothes-2-order'),
            'id' => 'clothes-2-order'
        ];

        $settings_c2o[] = [
            'name' => __('API Key', 'clothes-2-order'),
            'desc_tip' => __('This is the unique API key provided by Clothes2Order', 'clothes-2-order'),
            'id' => 'clothes-2-order_api_key',
            'type' => 'password',
            'desc' => __('API key provided by Clothes2Order', 'clothes-2-order'),
        ];

        $settings_c2o[] = [
            'name' => __('API Endpoint', 'clothes-2-order'),
            'desc_tip' => __('This is the unique URL that is used to communicate with Clothes2Order', 'clothes-2-order'),
            'id' => 'clothes-2-order_endpoint',
            'type' => 'text',
            'desc' => __('URL that is used to communicate with Clothes2Order', 'clothes-2-order'),
        ];

        $settings_c2o[] = [
            'name' => __('API Admin email', 'clothes-2-order'),
            'desc_tip' => __('Please enter an email address', 'clothes-2-order'),
            'id' => 'clothes-2-order_email',
            'type' => 'email',
            'desc' => __('This address will receive any failed order emails', 'clothes-2-order'),
        ];

        $settings_c2o[] = [
            'name' => __('API Test Mode', 'clothes-2-order'),
            'id' => 'clothes-2-order_test_mode',
            'type' => 'checkbox',
            'desc' => __('This will enable test mode.', 'clothes-2-order'),
        ];

        $settings_c2o[] = [
            'type' => 'sectionend', 'id' => 'clothes-2-order'
        ];

        return $settings_c2o;
    } else {
        return $settings;
    }
}

/**
 * Ensure the required product_cat terms are available
 */
function createProductCatTerms()
{
    require_once plugin_dir_path(__FILE__) . '/classes/ProductTerms.php';
    $productTerms = new clothes2order\classes\ProductTerms();

    $name = 'Clothing';
    $slug = sanitize_title_with_dashes($name);
    $description = 'Clothes 2 Order Product Category';

    $productTerms->ensureTermsExist('product_cat', $slug, $name, $description);
}

/**
 * Handle a successful order if the order/basket items contain c2o items
 *
 * @param $order_id
 */
function processNewOrder($order_id)
{
    require_once plugin_dir_path(__FILE__) . '/classes/Order.php';
    $order = new clothes2order\classes\Order();
    $order->checkBasket($order_id);
}

/**
 * Display the order meta from c2o
 *
 * @param $order
 */
function updateOrderUI($order)
{
    echo '<br style="clear: both"><h3>Clothes 2 Order</h3>';

    if ($value = $order->get_meta('_clothes_2_order_error_msg')) {
        echo '<p><strong>' . __("Error message", "clothes-2-order") . ':</strong> ' . $value . '</p>';
    }
    if ($value = $order->get_meta('_clothes_2_order_order_ID')) {
        echo '<p><strong>' . __("Order ID", "clothes-2-order") . ':</strong> ' . $value . '</p>';
    }
    if ($value = $order->get_meta('_clothes_2_order_net_value')) {
        echo '<p><strong>' . __("Order net value", "clothes-2-order") . ':</strong> ' . $value . '</p>';
    }
    if ($value = $order->get_meta('_clothes_2_order_gross_value')) {
        echo '<p><strong>' . __("Order gross value", "clothes-2-order") . ':</strong> ' . $value . '</p>';
    }
    if ($value = $order->get_meta('_clothes_2_order_est_dispatch_date')) {
        echo '<p><strong>' . __("Estimated dispatch date", "clothes-2-order") . ':</strong> ' . $value . '</p>';
    }
}
