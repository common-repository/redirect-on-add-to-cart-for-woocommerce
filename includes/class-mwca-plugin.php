<?php
use Automattic\WooCommerce\StoreApi\Schemas\V1\ProductSchema;

/**
 * Main Plugin Class
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'MWCA_Plugin', false ) ) return;

/**
 * Main Plugin Class.
 *
 * @class MWCA_Plugin
 */
final class MWCA_Plugin {
	private $mwca = null;

	/**
	 * MWCA_Plugin Constructor.
	 *
	 * @param MWCA $mwca MWCA instance.
	 */
	public function __construct( $mwca ) {
		$this->mwca = $mwca;
	}

	/**
	 * Initializes the instance. Setups callback for hooks and includes files.
	 */
	public function init() {
		$this->includes();
		add_action( 'woocommerce_blocks_loaded', array( $this, 'register_endpoint_data' ) );
	}

	public function register_endpoint_data() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint' => ProductSchema::IDENTIFIER,
				'namespace' => 'pxq/mwca',
				'data_callback' => array( $this, 'get_mwca_data' ),
				'schema_callback' => array( $this, 'get_mwca_schema' ),
				'schema_type' => ARRAY_A,
			)
		);	
	}

	public function get_mwca_data( $product ) {
		return array(
			'is_skip_cart_enabled' => $this->mwca->is_skip_cart_enabled( $product->get_id() ),
			'checkout_url' => trim( $this->mwca->get_checkout_url( $product->get_id() ) ),
		);
	}

	public function get_mwca_schema() {
		return array(
			'is_skip_cart_enabled' => array(
				'description' => __( 'MWCA: Whether to redirect on add to cart or not', 'ms-wc-cart-addons' ),
				'type' => 'boolean',
				'readonly' => true,
			),
			'checkout_url' => array(
				'description' => __( 'MWCA: Where to redirect on add to cart', 'ms-wc-cart-addons' ),
				'type' => 'string',
				'readonly' => true,
			),
		);
	}

	/**
	 * Includes files used in admin, on the frontend and common files.
	 */
	private function includes() {
		if ( $this->mwca->is_request( 'admin' ) ) {
			include_once dirname( MWCA_PLUGIN_FILE ) . '/includes/admin/class-mwca-admin.php';

			$admin = new MWCA_Admin( $this->mwca );
			$admin->init();
		}

		if ( $this->mwca->is_request( 'frontend' ) ) {
			include_once dirname( MWCA_PLUGIN_FILE ) . '/includes/class-mwca-front.php';

			$front = new MWCA_Front( $this->mwca );
			$front->init();
		}
	}
}//class
