<?php
/**
 * Class WC_Bluefin_REST_Base_Controller
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST controller for Bluefin iframes.
 */

class WC_Bluefin_REST_Base_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc_bluefin/v1';

	/**
	 * Verify access.
	 *
	 * Override this method if custom permissions required.
	public function check_permission() {
		return current_user_can( 'manage_woocommerce' );
	}
	*/
}
