<?php

if(!defined('ABSPATH')) {
	exit;
}

/**
 * The bluefin_payment_gateway class.
 */
class bluefin_payment_gateway {
	/**
	 * This class instance.
	 *
	 * @var \bluefin_payment_gateway single instance of this class.
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			// new Setup();
		}

		$this->init();
		
		
		
		// Register Routes
		add_action('rest_api_init', [ $this, 'register_routes' ]);

		add_action('woocommerce_admin_order_data_after_billing_address', [ $this, 'bf_transaction_id_order_meta' ], 10, 1);

		add_action('woocommerce_after_order_details', [ $this, 'bf_transaction_id_order_checkout_meta' ]);
		
		// Admin Script must be here in order to load in admin mode for orders
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		
	}
	

	public function init() {
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/class-wc-bluefin-defaults.php';
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/class-wc-bluefin-exception.php';
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/class-wc-bluefin-logger.php';
		
		
		include_once WC_BLUEFIN_PLUGIN_PATH . '/includes/class-wc-bluefin-api.php';
		
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/class-wc-bluefin-gateway.php';

		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_gateways' ] );
		
		
		add_filter( 'plugin_action_links_' . plugin_basename( WC_BLUEFIN_MAIN_FILE ), [ $this, 'plugin_action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		
	}
	
	

	public function add_gateways($methods) {
		$methods[] = 'WC_Gateway_Bluefin';

		return $methods;
	}
	
	
	public function plugin_action_links( $links ) {
		$plugin_links = [
			'<a href="admin.php?page=wc-settings&tab=checkout&section=bluefin_gateway">' . esc_html__( 'Settings', 'bluefin-payment-gateway' ) . '</a>',
		];
		return array_merge( $plugin_links, $links );
	}
	
	
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WC_BLUEFIN_MAIN_FILE ) === $file ) {
			$row_meta = [
				'docs'    => '<a href="' . esc_url( 'https://developers.bluefin.com/payconex' ) . '" title="' . esc_attr( __( 'View Documentation', 'bluefin-payment-gateway' ) ) . '">' . __( 'Docs', 'bluefin-payment-gateway' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://www.bluefin.com/contact/' ) . '" title="' . esc_attr( __( 'Open a support request at Bluefin', 'bluefin-payment-gateway' ) ) . '">' . __( 'Support', 'bluefin-payment-gateway' ) . '</a>',
			];
			return array_merge( $links, $row_meta );
		}
		
		return (array) $links;
	}
	
	public function admin_scripts() {
			wp_enqueue_script('bluefin-plugin-admin', plugins_url( 'assets/plugin_admin.js', WC_BLUEFIN_MAIN_FILE ), ['wp-element'], null, true);
			
			wp_localize_script('bluefin-plugin-admin', 'bluefinPlugin', [
				'capture_url' => esc_url_raw(rest_url('wc_bluefin/v1/capture_transaction')),
				'nonce'    => wp_create_nonce('wp_rest'),
			]);
	}
	
	public function register_routes() {
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/admin/class-wc-bluefin-rest-base-controller.php';
	 	require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/admin/class-wc-bluefin-rest-iframe-controller.php';
		require_once WC_BLUEFIN_PLUGIN_PATH . '/includes/admin/class-wc-bluefin-rest-transaction-controller.php';
	 	
	 	$iframe_controller = new WC_REST_Bluefin_Iframe_Controller();
		$transaction_controller = new WC_REST_Bluefin_Transaction_Controller();
	 	
	 	$iframe_controller->register_routes();
		$transaction_controller->register_routes();
		
	}
	
	public function bf_transaction_id_order_checkout_meta($order) {
		echo '
		    <h2> Bluefin Payment Data </h2>
			<p><strong>' . __('Bluefin Transaction Identifier') . ':</strong>' . $order->get_meta('bluefinTransactionId') . '</p>
		';
	}

	public function bf_transaction_id_order_meta($order) {
		echo '
		    <h3> Bluefin Payment Data </h3>
			<p><strong>' . __('Bluefin Transaction Identifier') . ':</strong><br> ' . $order->get_meta('bluefinTransactionId') . '</p>
		';
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'bluefin-payment-gateway' ), $this->version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'bluefin-payment-gateway' ), $this->version );
	}

	/**
	 * Gets the main instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @return \bluefin_payment_gateway
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
