<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Not using WC_Payment_Token as it is showing these, by default, in the checkout session

class WC_Payment_Token_Bluefin {
	//  extends WC_Payment_Token {

	/** @protected string Token Type String */
	protected $type = 'Bluefin';


	protected $token;

	protected $_user_id;


	public function __construct() { }


	public function set_token( $bf_token_reference ) {
		$this->token = $bf_token_reference;
	}

	public function set_user_id( $user_id ) {
		$this->_user_id = $user_id;
	}

	public function get_token() {
		return $this->token;
	}

	public function get_user_id() {
		return $this->_user_id;
	}

	// TODO? set_expiry, last_four, etc. IF NEEDED
	public function save() {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_bluefin_payment_gateway_reference_tokens',
			[
				'customer_id' => $this->get_user_id(),
				'token'       => $this->get_token(),
			]
		);
	}

	public static function get_tokens( $customer_id ) {
		global $wpdb;

		// WC_Bluefin_Logger::log( "SELECT * FROM {$wpdb->prefix}woocommerce_bluefin_payment_gateway_reference_tokens WHERE customer_id = {$customer_id}" );

		$results = $wpdb->get_results( "SELECT token FROM {$wpdb->prefix}woocommerce_bluefin_payment_gateway_reference_tokens WHERE customer_id = {$customer_id}", OBJECT );

		return $results;
	}
}
