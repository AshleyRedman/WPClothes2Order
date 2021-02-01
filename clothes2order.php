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
 * Version:      1.2
 * Plugin URI:   https://ashredman.com
 * Author:       Reuben Porter & Ashley Redman
 * Author URI:   https://ashredman.com
 * Text Domain:  clothes2order
 * Domain Path:  /languages/
 * Requires PHP: 7.3
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

namespace clothes2order;

use clothes2order\classes\ProductTerms;

defined( 'ABSPATH' ) || die();

$c2o_dir = dirname( __FILE__ );

if (class_exists('WooCommerce')) {

    // TODO init the plugin
    // 1. Check & create specific taxonomy terms to determine which products to check in a basket
    add_action('init', function () {
        new classes\ProductTerms();
    });
    // 2. Add any additional product fields


    // 3. On payment complete, 'run' the basket & post API calls for each basket item if meeting requirement
    add_action('woocommerce_payment_complete', function ($order_id) {
        $order = new classes\Order($order_id);
    });

} else {
    /**
     * Docs: https://developer.wordpress.org/reference/hooks/admin_notices/
     */
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . _('Woocommerce is required to use the Clothes2Order Plugin!') . '</p></div>';
    });
}