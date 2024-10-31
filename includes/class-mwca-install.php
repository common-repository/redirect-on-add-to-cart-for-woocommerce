<?php
/**
 * Installation related functions and actions.
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'MWCA_Install', false ) ) return;

/**
 * MWCA_Install Class.
 */
class MWCA_Install {
	/**
	 * Activates plugin.
	 */
	public static function activate_plugin() {
		self::activate_helper();

		include_once dirname( MWCA_PLUGIN_FILE ) . '/includes/class-mwca.php';

		add_option( MWCA::SLUG . '_installation_lite', array( MWCA::VER, time() ), '', 'no' );

	    foreach( MWCA::get_default_settings() as $key => $value ) {
	    	add_option( $key, $value );
	    }
	}

	/**
	 * Activates plugin
	 * Checks WP, PHP, WC version compatibility
	 */
	public static function activate_helper() {
		$php = esc_html__( '7.0.0', 'ms-wc-cart-addons' );
		$wp = esc_html__( '5.0.0', 'ms-wc-cart-addons' );
		$wc_version = esc_html__( '4.1.0', 'ms-wc-cart-addons' );

		global $wp_version;

		if ( version_compare( PHP_VERSION, $php, '<' ) ) {
		  $flag = esc_html__( 'PHP', 'ms-wc-cart-addons' );
		  $version = $php;
		}
		elseif ( version_compare( $wp_version, $wp, '<' ) ) {
		  $flag = esc_html__( 'WordPress', 'ms-wc-cart-addons' );
		  $version = $wp;
		}
		elseif ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		    if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $wc_version, '<' ) ) {
		      $flag = esc_html__( 'WooCommerce', 'ms-wc-cart-addons' );
		      $version = $wc_version;
		    }
		    else {
		    	return;
		    }
		}
		else{
		    $flag = esc_html__( 'Activated WooCommerce', 'ms-wc-cart-addons' );
		    $version = $wc_version;
		}

		deactivate_plugins( basename( __FILE__ ) );
		/* translators: 1: non-compatible source (PHP, WordPress or WooCommerce) 2: version of non-compatible source */
		$msg = sprintf( '<p>' . esc_html__( 'The Redirect on Add To Cart For WooCommerce plugin requires %1$s version %2$s or greater.', 'ms-wc-cart-addons' ) . '</p>', $flag, $version );
		wp_die( $msg, esc_html__( 'Plugin Activation Error', 'ms-wc-cart-addons' ), array( 'response' => 200, 'back_link' => TRUE ) );
	}
}