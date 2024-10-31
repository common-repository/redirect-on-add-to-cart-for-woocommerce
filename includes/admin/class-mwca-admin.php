<?php
/**
 * Main Admin Class
 */

defined( 'ABSPATH' ) || exit;

/**
 * @class MWCA_Admin
 */
class MWCA_Admin {
	const BOOL_SETTINGS = array(
			MWCA::SETTING_KEY_ENABLED, MWCA::SETTING_KEY_SKIP_CART_ENABLED,
	);

	private $mwca = null;

	public function __construct( $mwca ) {
		$this->mwca = $mwca;
	}

	public function init() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'get_section_settings' ), 10, 2 );
		foreach( self::BOOL_SETTINGS as $key ) {
			add_filter( "woocommerce_admin_settings_sanitize_option_$key", array( $this, 'sanitize_setting' ), 10, 3 );
		}
		add_action( 'woocommerce_settings_mwca_settings', array( $this, 'echo_settings_js') );
		if ( 1 === $this->mwca->get_installed_version() ) {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_data_tab') );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_data_tab' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_data_tab' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( MWCA_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function add_section( $sections ) {
			$sections[ 'mwca_settings' ] = esc_html__( 'Redirect on add-to-cart', 'ms-wc-cart-addons' );
			return $sections;
	}

	public function get_section_settings( $settings, $current_section ) {
		if ( 'mwca_settings' !== $current_section ) return $settings;
		return array(
			array(
				'name'     => esc_html__( 'Main', 'ms-wc-cart-addons' ),
				'desc'     => esc_html__( 'Configure all products of your shop at once. Below settings can be overridden for a single product on product edit screen.', 'ms-wc-cart-addons' ),
				'id'       => 'mwca_settings',
				'type'     => 'title',
			),
			array(
				'name'     => esc_html__( 'Enable configuration', 'ms-wc-cart-addons' ),
				'desc'     => esc_html__( 'Configure all products', 'ms-wc-cart-addons' ),
				'id'       => MWCA::SETTING_KEY_ENABLED,
				'type'     => 'checkbox',
				'value'    => get_option( MWCA::SETTING_KEY_ENABLED ) ? 'yes' : 'no',
			),
			array(
				'name'     => esc_html__( 'Enable redirection', 'ms-wc-cart-addons' ),
				'desc'     => esc_html__( 'Redirect the user to a page or URL on add-to-cart button', 'ms-wc-cart-addons' ),
				'id'       => MWCA::SETTING_KEY_SKIP_CART_ENABLED,
				'type'     => 'checkbox',
				'value'    => get_option( MWCA::SETTING_KEY_SKIP_CART_ENABLED ) ? 'yes' : 'no',
			),
			array(
				'name'     => esc_html__( 'Redirect to', 'ms-wc-cart-addons' ),
				'id'       => MWCA::SETTING_KEY_REDIRECT_TO,
				'type'     => 'radio',
				'value'    => get_option( MWCA::SETTING_KEY_REDIRECT_TO, MWCA::REDIRECT_TO_PAGE ),
				'options'  => array(
					MWCA::REDIRECT_TO_PAGE => esc_html__( 'A page on your website', 'ms-wc-cart-addons' ),
					MWCA::REDIRECT_TO_URL  => esc_html__( 'An internal or external URL', 'ms-wc-cart-addons' ),
				),
				'desc_tip' => esc_html__( 'Select whether to redirect the user to a page on your website or an internal/external URL.', 'ms-wc-cart-addons' ),
			),
			array(
				'name'     => esc_html__( 'Redirect to page', 'ms-wc-cart-addons' ),
				'desc'     => esc_html__( 'Select a page of your website where the user will be redirected to on add-to-cart button.', 'ms-wc-cart-addons' ),
				'id'       => MWCA::SETTING_KEY_REDIRECT_TO_PAGE,
				'type'     => 'select',
				'value'    => get_option( MWCA::SETTING_KEY_REDIRECT_TO_PAGE ),
				'options'  => $this->get_pages(),
				'desc_tip' => true,
			),
			array(
				'name'     => esc_html__( 'Redirect to URL', 'ms-wc-cart-addons' ),
				'desc'     => esc_html__( 'Input a URL. This can be any URL - for example, URL of a page on your website, URL of a page of your Amazon store.', 'ms-wc-cart-addons' ),
				'id'       => MWCA::SETTING_KEY_REDIRECT_TO_URL,
				'type'     => 'url',
				'value'    => get_option( MWCA::SETTING_KEY_REDIRECT_TO_URL ),
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'mwca_settings',
			),
		);
	}

	public function sanitize_setting( $value, $option, $raw_value ) {
		return 'yes' === $value ? 1 : 0;
	}

	public function echo_settings_js( $parent_selector = 'table', $field_selector = 'tr' ) {
	?>
		<script type="text/javascript">
			jQuery( function( $ ) {
				'use strict';
				
				var parentSelector = '<?php echo esc_html( $parent_selector ); ?>';
				var fieldSelector = '<?php echo esc_html( $field_selector ); ?>';
				var keys = [ "<?php echo implode( '", "', array_map( 'esc_html', array_keys( MWCA::get_default_settings() ) ) ); ?>" ];
				var ids = [ 1, 3, 4 ];
				var names = [ 2 ];
				var idElems = [];
				var $nameElems = [];
				var $main = $( '#' + keys[0] );
				$.each( ids, function( index, value ) {
					idElems.push( $( '#' + keys[ value ] )[0] );
				} );
				var $idSet = $( idElems );
				$.each( names, function( index, value ) {
					$nameElems.push( $( '[name="' + keys[ value ] + '"]' ) );
				} );
				
				
				function showHideField( $e, flag ) {
					$e.parentsUntil( parentSelector, fieldSelector ).first()[ flag ? 'show' : 'hide' ]();
				}

				function showHideSkipCart( flag ) {
					$idSet.each( function( index ) { if ( index > 0 && index < 4 ) showHideField( $( this ), flag ); } );
					$.each( $nameElems, function( i, $e ) { showHideField( $e, flag ); } );
					setTimeout( function() {
						$.each( $nameElems, function( i, $e ) { $e.trigger('change'); } );
					}, 0 );
				}

				function showHideConfig( flag ) {
					var skipCart = flag && $idSet.eq(0).prop( 'checked' );
					showHideSkipCart( skipCart );
					showHideField( $idSet.eq(0), flag );
				}
				
				showHideConfig( $main.prop( 'checked' ) );

				$main.change( function() {
					showHideConfig( $( this ).prop( 'checked' ) );
				} );

				$idSet.eq(0).change( function() {
					showHideSkipCart( $( this ).prop( 'checked' ) );
				} );

				$nameElems[0].change( function() {
					if ( ! $main.prop( 'checked') || ! $idSet.eq(0).prop( 'checked') ) return;
					var flag = '1' === $nameElems[0].filter(':checked').val();
					showHideField( $idSet.eq(1), flag );
					showHideField( $idSet.eq(2), ! flag );
				} );
			} );
		</script>
	<?php
	}

	public function add_data_tab( $tabs ) {
	    $tabs[ 'mwca_settings' ] = array(
	        'label' => esc_html__( 'Redirect on add-to-cart', 'ms-wc-cart-addons' ),
	        'target' => 'mwca_settings',
	        'class' => array(),
	        'priority' => 100,
	    );
	    return $tabs;
	}

	public function render_data_tab() {
		global $post, $product_object;

		$settings = get_post_meta( $post->ID, '_mwca_settings', true );
		$settings = wp_parse_args( ! is_array( $settings ) ? array() : $settings, MWCA::get_default_settings() );
		
		echo '<div id="mwca_settings" class="panel woocommerce_options_panel">';
		woocommerce_wp_checkbox(
			array(
				'label'          => esc_html__( 'Enable Configuration', 'ms-wc-cart-addons' ),
				'description'    => esc_html__( 'Configure this product', 'ms-wc-cart-addons' ),
				'id'             => MWCA::SETTING_KEY_ENABLED,
				'value'          => $settings[ MWCA::SETTING_KEY_ENABLED ] ? 'yes' : 'no',
			)
		);
		woocommerce_wp_checkbox(
			array(
				'label'          => esc_html__( 'Enable redirection', 'ms-wc-cart-addons' ),
				'description'    => esc_html__( 'Redirect the user to a page or URL on add-to-cart button', 'ms-wc-cart-addons' ),
				'id'             => MWCA::SETTING_KEY_SKIP_CART_ENABLED,
				'value'          => $settings[ MWCA::SETTING_KEY_SKIP_CART_ENABLED ] ? 'yes' : 'no',
			)
		);
		woocommerce_wp_radio(
			array(
				'label'          => esc_html__( 'Redirect to', 'ms-wc-cart-addons' ),
				'description'    => esc_html__( 'Select whether to redirect the user to a page on your website or an internal/external URL.', 'ms-wc-cart-addons' ),
				'id'             => MWCA::SETTING_KEY_REDIRECT_TO,
				'value'          => $settings[ MWCA::SETTING_KEY_REDIRECT_TO ],
				'options'        => array(
					MWCA::REDIRECT_TO_PAGE => esc_html__( 'A page on your website', 'ms-wc-cart-addons' ),
					MWCA::REDIRECT_TO_URL  => esc_html__( 'An internal or external URL', 'ms-wc-cart-addons' ),
				),
				'desc_tip'       => true,
			)
		);
		woocommerce_wp_select(
			array(
				'label'          => esc_html__( 'Redirect to page', 'ms-wc-cart-addons' ),
				'description'    => esc_html__( 'Select a page of your website where the user will be redirected to on add-to-cart button.', 'ms-wc-cart-addons' ),
				'id'             => MWCA::SETTING_KEY_REDIRECT_TO_PAGE,
				'value'          => $settings[ MWCA::SETTING_KEY_REDIRECT_TO_PAGE ],
				'options'        => $this->get_pages(),
				'desc_tip'       => true,
			)
		);
		woocommerce_wp_text_input(
			array(
				'label'          => esc_html__( 'Redirect to URL', 'ms-wc-cart-addons' ),
				'description'    => esc_html__( 'Input a URL. This can be any URL - for example, URL of a page on your website, URL of a page of your Amazon store.', 'ms-wc-cart-addons' ),
				'id'             => MWCA::SETTING_KEY_REDIRECT_TO_URL,
				'data_type'      => 'url',
				'value'          => $settings[ MWCA::SETTING_KEY_REDIRECT_TO_URL ],
				'desc_tip'       => true,
			)
		);
		echo '</div>';
		$this->echo_settings_js( '#mwca_settings', 'p, fieldset' );
	}

	public function save_data_tab( $post_id, $post ) {
		$keys = array_keys( MWCA::get_default_settings() );
		$data = array();
		
		foreach( $keys as $key ) $data[ $key ] = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : '';
		foreach( self::BOOL_SETTINGS as $key ) $data[ $key ] = 'yes' === $data[ $key ] ? 1 : 0;
	    
	    update_post_meta( $post_id, '_mwca_settings', $data );
	}

	public function enqueue_scripts( $hook_suffix ) {
		global $post_type;
		if ( 'product' === $post_type && ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) ) {
			wp_register_style( MWCA::SLUG, false ); // phpcs:ignore
			wp_enqueue_style( MWCA::SLUG );
			wp_add_inline_style( MWCA::SLUG, '#mwca_settings .wc-radios label{ margin-left:0;}' );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=mwca_settings' ) . '" aria-label="' . esc_attr__( 'View WC Redirect on Add To Cart settings', 'ms-wc-cart-addons' ) . '">' . esc_html__( 'Settings', 'ms-wc-cart-addons' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}


	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( MWCA_PLUGIN_FILE ) === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'mwca_docs_url', 'https://wpxqw.github.io/mwca/' ) ) . '" aria-label="' . esc_attr__( 'View documentation', 'ms-wc-cart-addons' ) . '">' . esc_html__( 'Docs', 'ms-wc-cart-addons' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	public function get_pages() {
		$pages = get_pages( array( 'hierarchical' => false, ) );
		$arr = array();
		if ( $pages ) {
			foreach( $pages as $p ) {
				$arr[ $p->ID ] = $p->post_title;
			}
		}
		return $arr;
	}
}
