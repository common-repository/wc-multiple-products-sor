<?php
/**
 * Plugin Name: Sort Multiple Products in WooCommerce
 * Plugin URI: http://wpurge.com/plugins/multisort-products
 * Description: Sort Multiple Products at once in WooCommerce using drag and drop.
 * Version: 1.0.0
 * Author: wpurge
 * Author URI: http://wpurge.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: wc-multiple-products-sort
 * Requires at least: 5.9
 * Tested up to: 6.1.1
 * Requires PHP: 7.4
 * WC requires at least: 7.1
 * WC tested up to: 7.5.1
 *
 * @since 1.0.0
 * @package wpurge_multisort_products
 * @subpackage wpurge_multisort_products
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// if class WPurge_MultiSort_Products is defined, abort.
if ( class_exists( 'WPurge_MultiSortable_Products' ) ) {
	return;
}

// if woocommerce is not active, abort.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}

/**
 * Define the plugin version.
 */
define( 'WPURGE_MULTISORT_PRODUCTS_VERSION', '1.0.0' );
/**
 * Define the plugin directory.
 */
define( 'WPURGE_MULTISORT_PRODUCTS_DIR', plugin_dir_path( __FILE__ ) );
/**
 * Define the plugin URL.
 */
define( 'WPURGE_MULTISORT_PRODUCTS_URL', plugin_dir_url( __FILE__ ) );

require_once WPURGE_MULTISORT_PRODUCTS_DIR . 'inc/class-wpurge-multisortable-products.php';

$multisortable_products = new WPurge_MultiSortable_Products();
