<?php

use Automattic\WooCommerce\Enums\OrderStatus;

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
		$trans_resp = null;

		$err_message = '';
		
		try {
			$json_body = $request->get_json_params();
			
			$order_id = intval($json_body['order_id']);
			
			$order = wc_get_order( $order_id );
			
			$transaction_id = $order->get_meta('bluefinTransactionId');

			$trans_resp = WC_Bluefin_API::v4_capture([
				"transactionId" => $transaction_id,
			]);

			$order->update_status( OrderStatus::COMPLETED, sprintf( __( 'Bluefin Authorization Transaction Captured', 'bluefin-payment-gateway' ) ) );
			$order->save();

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
		} catch (\Throwable $e) {
			$err_message = $e->getMessage()
				. ' on Line ' 
				. strval($e->getLine())
				. ' File: ' . $e->getFile();

			return new WP_Error(
				'bluefin_error',
				sprintf(
				__	( $err_message, 'bluefin-payment-gateway' ),
				)
			);
		}

		return rest_ensure_response([
			'ok'	   => true,
		]);
	}
}
