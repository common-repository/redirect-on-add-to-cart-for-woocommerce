<?php
/**
 * Main Frontend Class
 */

defined( 'ABSPATH' ) || exit;

/**
 * @class MWCA_Front
 */
final class MWCA_Front {
	private $mwca = null;

	/**
	 * MWCA Constructor.
	 */
	public function __construct( $mwca ) {
		$this->mwca = $mwca;
	}

	public function init() {
		add_action( 'woocommerce_init', array( $this, 'wc_init' ) );
	}

	/**
	 * Setup callbacks for wc specific hooks.
	 */
	public function wc_init() {
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'add_attributes' ), 100, 2 );
		
		add_filter( 'woocommerce_add_to_cart_handler', array( $this, 'enable_redirection' ), 100, 2 );

		add_filter( 'woocommerce_get_script_data', array( $this, 'update_script_data' ), 100, 2 );

		add_action( 'template_redirect', array( $this, 'handle_classic_or_block_template' ), 100 );
	}

	public function add_attributes( $args, $product ) {
		if ( is_array( $args ) && isset( $args['class'] ) &&
			false !== strpos( $args['class'], 'ajax_add_to_cart', true ) &&
			isset( $args['attributes'] ) && is_array( $args['attributes'] )
		) {
			$url = '';
			if ( $this->mwca->is_skip_cart_enabled( $product->get_id() ) ) {
				$url = $this->mwca->get_checkout_url( $product->get_id() );
			}
			else if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				// this will not call the function added in cart-handler hook b/c that one is added only when cart is being skipped. WC_Frontend_Scripts::get_script_data() also fires it with null product id.
				$url = apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null );
			}

			if ( trim( $url ) ) $args['attributes']['data-mwca-url'] = esc_attr( $url );
		}
		return $args;
	}

	public function enable_redirection( $type, $product ) {
		if ( $this->mwca->is_skip_cart_enabled( $product->get_id() ) ) {
			$checkout_url = trim( $this->mwca->get_checkout_url( $product->get_id() ) );

			// WC_Form_Handler::add_to_cart_action() does a wp_safe_redirect() which only redirects to the allowed hosts and fallbacks to admin_url(). As the add2cart can redirect to the custom url, set custom url as the fallback. wp_safe_redirect_fallback is fired with admin_url(), 302 from wp_safe_redirect().
			add_filter( 'wp_safe_redirect_fallback', function( $url, $http_status_code ) use ( $checkout_url ) {
				return $checkout_url ? $checkout_url : $url;
			}, 100, 2 );

			add_filter( 'woocommerce_add_to_cart_redirect', function( $url, $product ) use ( $checkout_url ) {
				return $checkout_url ? $checkout_url : $url;
			}, 100, 2 );
		}

		return $type;
	}

	public function enqueue_scripts() {
		$version = MWCA::VER;
		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			wp_enqueue_script( MWCA::SLUG . 'script', plugins_url( 'assets/script.js', MWCA_PLUGIN_FILE ), array( 'jquery', 'woocommerce', 'wc-add-to-cart' ), $version, true );

			wp_add_inline_style( 'woocommerce-inline', '[data-mwca-url] + .added_to_cart { display:none; }' );
		}
	}

	public function update_script_data( $params, $handle ) {
		if ( 'wc-add-to-cart' === $handle && is_array( $params ) ) {
			$params['cart_redirect_after_add'] = 'no';
		}
		return $params;
	}

	public function handle_classic_or_block_template() {
		//passing 'archive-product' as a dummy value. 'archive-product' is a ligit template name.
		$wc_has_block_template = apply_filters( 'woocommerce_has_block_template', false, 'archive-product' );
		if ( $wc_has_block_template ) {
			add_filter( 'woocommerce_product_supports', array( $this, 'deny_ajax_support' ), 100, 3 );
			add_action( 'wp_footer', array( $this, 'print_block_script' ), 100 );
		}
		else{
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		}
	}

	public function deny_ajax_support( $is_supported, $feature, $product ) {
	    if ( 'ajax_add_to_cart' === $feature && $this->mwca->is_skip_cart_enabled( $product->get_id() ) ) {
	    	$is_supported = false;
	    }
	    return $is_supported;
	}

	public function print_block_script() {
	?>
	<script>
		jQuery( function( $ ) {
			'use strict';
			
			if (wp && wp.hooks) {
				wp.hooks.addAction( 'experimental__woocommerce_blocks-cart-add-item', 'pxq/mwca/redirect', function(obj){
					if ( obj.product.extensions && obj.product.extensions['pxq/mwca'] ) {
						var data = obj.product.extensions['pxq/mwca'];
						if ( data.is_skip_cart_enabled ) {
							window.location = data.checkout_url;
						}
					}
				} );
			}
			
			var is_skip_cart_enabled = <?php echo $this->mwca->is_skip_cart_enabled( 0 ) ? 1 : 0; ?>;
			if (is_skip_cart_enabled &&
				wc && wc.wcSettings && wc.wcSettings.allSettings &&
				wc.wcSettings.allSettings.productsSettings &&
				wc.wcSettings.allSettings.productsSettings.cartRedirectAfterAdd) {
				wc.wcSettings.allSettings.productsSettings.cartRedirectAfterAdd = false
			}
		} );
	</script>
	<?php
	}
}