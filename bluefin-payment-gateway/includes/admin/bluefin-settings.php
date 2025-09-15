<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bluefin_settings = [
	'enabled' => [
		'title'   => __( 'Enable/Disable', 'bluefin-payment-gateway' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Bluefin Payment Gateway', 'bluefin-payment-gateway' ),
		'default' => 'yes'
	],
	'title' => [
		'title'       => __( 'Title', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'This controls the title on checkout.', 'bluefin-payment-gateway' ),
		'default'     => __( 'Bluefin Payment Gateway', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
		// 'readonly'    => true,
	],
	'description' => [
		'title'       => __( 'Description', 'bluefin-payment-gateway' ),
		'type'        => 'textarea',
		'description' => __( 'Payment method description.', 'bluefin-payment-gateway' ),
		'default'     => __( 'Pay securely with Bluefin Payment Gateway.', 'bluefin-payment-gateway' ),
	],
	'account_id' => [
		'title'       => __( 'Account Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Account Identifier', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		// 'required'    => true,
	],
	'merchant_api_key_id' => [
		'title'       => __( 'Merchant API Key Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Merchant  API Key Identifier', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
	],
	'merchant_api_key_secret' => [
		'title'       => __( 'Merchant API Key Secret', 'bluefin-payment-gateway' ),
		'type'        => 'password',
		'description' => __( 'Merchant  API Key Secret', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'iframe_config_id' => [
		'title'       => __( 'iFrame Configuration Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'iFrame Configuration used by the Checkout Component. Preconfigure payment methods and their settings', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'use_sandbox' => [
		'title'       => __( 'Use Sandbox Environment', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'By enabling this property, you are using Bluefin PayConex certification enviroment', 'bluefin-payment-gateway' ),
		'label'   => __( 'Enable Sandbox', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => true,
	],
	'use_auth_only' => [
		'title'       => __( 'Authorize only (capture manually in the admin)', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'Upon Order Confirmation, Admin -> Orders -> Select the order to capture and click on Capture', 'bluefin-payment-gateway' ),
		'label'   => __( 'Enable Authorize Only', 'bluefin-payment-gateway' ),
		'default'     => 'no',
		'desc_tip'    => true,
	],

];

return $bluefin_settings;
