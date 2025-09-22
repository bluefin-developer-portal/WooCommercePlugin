<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Communicating with Bluefin API.
 */

class WC_Bluefin_API {

	// const ENDPOINT           = 'https://api.payconex.net/';
	// const BLUEFIN_API_VERSION = '2025-08-19';

	const api_postfix = '/api/v4/accounts/';

	private static $endpoint = '';

	private static $use_sandbox = true;

	private static $use_3ds = true;

	private static $account_id = '';

	private static $api_key_secret = '';

	private static $api_key_id = '';

	private static $iframe_config_id = '';

	private static $threeDSecureInitSettings = [];

	public static $use_card_payment            = true;
	public static $use_google_pay              = true;
	public static $use_mastercard_click_to_pay = true;


	public static function set_3ds_settings( $threeDSecureInitSettings ) {
		self::$threeDSecureInitSettings = $threeDSecureInitSettings;
	}

	public static function set_use_3ds( bool $use_3ds ) {
		self::$use_3ds = $use_3ds;
	}

	public static function get_use_3ds() {
		return self::$use_3ds;
	}

	public static function generate_headers() {
		$headers = [];

		$headers['Content-Type'] = 'application/json';

		if ( self::$use_sandbox ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( self::$api_key_id . ':' . self::$api_key_secret );
		} else {
			// TODO: HMAC
		}

		return $headers;
	}


	public static function set_endpoint( $endpoint ) {
		self::$endpoint = $endpoint;
	}

	public static function set_account_id( $account_id ) {
		self::$account_id = $account_id;
	}

	public static function set_api_key_secret( $secret ) {
		self::$api_key_secret = $secret;
	}

	public static function set_api_key_id( $id ) {
		self::$api_key_id = $id;
	}

	public static function set_iframe_config_id( $id ) {
		self::$iframe_config_id = $id;
	}

	public static function set_env( $use_sandbox ) {
		self::$use_sandbox = $use_sandbox;
	}


	public static function POST_request( $url, $request, $headers ) {
		$method = 'POST';

		$request_string = json_encode( $request );

		$response = wp_safe_remote_post(
			$url,
			[
				'method'  => $method,
				'headers' => $headers,
				'body'    => $request_string,
				'timeout' => 60, // in seconds
			]
		);

		// WC_Bluefin_Logger::log(print_r( $response['response']['code'], true ));

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			WC_Bluefin_Logger::error(
				"Bluefin API error: {$method} {$url}",
				[
					'request'  => $request_string,
					'response' => $response,
				]
			);
			throw new WC_Bluefin_Exception( $error_message );

		}

		if ( $response['response']['code'] >= 400 ) {
			WC_Bluefin_Logger::error(
				"Bluefin API error: {$method} {$url}",
				[
					'request'  => $request_string,
					'response' => $response,
				]
			);

			throw new WC_Bluefin_Exception( print_r( $response, true ), __( 'There was a problem communicating with Bluefin Services. Please, contact the admin.', 'bluefin-payment-gateway' ) );
		}

		return json_decode( $response['body'], true );
	}

	public static function v4_init_iframe( $request_json ) {
		$url = self::$endpoint . self::api_postfix . self::$account_id .
				'/payment-iframe/' . self::$iframe_config_id . '/instance/init';
		// WC_Bluefin_Logger::log('URL: ' . $url);

		$user_id = get_current_user_id();

		$tokens = array_map(
			function ( $item ) {
				return $item->token;
			},
			WC_Payment_Token_Bluefin::get_tokens( $user_id )
		);

		$iframe_init_config = [
			'label'                 => 'my-instance-1', // TODO: Make it unique based on customer_id + something?
			'amount'                => $request_json['total_price'],
			'customer'              => $request_json['customer'],
			'timeout'               => $request_json['timeout'],
			'bfTokenReferences'     => $tokens,
			'initializeTransaction' => true,
		];

		$allowed_payment_methods = [];

		if ( self::$use_card_payment ) {
			array_push( $allowed_payment_methods, 'CARD' );
		}
		if ( self::$use_google_pay ) {
			array_push( $allowed_payment_methods, 'GOOGLE_PAY' );
		}
		if ( self::$use_mastercard_click_to_pay ) {
			array_push( $allowed_payment_methods, 'CLICK_TO_PAY' );
		}

		$iframe_init_config['allowedPaymentMethods'] = $allowed_payment_methods;

		if ( self::get_use_3ds() ) {
			$iframe_init_config['threeDSecureInitSettings'] = self::$threeDSecureInitSettings;
		}

		if ( isset( $request_json['shippingaddress'] ) ) {
			$iframe_init_config['shippingAddress'] = $request_json['shippingaddress'];
		}

		$res = self::POST_request( $url, $iframe_init_config, self::generate_headers() );

		return $res;
	}

	public static function v4_refund( $transaction ) {
		$url = self::$endpoint . self::api_postfix . self::$account_id .
				'/payments/' . $transaction['transactionId'] . '/refund';

		$refund_req = [
			'posProfile'  => 'ECOMMERCE',
			'description' => $transaction['description'],
			'amounts'     => $transaction['amounts'],
		];

		$res = self::POST_request( $url, $refund_req, self::generate_headers() );

		return $res;
	}

	public static function v4_capture( $transaction ) {
		$url = self::$endpoint . self::api_postfix . self::$account_id .
				'/payments/' . $transaction['transactionId'] . '/capture';

		$capture_req = [
			'posProfile' => 'ECOMMERCE',
		];

		$res = self::POST_request( $url, $capture_req, self::generate_headers() );

		return $res;
	}

	public static function v4_auth( $transaction ) {
		$url = self::$endpoint . self::api_postfix . self::$account_id .
				'/payments/auth';

		$auth_req = [
			'transactionId'    => $transaction['transactionId'],
			'posProfile'       => 'ECOMMERCE',
			'amounts'          => [
				'total'    => $transaction['total'],
				'currency' => $transaction['currency'],
			],
			'trace'            => [
				'source' => 'WooCommerce Plugin',
			],
			'bfTokenReference' => $transaction['bftokenreference'],
		];

		$res = self::POST_request( $url, $auth_req, self::generate_headers() );

		return $res;
	}

	public static function v4_sale( $transaction ) {
		$url = self::$endpoint . self::api_postfix . self::$account_id .
				'/payments/sale';

		$sale_req = [
			'transactionId'    => $transaction['transactionId'],
			'posProfile'       => 'ECOMMERCE',
			'amounts'          => [
				'total'    => $transaction['total'],
				'currency' => $transaction['currency'],
			],
			'trace'            => [
				'source' => 'WooCommerce Plugin',
			],
			'bfTokenReference' => $transaction['bftokenreference'],
		];

		$res = self::POST_request( $url, $sale_req, self::generate_headers() );

		return $res;
	}
}
