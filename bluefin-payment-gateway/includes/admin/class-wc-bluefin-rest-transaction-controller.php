<?php

defined( 'ABSPATH' ) || exit;


class WC_REST_Bluefin_Transaction_Controller extends WC_Bluefin_REST_Base_Controller {

	public function register_routes() {

		register_rest_route($this->namespace,
			'/capture_transaction', 
			[
				'methods' => WP_REST_Server::CREATABLE, // 'POST',
				'callback' => [ $this, 'capture_transaction' ],
				// adjust for auth as needed
				'permission_callback' => [ $this, 'check_permissions' ],
			]);
	}

	public function check_permissions() {
		return current_user_can( 'administrator' );
	}

	public function capture_transaction($request) {

		$resp = null;

		$err_message = '';

		try {

		} catch (WC_Bluefin_Exception $err) {


			$err_message = sprintf( __( 'Payment verification error: %s', 'bluefin-payment-gateway' ), $err->getLocalizedMessage() );
			wc_add_notice( esc_html( $err_message ), 'error' );
			// wc_print_notices();

			return new WP_Error(
				'bluefin_error',
				sprintf(
				__	( $err_message, 'bluefin-payment-gateway' ),
				)
			);
		}
		



		return rest_ensure_response([
			'resp'	   => array(),
		]);
	}
}
