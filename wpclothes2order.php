<?php

/**
 * Plugin Name:  WPClothes2Order
 * Description:  Unofficial WooCommerce Plugin for <a href="https://www.clothes2order.com/">Clothes2Order</a>
 * Version:      1.0.0
 * Plugin URI:   https://www.wpclothes2order.com
 * Author:       Ashley Redman
 * Author URI:   https://github.com/AshleyRedman
 * Text Domain:  wpc2o
 * Domain Path:
 * Requires at least: 6.0.0
 * Requires PHP: 7.4
 *
 * @package   wpclothes2order
 * @link      https://www.wpclothes2order.com
 * @author    Ashley Redman <ash.redman@outlook.com>
 * @copyright 2022 Ashley Redman
 * @license   GPL v2 or later
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

namespace WPClothes2Order;

defined('ABSPATH') || exit;

add_action('plugins_loaded', function () {
    if (class_exists('Woocommerce')) {
        if (!class_exists('WPClothes2Order\Options')) {
            require_once('classes/Options.php');
            new Options;
        }
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>' . _('Woocommerce is required to use WPClothes2Order!', 'wp-clothes-2-order') . '</p></div>';
        }, 10, 2);
    }
});