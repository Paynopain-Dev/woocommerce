<?php
/**
 * The file that define the Paylands Ajax Endpoints
 *
 * This file have the ajax endpoints, necessary to use in the
 * checkout and can process the payment.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paylands WooCommerce Ajax Class
 */
class Paylands_WooCommerce_Ajax {

	use Paylands_Helpers;

	/**
	 *  The Class constructor.
	 */
	public function __construct() {

		$hooks = array(
			'paylands_delete',
			'paylands_tokenized',
			'paylands_payment_form'
        );

        // Load the hooks
        foreach ( $hooks as $hook ) {
            $this->action( "wp_ajax_{$hook}", $hook );
            $this->action( "wp_ajax_nopriv_{$hook}", $hook );
        }
	}

	/**
     * Tokenized Payment. This action execute when a use has card registered
     * on the system and will try to pay by card uuid.
     *
     * action url https://site.url/paylands-actions/tokenized.
     * @return void
     */
    public function paylands_tokenized() {
    	if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! isset( $_POST['order_id'] ) || ! isset( $_POST['card_uuid'] ) ) {
			return false;
		}

    	// Load the PNP Payland SDK
    	$this->paylands_api_loader();

    	// Load Order
    	$order 			= wc_get_order( $_POST['order_id'] );
    	$urls 			= $this->get_actions_url( $order->get_id() );
    	$data 			= array();
    	$order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";

    	$payland_order = $this->api->createOrder(
            (string)$order->get_total(),
            "AUTHORIZATION",
            (string)get_current_user_id(),
            $order_names,
            (string)$order->get_id(),
            $this->gateway->secure_payment,
            $urls['callback'],
            $urls['ok'],
            $urls['ko'],
            $_POST['card_uuid']
        );

        // validate if order was created
        if ( empty( $payland_order ) || $payland_order['code'] !== 200 ) {
            // send error page
            $data['redirect'] = $urls['ko'];
        } else {
            $this->orders->save( $payland_order ); // Save Order in db.
            $data['redirect'] = $this->api->getTokenized3DSUrl() . $payland_order["order"]["token"];
        }

        return $this->send_success( $data );
    }

    /**
     * Payment Form Action to show the payland form when the user
     * click in "add new card" from card list.
     *
     * action url https://site.url/modules/gateways/lib/Routes.php?action=paymentForm
     *
     * @return void
     */
    public function paylands_payment_form() {
    	// Load the PNP Payland SDK
    	$this->paylands_api_loader();
    	// Load Invoice.
    	$order 			= wc_get_order( $_GET['order_id'] );
    	$order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";
    	$urls 			= $this->get_actions_url( $order->get_id() );
    	$data 			= array();

    	$paylands_order = $this->api->createOrder(
            (string)$order->get_total(),
            "AUTHORIZATION",
            (string)get_current_user_id(),
            $order_names,
            (string)$order->get_id(),
            $this->gateway->secure_payment,
            $urls['callback'],
            $urls['ok'],
            $urls['ko']
        );

        // validate if order was created
        if ( empty( $paylands_order ) || $paylands_order['code'] !== 200 ) {
            // send error page
            $data['redirect'] = $urls['ko'];
        } else {
            $this->orders->save( $paylands_order ); // Save Order in db.
            $data['redirect'] = $this->api->getRedirectUrl() . $paylands_order["order"]["token"];
        }

        return $this->send_success( $data );
    }

    /**
     * Delete card action
     *
     * action url https://site.url/paylands-actions/delete
     *
     * @return void
     */
    public function paylands_delete() {
    	$this->load_models();

        try {
            $this->cards->delete($_POST['card_id']);
            return $this->send_success(array('success' => true));
        } catch (Exception $e) {
            return $this->send_error(array('success' => false));
        }
    }
}

/**
 * Initialize the Ajax Class
 */
new Paylands_WooCommerce_Ajax();
