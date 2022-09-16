<?php

/**
 * Plugin Name:         WPClothes2Order
 * Plugin URI:          https://wpclothes2order.com/download
 * Description:         Unofficial WooCommerce Plugin for <a href="https://www.clothes2order.com/">Clothes2Order</a>
 * Version:             1.0.0
 * Plugin URI:          https://www.wpclothes2order.com
 * Author:              Ashley Redman
 * Author URI:          https://github.com/AshleyRedman
 * License              GPL v3 or later
 * Text Domain:         wpc2o
 * Domain Path:         /languages
 * Requires at least:   6.0.0
 * Requires PHP:        7.4
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

defined('ABSPATH') || exit;

require_once 'vendor/autoload.php';
require_once 'includes/constant.php';

require_once 'classes/WPC2O_C2O_Product.php';
require_once 'classes/WPC2O_Email.php';
require_once 'classes/WPC2O_OrderRequest.php';
require_once 'classes/WPC2O_Notice.php';
require_once 'classes/WPC2O_Stock_Sync.php';

require_once 'includes/scripts.php';
require_once 'includes/wc-options.php';
require_once 'includes/wpc2o-options.php';
require_once 'includes/wpc2o-options-getting-started.php';
require_once 'includes/wpc2o-options-api.php';
require_once 'includes/wpc2o-options-delivery.php';
require_once 'includes/wpc2o-options-logo.php';
require_once 'includes/wpc2o-options-orders.php';
require_once 'includes/wpc2o-options-stock.php';
require_once 'includes/wpc2o-orders.php';
require_once 'includes/register_rest_fields.php';

add_action('plugins_loaded', 'wpc2o_start');

function wpc2o_start()
{
    if (class_exists('Woocommerce')) {
        add_filter('woocommerce_get_sections_products', 'wpc2o_options_page');
        add_filter('woocommerce_get_settings_products', 'wpc2o_options_page_settings', 10, 2);

        if (wpc2o_api_credentials_check()) {
            // styles & scripts
            add_action('admin_enqueue_scripts', 'wpc2o_assets');

            // plugin options
            add_action('after_setup_theme', 'wpc2o_options');
            add_filter('plugin_action_links_WPClothes2Order/wpclothes2order.php', 'wpc2o_settings_link');

            // Admin products columns
            add_filter('manage_edit-product_columns', 'wpc2o_admin_products_c2o_column', 9999);
            add_action('manage_product_posts_custom_column', 'wpc2o_wc_c2o_product_column', 10, 2);

            // Admin orders columns
            add_filter('manage_edit-shop_order_columns', 'wpc2o_admin_orders_c2o_column', 9999);
            add_action('manage_shop_order_posts_custom_column', 'wpc2o_wc_c2o_order_column', 10, 2);

            // Plugin theme options
            add_action('carbon_fields_register_fields', 'wpc2o_theme_options');
            // Product options
            add_action('carbon_fields_register_fields', 'wpc2o_wc_theme_options');

            // register on place order
            add_action('woocommerce_thankyou', 'wpc2o_process_completed_order', 10, 1);
            add_action('woocommerce_admin_order_data_after_order_details', 'wpc2o_update_order_notes', 10, 1);

            // register rest fields
            add_action('rest_api_init', 'wpc2o_register_rest_fields');

            // register cron
        } else {
            new WPC2O_Notice('error', 'Missing WPClothes2Order API credentials. Please add them <a href="' . get_admin_url() . 'admin.php?page=wc-settings&tab=products&section=wpc2o">here</a>', false);
        }
    } else {
        new WPC2O_Notice('error', 'Woocommerce is required to use WPClothes2Order!', false);
    }
}
