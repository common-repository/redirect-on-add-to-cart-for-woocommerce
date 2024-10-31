<?php
/**
 * Plugin Name: Redirect on Add To Cart For WooCommerce
 * Description: Lets you redirect the user to a page on your website or internal/external URL on add-to-cart button. You can enable this redirection for all or selected products.
 * Version: 1.3.0
 * Author: wpxqw
 * Developer: wpxqw
 * Text Domain: ms-wc-cart-addons
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 6.5.3
 * WC requires at least: 4.1
 */

// TESTED with WC 8.8.5

defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/includes/class-mwca-install.php';
register_activation_hook( __FILE__, array( 'MWCA_Install', 'activate_plugin' ) );

/**
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
  return;
}

if ( ! defined( 'MWCA_PLUGIN_FILE' ) ) {
	define( 'MWCA_PLUGIN_FILE', __FILE__ );
}

// Include the common class and main plugin class, and initiate them - MWCA & MWCA_Plugin.
if ( ! class_exists( 'MWCA', false ) && ! class_exists( 'MWCA_Plugin', false ) ) {
	include_once dirname( MWCA_PLUGIN_FILE ) . '/includes/class-mwca.php';
	include_once dirname( MWCA_PLUGIN_FILE ) . '/includes/class-mwca-plugin.php';
	
	/**
	 * Returns the instance of MWCA class.
	 *
	 * @return MWCA
	 */
	function MWCA() {
		return MWCA::instance();
	}

	// Construct MWCA instance and set as global.
	$GLOBALS['mwca'] = MWCA();

	$plugin = new MWCA_Plugin( $GLOBALS['mwca'] );
	$plugin->init();
}