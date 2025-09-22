<?php

use Automattic\WooCommerce\Enums\OrderStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class WC_Gateway_Bluefin extends WC_Payment_Gateway {

	public $capture = true;


	// Note: Resolves "Deprecated: Creation of dynamic property is deprecated" warning
	protected $account_id;

	protected $merchant_api_key_id;

	protected $merchant_api_key_secret;

	protected $enable_logging;

	protected $use_sandbox;

	protected $use_auth_only;

	protected $iframe_config_id;

	protected $use_three_d_secure;



	public function __construct() {
		$this->id                 = 'bluefin_gateway'; // Unique ID
		$this->method_title       = __( 'Bluefin Payment Gateway', 'bluefin-payment-gateway' );
		$this->method_description = __( 'Pay using Bluefin Payment Gateway', 'bluefin-payment-gateway' );
		$this->has_fields         = true;
		$this->supports           = [
			'products',
			'refunds',
		];

		/*
		$this->links = [
				[
					"_type" => "about",
					"url" => "https:\/\/wordpress.org\/plugins\/bluefin-payment-gateway"
				],
								[
					"_type" => "about",
					"url" => "https:\/\/wordpress.org\/plugins\/bluefin-payment-gateway"
				]
			];
			*/

		// $this->errors = [ 'AAA', ];

		// Note: A full path to a file inside a plugin or mu-plugin.
		// See: https://developer.wordpress.org/reference/functions/plugins_url
		$this->icon = plugins_url( '/assets/bluefin.png', WC_BLUEFIN_MAIN_FILE );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->enable_logging = 'yes' === $this->get_option( 'enable_logging', 'yes' );

		$this->account_id              = $this->get_option( 'account_id' );
		$this->merchant_api_key_id     = $this->get_option( 'merchant_api_key_id' );
		$this->merchant_api_key_secret = $this->get_option( 'merchant_api_key_secret' );
		$this->use_sandbox             = 'yes' === $this->get_option( 'use_sandbox', 'yes' );
		$this->iframe_config_id        = $this->get_option( 'iframe_config_id' );
		$this->use_auth_only           = 'yes' === $this->get_option( 'use_auth_only', 'no' );

		$this->use_three_d_secure = 'yes' === $this->get_option( 'use_three_d_secure', 'yes' );

		// WC_Bluefin_Logger::log('DEBUG: ' . $this->id . ' ' . $this->plugin_id);

		$this->setup_static_API();

		add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );

		// Save settings
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			[ $this, 'process_admin_options' ]
		);
		# add_filter( 'woocommerce_gateway_' . $this->id . '_settings_values', [ $this, 'update_onboarding_settings' ] );

		// NOTE: woocommerce_update_options_payment_gateways_ not properly working (not saving unless the user manually goes to the option page) so using this workaround.
		$this->update_options();

		// Note: display error is in the parent class.
		add_action( 'admin_notices', [ $this, 'display_errors' ], 9999 );

		add_action( 'woocommerce_order_item_add_action_buttons', [ $this, 'reverse_auth' ] );

		add_action( 'woocommerce_order_item_add_action_buttons', [ $this, 'capture_payment' ] );

		/*
		function should_refund($order) {
			// WC_Bluefin_Logger::log('should refund: ' . strval($order));
			if($order->get_status() == OrderStatus::ON_HOLD) {
				return false;
			}
			return true;
		}

		// TODO: TBD: DO THIS BUT MAKE YOUR OWN REFUND
		add_filter( 'woocommerce_admin_order_should_render_refunds', 'should_refund');

		*/

		/*
		add_action( 'admin_notices', function() {

		echo '<div class="notice notice-error"><p>';
		echo __( 'My Gateway requires setup before it can be used. Go to settings.', 'my-textdomain' );
		echo '</p></div>';
		});

		*/
	}

	public function setup_static_API() {
		// WC_Bluefin_API
		WC_Bluefin_API::set_account_id( $this->account_id );
		WC_Bluefin_API::set_api_key_id( $this->merchant_api_key_id );
		WC_Bluefin_API::set_api_key_secret( $this->merchant_api_key_secret );

		WC_Bluefin_API::set_endpoint(
			$this->use_sandbox ?
				WC_Bluefin_Defaults::cert_env : WC_Bluefin_Defaults::prod_env
		);

		WC_Bluefin_API::set_iframe_config_id( $this->iframe_config_id );
		WC_Bluefin_API::set_env( $this->use_sandbox );

		// WC_Bluefin_Logger
		WC_Bluefin_Logger::set_logger_enabled( $this->enable_logging );

		$this->set_3ds_settings();

		$this->set_iframe_settings();
	}

	public function set_3ds_settings() {
		$three_d_secure_entries = [
			'three_d_secure_trans_type'          => 'transactionType',
			'three_d_secure_delivery_time_frame' => 'deliveryTimeFrame',
			'three_d_secure_challenge_indicator' => 'threeDSecureChallengeIndicator',
			'three_d_secure_reorder_indicator'   => 'reorderIndicator',
			'three_d_secure_shipping_indicator'  => 'shippingIndicator',
		];

		$mapped_settings = [];

		WC_Bluefin_API::set_use_3ds( $this->use_three_d_secure );

		foreach ( $three_d_secure_entries as $option_name => $API_field ) {
			$mapped_settings[ $API_field ] = $this->get_option( $option_name );
		}

		WC_Bluefin_API::set_3ds_settings( $mapped_settings );
	}

	public function set_iframe_settings() {
		WC_Bluefin_API::$use_card_payment            =
			'yes' === $this->get_option( 'use_card_payment', 'yes' );
		WC_Bluefin_API::$use_google_pay              =
			'yes' === $this->get_option( 'use_google_pay', 'yes' );
		WC_Bluefin_API::$use_mastercard_click_to_pay =
			'yes' === $this->get_option( 'use_mastercard_click_to_pay', 'yes' );
	}


	// MOVED TO THE ADMIN SCRIPTS
	public function validate_account_id_field( $key, $value ) {
		if ( empty( $value ) ) {
			WC_Admin_Settings::add_error( __( 'Bluefin Account Identifier is required.', 'bluefin-payment-gateway' ) );
			return $this->get_option( $key ); // Note: Keep old value
		}
			return $value;
	}

	/*
	// TODO: Required fields for the admin options validation
	public function get_required_settings_keys() {
		return [ 'account_id', 'merchant_api_key_id' ];
	}
	*/


	/*
	public function process_admin_options(): bool {
		$this->errors = [ 'Admin Error', ];
		return false;
		return parent::process_admin_options();
	}
	*/


	public function is_available() {
		return ! $this->needs_setup();
	}


	public function needs_setup(): bool {
		// Note: inverse of is_account_connected()
		return ! $this->is_account_connected();
	}

	public function is_account_connected() {
		return $this->get_option( 'account_id' ) != ''
			&& $this->get_option( 'merchant_api_key_id' ) != ''
			&& $this->get_option( 'merchant_api_key_secret' ) != ''
			&& $this->get_option( 'iframe_config_id' ) != ''
			&& $this->get_option( 'iframe_timeout' ) != '';
	}

	/*
	public function get_setup_help_text() {
		return sprintf(
			__( 'Your API details can be obtained from your <a href="%1$s">PayConex account</a>. Don’t have a PayConex account? <a href="%2$s">Create one.</a>', 'bluefin-payment-gateway' ),
			'<link>',
			'<link>'
		);
	}
	*/


	public function payment_scripts() {
		wp_enqueue_script( 'bluefin-plugin', plugins_url( 'assets/index.js', WC_BLUEFIN_MAIN_FILE ), [ 'wp-element' ], null, true );

		wp_enqueue_script( 'bluefin-sdk', WC_Bluefin_Defaults::script_path, [ 'wp-element' ], null, true );

		wp_localize_script(
			'bluefin-plugin',
			'bluefinPlugin',
			[
				'generate_bearer_token_url' => esc_url_raw( rest_url( 'wc_bluefin/v1/generate_bearer_token' ) ),
				'cc_endpoint'               => $this->use_sandbox ? WC_Bluefin_Defaults::cc_cert : WC_Bluefin_Defaults::cc_prod,
				'iframe_timeout'            => $this->get_option( 'iframe_timeout' ),
				// 'current_customer_id'                => get_current_user_id(),
				'nonce'                     => wp_create_nonce( 'wp_rest' ),
			]
		);
	}



	public function update_options() {
		foreach ( $this->form_fields as $key => $value ) {
			$this->update_option( $key, $this->get_option( $key ) );
		}
	}

	// Admin form fields
	public function init_form_fields() {
		$this->form_fields = require __DIR__ . '/admin/bluefin-settings.php';
	}


	public function can_refund_order( $order ) {
		// NOTE: ADD MORE LOGIC IF THERE ARE MORE REQUIREMENTS
		$total = $order->get_total();
		if ( $total > 0 && boolval( $order->get_meta( 'bluefinTransactionId' ) ) ) {
			return true;
		}
		return false;
	}

	public function reverse_auth( $order ) {
		if ( $order->get_status() == OrderStatus::ON_HOLD ) {
			echo '<button type="button" class="button" id="bluefin_reverse_auth_button"'
				. ' data-order-id=' . strval( $order->get_id() ) . '>'
				. 'Reverse Auth' . '</button>';
		}
	}

	public function capture_payment( $order ) {
		if ( $order->get_status() == OrderStatus::ON_HOLD ) {
			echo '<button type="button" class="button" id="bluefin_capture_button"'
				. ' data-order-id=' . strval( $order->get_id() ) . '>'
				. 'Capture' . '</button>';
		}
	}


	// Process the refund
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order = wc_get_order( $order_id );

		$transaction_id = $order->get_meta( 'bluefinTransactionId' );

		$current_order_amount = $order->get_total() - ( floatval( $order->get_total_refunded() ) - $amount );

		WC_Bluefin_Logger::log( 'process_refund: ' . $transaction_id . ' ' . ' order total: ' . strval( $order->get_total() ) . ' ' . strval( $amount ) . ' reason: ' . $reason . ' total refund: ' . strval( floatval( $order->get_total_refunded() ) ) . ' current amount: ' . strval( $current_order_amount ) );

		try {

			$trans_resp = WC_Bluefin_API::v4_refund(
				[
					'transactionId' => $transaction_id,
					'description'   => $reason,
					'amounts'       => [
						'total'    => strval( $amount ),
						'currency' => 'USD',
					],
				]
			);

			if ( $current_order_amount != $amount ) {
				// Partially Refunded
				// Do add_order_note since the order status is the same in this case
				// TODO: Add that refunded transaction id
				if ( $order->get_status() == OrderStatus::COMPLETED ) { // In the case of the same status
					$order->add_order_note(
						sprintf( __( 'Transaction Partially Refunded via Bluefin', 'bluefin-payment-gateway' ) )
					);
				}
				// $order->update_status( OrderStatus::COMPLETED, sprintf( __( 'Transaction Partially Refunded via Bluefin', 'bluefin-payment-gateway' ) ) );
			} else {
				// Full Refund
				// $order->update_status( OrderStatus::REFUNDED, sprintf( __( 'Transaction Refunded via Bluefin', 'bluefin-payment-gateway' ) ) );
				$order->add_order_note(
					sprintf( __( 'Transaction Refunded via Bluefin', 'bluefin-payment-gateway' ) )
				);
			}

			$order->save();

			// Indicating success
			return true;

		} catch ( WC_Bluefin_Exception $err ) {
			$message = sprintf( __( 'There was a problem initiating a refund via Bluefin: %s', 'bluefin-payment-gateway' ), $err->getLocalizedMessage() );

			return new WP_Error(
				'bluefin_error',
				$message
			);

		}
	}


	private function is_token_vaulted( $trans_resp ) {
		return isset( $trans_resp['bfTokenReference'] );
	}

	// Process the payment
	public function process_payment( $order_id ) {
		$request_data = $_POST;

		// See: https://woocommerce.github.io/code-reference/classes/WC-Order.html
		// get_customer_id()
		$order = wc_get_order( $order_id );

		# WC()->session->set_customer_session_cookie(true);

		WC_Bluefin_Logger::log( 'process_payment: ' . json_encode( $request_data ) );

		# wc_add_notice( __('Payment error:', 'woothemes') . 'failed', 'error' );
		# wc_print_notices();

		// set_status: https://woocommerce.github.io/code-reference/classes/WC-Order.html#method_set_status

		try {
			$trans_resp = null;

			if ( $this->use_auth_only ) {
				$trans_resp = WC_Bluefin_API::v4_auth(
					[
						'transactionId'    => $request_data['transactionid'],
						'total'            => $request_data['total_price'],
						'currency'         => 'USD',
						'bftokenreference' => $request_data['bftokenreference'],
					]
				);

				$order->add_meta_data( 'bluefinTransType', 'auth' );

				$order->update_status( OrderStatus::ON_HOLD, sprintf( __( 'Bluefin Transaction Authorized', 'bluefin-payment-gateway' ) ) );

			} else {
				$trans_resp = WC_Bluefin_API::v4_sale(
					[
						'transactionId'    => $request_data['transactionid'],
						'total'            => $request_data['total_price'],
						'currency'         => 'USD',
						'bftokenreference' => $request_data['bftokenreference'],
					]
				);

				$order->add_meta_data( 'bluefinTransType', 'sale' );

				$order->update_status( OrderStatus::COMPLETED, sprintf( __( 'Bluefin Sale Transaction Processed', 'bluefin-payment-gateway' ) ) );
			}

			$order->add_meta_data( 'bluefinTransactionId', $trans_resp['transactionId'] );

			if ( $this->is_token_vaulted( $trans_resp ) ) {
				// UNUSED UNLESS it is specifically used by a certain merchant
				$token = new WC_Payment_Token_Bluefin();

				$token->set_token( $trans_resp['bfTokenReference'] );

				$token->set_user_id( get_current_user_id() );

				$token->save();

				// $tokens = WC_Payment_Token_Bluefin::get_tokens( get_current_user_id() );

				// WC_Bluefin_Logger::log('WC_Payment_Tokens: ' . json_encode($tokens));

				/*
				// UNUSED UNLESS it is specifically used by a certain merchant
				$token = new WC_Payment_Token_Bluefin();

				$token->set_token( $trans_resp['bfTokenReference'] );
				$token->set_gateway_id( $this->id );

				$token->set_user_id( get_current_user_id() );

				$token->save();

				$tokens = WC_Payment_Tokens::get_customer_tokens( get_current_user_id(), $this->id );

				WC_Bluefin_Logger::log('WC_Payment_Tokens: ' . json_encode($tokens));

				foreach($tokens  as $key=>$value) {
					WC_Bluefin_Logger::log('WC_Payment_Token token: ' . $value->get_token());
					WC_Payment_Tokens::delete($value->get_id());
				}

				*/
			}

			// See: https://woocommerce.github.io/code-reference/classes/Automattic-WooCommerce-Enums-OrderStatus.html
			// See: https://woocommerce.com/document/managing-orders/order-statuses/
			// See: https://woocommerce.github.io/code-reference/classes/WC-Order.html#method_update_status
			// $order->set_status(OrderStatus::COMPLETED);

			// $order->set_status(OrderStatus::ON_HOLD); // NOTE: Never use payment_complete with this

			// $order->add_order_note("AUTHORIZED"); // IF NEEDED

			$order->save();

			// $order->payment_complete();

			// WC_Bluefin_Logger::log('process_payment $order->meta_data: ' . json_encode($order->meta_data));

			// Remove cart.
			// WC()->cart->empty_cart();

			// Redirect to thank you page
			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];

		} catch ( WC_Bluefin_Exception $err ) {
			$message = sprintf( __( 'Payment Error: %s', 'bluefin-payment-gateway' ), $err->getLocalizedMessage() );
			wc_add_notice( esc_html( $message ), 'error' );

			return [
				'result'   => 'failure',
				'redirect' => '',
			];
			// wc_print_notices();
		}
	}
}

// do_action( 'woocommerce_set_cart_cookies',  true );
