<?php
/**
 * WooCommerce Bluefin Gateway Uninstall
 *
 * @version  x.x.x
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Exit if uninstall not called from WordPress.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'WC_REMOVE_ALL_DATA' ) || true !== WC_REMOVE_ALL_DATA ) {
	// Remove OAuth keys from the settings
	$settings = get_option( 'woocommerce_bluefin_gateway_settings', [] );
	if ( is_array( $settings ) ) {
		// Disable the gateway before removing the plugin
		$settings['enabled'] = 'no';

		unset( $settings['account_id'] );
		unset( $settings['merchant_api_key_id'], $settings['merchant_api_key_secret'] );
	}
	update_option( 'woocommerce_bluefin_gateway_settings', $settings );

} else {
	// remove ALL plugin settings.
	delete_option( 'woocommerce_bluefin_gateway_settings' );
}
