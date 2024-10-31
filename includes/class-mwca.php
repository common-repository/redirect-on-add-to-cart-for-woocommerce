<?php
/**
 * Common Class
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'MWCA', false ) ) return;

/**
 * Common Class.
 *
 * @class MWCA
 */
class MWCA {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public const VER = '1.3.0';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	public const SLUG = 'mwca';

	/**
	 * Constants for Setting keys
	 */
	const SETTING_KEY_ENABLED                    = 'mwca_enabled';
	const SETTING_KEY_SKIP_CART_ENABLED          = 'mwca_skip_cart_enabled';
	const SETTING_KEY_REDIRECT_TO                = 'mwca_redirect_to';
	const SETTING_KEY_REDIRECT_TO_PAGE           = 'mwca_redirect_to_page';
	const SETTING_KEY_REDIRECT_TO_URL            = 'mwca_redirect_to_url';
	
	const REDIRECT_TO_PAGE = 1;
	const REDIRECT_TO_URL = 2;
	
	/**
	 * The single instance of the class.
	 *
	 * @var MWCA
	 */
	private static $_instance = null;

	/**
	 * cache of lazy loaded product settings 
	 *
	 * @var array
	 */
	private $product_settings = array();

	/**
	 * lazy loaded global settings 
	 *
	 * @var array
	 */
	private $global_settings = array();

	/**
	 * cache of normalized product ids
	 *
	 * @var array
	 */
	private $normalized_product_ids = array();

	/**
	 * Main MWCA Instance.
	 *
	 * Ensures only one instance of MWCA is loaded or can be loaded.
	 *
	 * @static
	 * @see    MWCA()
	 * @return MWCA - Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'woocommerce' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'woocommerce' ), '1.0.0' );
	}

	private function __construct() {}

	/**
	 * Gets the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( MWCA_PLUGIN_FILE ) );
	}

	/**
	 * Gets the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'mwca_template_path', 'mwca/' );
	}

	/**
	 * Gets the internal template path.
	 *
	 * @return string
	 */
	public function template_base(){
		return apply_filters( 'mwca_template_base', $this->plugin_path() . '/templates/' );
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * Legacy REST requests should still run some extra code for backwards compatibility.
	 *
	 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
	 * copied from wc b/c it may remove from wc in future.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

		return apply_filters( 'mwca_is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * What type of request is this?
	 * copied from wc b/c it is private in wc.
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}

	public function is_skip_cart_enabled( $product_id ) {
		$enabled = (bool) $this->get_setting( $product_id, self::SETTING_KEY_SKIP_CART_ENABLED );
		return apply_filters( 'mwca_is_skip_cart_enabled', $enabled, $product_id );
	}

	public function get_redirect_to( $product_id ) {
		$redirect_to = (int) $this->get_setting( $product_id, self::SETTING_KEY_REDIRECT_TO );
		return apply_filters( 'mwca_redirect_to', $redirect_to, $product_id );
	}

	public function get_redirect_to_page( $product_id ) {
		$page_id = $this->get_setting( $product_id, self::SETTING_KEY_REDIRECT_TO_PAGE );
		return apply_filters( 'mwca_redirect_to_page', $page_id, $product_id );
	}

	public function get_redirect_to_url( $product_id ) {
		$url = $this->get_setting( $product_id, self::SETTING_KEY_REDIRECT_TO_URL );
		return apply_filters( 'mwca_redirect_to_url', $url, $product_id );
	}

	public function get_checkout_url( $product_id ) {
		$type = $this->get_redirect_to( $product_id );
		if ( self::REDIRECT_TO_PAGE === $type ) {
			return get_permalink( $this->get_redirect_to_page( $product_id ) );
		}
		else if ( self::REDIRECT_TO_URL === $type ) {
			return $this->get_redirect_to_url( $product_id );
		}
		return false;
	}

	public function get_setting( $p_id, $key, $default = false ) {
		if ( 1 === $this->get_installed_version() ) {
		if ( ! isset( $this->normalized_product_ids[ $p_id ] ) ) {
			$this->normalized_product_ids[ $p_id ] = $this->normalize_product_id( $p_id );
		}
		$product_id = $this->normalized_product_ids[ $p_id ];
						
		$this->load_product_settings( $product_id );
		if ( $this->product_settings[ $product_id ][ self::SETTING_KEY_ENABLED ] ) {
			return $this->product_settings[ $product_id ][ $key ];
		}
		}
		
		$this->load_global_setting();
		if ( $this->global_settings[ self::SETTING_KEY_ENABLED ] ) {
			return $this->global_settings[ $key ];
		}
		
		return $default;
	}

	public function load_product_settings( $product_id ) {
		if ( ! isset( $this->product_settings[ $product_id ] ) ) {
			$settings = get_post_meta( $product_id, '_mwca_settings', true );
			if ( ! is_array( $settings ) || ! isset( $settings[ self::SETTING_KEY_ENABLED ] ) ) {
				 $settings = self::get_default_settings();
			}
			$this->product_settings[ $product_id ] = $settings;
		}
	}

	public function load_global_setting() {
		if ( ! isset( $this->global_settings[ self::SETTING_KEY_ENABLED ] ) ) {
			$this->global_settings = array();
			foreach( self::get_default_settings() as $key => $val ) {
				$this->global_settings[ $key ] = get_option( $key, $val );
			}
		}
	}

	public function normalize_product_id( $product_id ) {
		return 'product_variation' === get_post_type( $product_id )
			? wp_get_post_parent_id( $product_id )
			: $product_id;
	}

	public function get_installed_version() {
		$arr = get_option( MWCA::SLUG . '_installation_lite' );
		return is_array( $arr ) && (int) $arr[1] < 1662409076 ? 1 : 2;

	}

	public static function get_default_settings() {
		return array(
			self::SETTING_KEY_ENABLED => false,
			self::SETTING_KEY_SKIP_CART_ENABLED => false,
			self::SETTING_KEY_REDIRECT_TO => self::REDIRECT_TO_PAGE,
			self::SETTING_KEY_REDIRECT_TO_PAGE => 0,
			self::SETTING_KEY_REDIRECT_TO_URL => '',
		);
	}

	public static function log( $arr, $msg = '' ) {
		if ( $msg ) $arr =  array_merge( array( $msg ), $arr );
		error_log( var_export( $arr, 1 ) );
	}

}