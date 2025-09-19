<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bluefin_settings = [
	'enabled'                            => [
		'title'   => __( 'Enable/Disable', 'bluefin-payment-gateway' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Bluefin Payment Gateway', 'bluefin-payment-gateway' ),
		'default' => 'yes',
	],
	'title'                              => [
		'title'       => __( 'Title', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'This controls the title on checkout.', 'bluefin-payment-gateway' ),
		'default'     => __( 'Bluefin Payment Gateway', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
		// 'readonly'    => true,
	],
	'description'                        => [
		'title'       => __( 'Description', 'bluefin-payment-gateway' ),
		'type'        => 'textarea',
		'description' => __( 'Payment method description.', 'bluefin-payment-gateway' ),
		'default'     => __( 'Pay securely with Bluefin Payment Gateway.', 'bluefin-payment-gateway' ),
	],
	'enable_logging'                     => [
		'title'       => __( 'Enable Logging', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'Enable Logging for debugging purposes. This setting is primarily used in development.', 'bluefin-payment-gateway' ),
		'label'       => __( 'Enable Logging', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => true,
	],
	'account_id'                         => [
		'title'       => __( 'Account Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Account Identifier', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		// 'required'    => true,
	],
	'merchant_api_key_id'                => [
		'title'       => __( 'Merchant API Key Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Merchant  API Key Identifier', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
	],
	'merchant_api_key_secret'            => [
		'title'       => __( 'Merchant API Key Secret', 'bluefin-payment-gateway' ),
		'type'        => 'password',
		'description' => __( 'Merchant  API Key Secret', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'iframe_config_id'                   => [
		'title'       => __( 'iFrame Configuration Identifier', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'iFrame Configuration used by the Checkout Component. Preconfigure payment methods and their settings', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'use_sandbox'                        => [
		'title'       => __( 'Use Sandbox Environment', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'By enabling this property, you are using Bluefin PayConex certification enviroment', 'bluefin-payment-gateway' ),
		'label'       => __( 'Enable Sandbox', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => true,
	],
	'use_auth_only'                      => [
		'title'       => __( 'Authorize only (capture manually in the admin)', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'Upon Order Confirmation, Admin -> Orders -> Select the order to capture and click on Capture', 'bluefin-payment-gateway' ),
		'label'       => __( 'Enable Authorize Only', 'bluefin-payment-gateway' ),
		'default'     => 'no',
		'desc_tip'    => true,
	],

	// Iframe Settings
	'iframe_responsive'                  => [
		'title'       => __( 'Responsive iframe', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'Enable responsive iframe that automatically adjusts height according to the screen', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => true,
	],
	'iframe_width'                       => [
		'title'       => __( 'Iframe Width', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Width of the payment iframe (e.g., 100%, 500px, 50vw)', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'iframe_height'                      => [
		'title'       => __( 'Iframe Height', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Height of the payment iframe (e.g., 600px, 60vh, 400px)', 'bluefin-payment-gateway' ),
		'default'     => __( '', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	'iframe_timeout'                     => [
		'title'       => __( 'Iframe Timeout', 'bluefin-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Iframe Timeout in seconds', 'bluefin-payment-gateway' ),
		'default'     => __( '600', 'bluefin-payment-gateway' ),
		'desc_tip'    => true,
	],
	// Payment Methods of the Iframe
	'use_card_payment'                   => [
		'title'       => __( 'Credit/Debit Card', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( '', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => false,
	],
	/*
	'use_ach_payment'           => [
		'title'       => __( 'ACH (Bank Transfer)', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( '', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => false,
	],
	*/
	'use_google_pay'                     => [
		'title'       => __( 'Google Pay', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( '', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => false,
	],
	'use_mastercard_click_to_pay'        => [
		'title'       => __( 'Mastercard Click to Pay', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( '', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => false,
	],
	// TODO:  ["Plugins.Payments.Bluefin.Fields.PaymentMethod.Required"] = "At least one payment method must be selected."

	// 3D Secure Settings
	'use_three_d_secure'                 => [
		'title'       => __( 'Use 3D Secure', 'bluefin-payment-gateway' ),
		'type'        => 'checkbox',
		'description' => __( 'Use 3D Secure for the Checkout Component, Card Payment Method. If this setting is enabled, the threeDSecureInitSettings below must be configured according to your needs. Note that this setting is required if cardSettings.threeDSecure is defined as \"required\".', 'bluefin-payment-gateway' ),
		'label'       => __( 'Enable 3D Secure', 'bluefin-payment-gateway' ),
		'default'     => 'yes',
		'desc_tip'    => true,
	],

	'three_d_secure_trans_type'          => [
		'title'       => __( '3DS Transaction Type', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'type'        => 'select',
		'description' => __( 'Each option provides context about the nature of the transaction, helping to ensure accurate processing and risk assessment.', 'bluefin-payment-gateway' ),
		'default'     => 'GOODS_SERVICE_PURCHASE',
		'desc_tip'    => true,
		'options'     => [
			'GOODS_SERVICE_PURCHASE' => __( 'GOODS_SERVICE_PURCHASE', 'bluefin-payment-gateway' ),
			'CHECK_ACCEPTANCE'       => __( 'CHECK_ACCEPTANCE', 'bluefin-payment-gateway' ),
			'ACCOUNT_FUNDING'        => __( 'ACCOUNT_FUNDING', 'bluefin-payment-gateway' ),
			'QUSAI_CASH_TRANSACTION' => __( 'QUSAI_CASH_TRANSACTION', 'bluefin-payment-gateway' ),
			'PREPAID_ACTIVATION'     => __( 'PREPAID_ACTIVATION', 'bluefin-payment-gateway' ),
		],
	],

	'three_d_secure_delivery_time_frame' => [
		'title'       => __( 'Delivery Time Frame', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'type'        => 'select',
		'description' => __( 'As the setting name suggests, this is the time for the goods to be delivered. The descriptions are pretty much self-explanatory given the options.', 'bluefin-payment-gateway' ),
		'default'     => 'ELECTRONIC_DELIVERY',
		'desc_tip'    => true,
		'options'     => [
			'ELECTRONIC_DELIVERY'        => __( 'ELECTRONIC_DELIVERY', 'bluefin-payment-gateway' ),
			'SAME_DAY_SHIPPING'          => __( 'SAME_DAY_SHIPPING', 'bluefin-payment-gateway' ),
			'OVERNIGHT_SHIPPING'         => __( 'OVERNIGHT_SHIPPING', 'bluefin-payment-gateway' ),
			'TWO_DAYS_OR_MOSRE_SHIPPING' => __( 'TWO_DAYS_OR_MOSRE_SHIPPING', 'bluefin-payment-gateway' ),
		],
	],

	'three_d_secure_challenge_indicator' => [
		'title'       => __( '3D Secure Challenge Indicator', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'type'        => 'select',
		'description' => __( 'Indicates whether a challenge is preferred, mandated, or requested for the transaction.', 'bluefin-payment-gateway' ),
		'default'     => 'NO_PREFERENCE',
		'desc_tip'    => true,
		'options'     => [
			'NO_PREFERENCE'              => __( 'NO_PREFERENCE', 'bluefin-payment-gateway' ),
			'PREFER_NO_CHALLENGE'        => __( 'PREFER_NO_CHALLENGE', 'bluefin-payment-gateway' ),
			'PREFER_A_CHALLENGE'         => __( 'PREFER_A_CHALLENGE', 'bluefin-payment-gateway' ),
			'OVERWRITE_NO_CHALLENGE'     => __( 'OVERWRITE_NO_CHALLENGE', 'bluefin-payment-gateway' ),
			'REQUIRES_MANDATE_CHALLENGE' => __( 'REQUIRES_MANDATE_CHALLENGE', 'bluefin-payment-gateway' ),
		],
	],

	'three_d_secure_reorder_indicator'   => [
		'title'       => __( 'Reorder Indicator', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'type'        => 'select',
		'description' => __( 'This setting indicates whether the order is new or was ordered before.', 'bluefin-payment-gateway' ),
		'default'     => 'FIRST_TIME_ORDERED',
		'desc_tip'    => true,
		'options'     => [
			'FIRST_TIME_ORDERED' => __( 'FIRST_TIME_ORDERED', 'bluefin-payment-gateway' ),
			'REORDER'            => __( 'REORDER', 'bluefin-payment-gateway' ),
		],
	],

	'three_d_secure_shipping_indicator'  => [
		'title'       => __( 'Shipping Indicator', 'bluefin-payment-gateway' ),
		'label'       => __( '', 'bluefin-payment-gateway' ),
		'type'        => 'select',
		'description' => __( 'Specifies the type of Shipping', 'bluefin-payment-gateway' ),
		'default'     => 'BILLING_ADDRESS',
		'desc_tip'    => true,
		'options'     => [
			'BILLING_ADDRESS'           => __( 'BILLING_ADDRESS', 'bluefin-payment-gateway' ),
			'MERCHANT_VERIFIED_ADDRESS' => __( 'MERCHANT_VERIFIED_ADDRESS', 'bluefin-payment-gateway' ),
			'NOT_BILLING_ADDRESS'       => __( 'NOT_BILLING_ADDRESS', 'bluefin-payment-gateway' ),
			'SHIP_TO_STORE'             => __( 'SHIP_TO_STORE', 'bluefin-payment-gateway' ),
			'DIGITAL_GOODS'             => __( 'DIGITAL_GOODS', 'bluefin-payment-gateway' ),

			'TRAVEL_AND_EVENT_TICKETS'  => __( 'TRAVEL_AND_EVENT_TICKETS', 'bluefin-payment-gateway' ),
			'PICK_UP_AND_GO_DELIVERY'   => __( 'PICK_UP_AND_GO_DELIVERY', 'bluefin-payment-gateway' ),
			'LOCKER_DELIVERY'           => __( 'LOCKER_DELIVERY', 'bluefin-payment-gateway' ),
			'OTHER'                     => __( 'OTHER', 'bluefin-payment-gateway' ),
		],
	],

];

return $bluefin_settings;
