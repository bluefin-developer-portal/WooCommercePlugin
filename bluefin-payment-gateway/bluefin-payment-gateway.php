<?php
/**
 * Plugin Name: Bluefin Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/bluefin-payment-gateway/
 * Description: The Bluefin PayConex™ Gateway is a comprehensive, PCI compliant, full-service payment solution.
 * Version: 1.0.0
 * Author: Bluefin
 * Author URI: https://www.bluefin.com/
 * Text Domain: bluefin-payment-gateway
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
 
// TODO: Plugin URI: https://wordpress.org/plugins/bluefin-payment-gateway/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
if ( ! defined( 'MAIN_PLUGIN_FILE' ) ) {
	define( 'MAIN_PLUGIN_FILE', __FILE__ );
}
 */

define( 'WC_BLUEFIN_MAIN_FILE', __FILE__ );

define( 'WC_BLUEFIN_PLUGIN_PATH', untrailingslashit( plugin_dir_path( WC_BLUEFIN_MAIN_FILE ) ) );


// TODO: Exclude vendor since we are not using any third parties
// require_once WC_BLUEFIN_PLUGIN_PATH . '/vendor/autoload_packages.php'; // plugin_dir_path( __FILE__ )

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function bluefin_payment_gateway_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Bluefin Payment Gateway requires WooCommerce to be installed and active. You can download %s here.', 'bluefin_payment_gateway' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( WC_BLUEFIN_MAIN_FILE, 'bluefin_payment_gateway_activate' );

register_deactivation_hook( WC_BLUEFIN_MAIN_FILE, 'bluefin_payment_gateway_deactivate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function bluefin_payment_gateway_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'bluefin_payment_gateway_missing_wc_notice' );
		return;
	}
}

function bluefin_payment_gateway_deactivate() {
	// add_action( 'admin_notices', 'bluefin_payment_gateway_missing_wc_notice' );
	return;
}



function woocommerce_bluefin_gateway() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		require_once __DIR__ . '/includes/class-wc-bluefin.php';

		$plugin = bluefin_payment_gateway::instance();
	}

	return $plugin;
}


add_action( 'plugins_loaded', 'bluefin_payment_gateway_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function bluefin_payment_gateway_init() {
	load_plugin_textdomain( 'bluefin-payment-gateway', false, plugin_basename( dirname( WC_BLUEFIN_MAIN_FILE ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'bluefin_payment_gateway_missing_wc_notice' );
		return;
	}

	woocommerce_bluefin_gateway();

}

add_action( 'woocommerce_blocks_loaded', function() {
	add_action( 'woocommerce_blocks_payment_method_type_registration', function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
		$payment_method_registry->register( new class extends AbstractPaymentMethodType {

			public function get_name() {
				return 'bluefin_gateway'; // Must match your $this->id
			}

			public function initialize() {
				// woocommmerce_ + this->id + _settings
				$this->settings = get_option( 'woocommerce_bluefin_gateway_settings' );
			}

			public function is_active() {
				return true;
			}

			public function get_payment_method_script_handles() {
				wp_register_script(
					'bluefin-blocks',
					plugins_url( 'assets/index.js', WC_BLUEFIN_MAIN_FILE ),
					[ 'wc-blocks-registry', 'wp-element', 'wp-i18n' ],
					'1.0.0',
					true
				);
				return [ 'bluefin-blocks' ];
			}

			public function get_payment_method_script_handles_for_admin() {
				return $this->get_payment_method_script_handles();
			}
			
			public function get_supported_features(): array {
    				return [
        				'products',
    				];
			}

			public function get_payment_method_data() {
				return [
					'title'       => $this->get_setting( 'title' ),
					'description' => $this->get_setting( 'description' ),
					'icon'        => plugins_url( 'assets/bluefin.png', WC_BLUEFIN_MAIN_FILE ),
					'supports'    => $this->get_supported_features(),
				];
			}
		} );
	} );
});

// High-Performance Order Storage
// See: https://developer.woocommerce.com/docs/features/high-performance-order-storage/#incompatible-plugins

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		// \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
		// 	'cart_checkout_blocks', WC_BLUEFIN_MAIN_FILE, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables', WC_BLUEFIN_MAIN_FILE, true
		);
		// \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WC_BLUEFIN_MAIN_FILE, true );
	}
});
