<?php

defined( 'ABSPATH' ) || exit;


class WC_REST_Bluefin_Iframe_Controller extends WC_Bluefin_REST_Base_Controller {

	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/generate_bearer_token',
			[
				'methods'             => WP_REST_Server::CREATABLE, // 'POST',
				'callback'            => [ $this, 'generate_bearer_token' ],
				// adjust for auth as needed
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	public function check_permissions() {
		return is_user_logged_in();
	}

	public function generate_bearer_token( $request ) {

		// $settings = get_option( 'woocommerce_bluefin_gateway_settings', [] ); // Note: json

		$json_body = $request->get_json_params();

		/*
		WC_Bluefin_Logger::log(
				"generate_bearer_token: {$json_body}" // encode
			);
		*/

		$iframe_instance_resp = null;

		$err_message = '';

		try {

			$iframe_instance_resp = WC_Bluefin_API::v4_init_iframe( $json_body );

		} catch ( WC_Bluefin_Exception $err ) {
			$err_message = sprintf( __( 'Iframe Payment Instance error: %s', 'bluefin-payment-gateway' ), $err->getLocalizedMessage() );
			wc_add_notice( esc_html( $err_message ), 'error' );
			// wc_print_notices();

			return new WP_Error(
				'bluefin_error',
				sprintf(
					__( $err_message, 'bluefin-payment-gateway' ),
				)
			);
		}

		return rest_ensure_response(
			[
				// 'settings' => $settings,
				'iframe_instance_resp' => $iframe_instance_resp,
			]
		);
	}
}
