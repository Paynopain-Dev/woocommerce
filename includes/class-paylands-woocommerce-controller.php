<?php
/**
 * The file that define the Paylands Ajax Controller
 *
 * This file has the routes used to handle all the Paylands
 * views while the user pay with Paylands.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Paylands Controller Class
 */
class Paylands_Controller {

	use Paylands_Helpers;

	/**
	 * Get method to execute a method to load the view data
	 * and stuff actions.
	 *
	 * @param  string $action - The View to load.
	 * @return Execute the method.
	 */
	public function get( $action ) {
		$action = str_replace( '-', '_', $action );
		return $this->{$action}();
	}

	/**
	 * Paylands Index View
	 *
	 * @return [type] [description]
	 */
	public function paylands_index() {
		try {

			$this->paylands_api_loader();

			if ( $this->is_gateway_ready ) {
				// Retrieve the order id to process payment.
				$order 			= wc_get_order( (int)$_GET['order_id'] );
				$total 			= $order->get_total();
				$customer_id 	= $this->get_customer_id();
				$customer_cards = $this->cards->get_by_customer( $customer_id );
				$urls 			= $this->get_actions_url( $order->get_id() );
				$order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";

				if ( ! $customer_cards || ! $this->gateway->save_cards ) {
					try {
						$payland_order = $this->api->createOrder(
							$total,
							"AUTHORIZATION",
							(string)$customer_id, // String format because is neccessary
							$order_names,
							(string)$order->get_id(), // String format because is neccessary
							$this->gateway->secure_payment,
							$urls['callback'],
							$urls['ok'],
							$urls['ko']
						);
					} catch (Exception $e) {
						$error = sprintf(
							__( 'Something failed creating the payland order. Error: %s', 'paylands-woocommerce'),
							$e->getMessage()
						);

						Paylands_Logger::log( $error );
						return header( "Location:" . $urls['ko'] );
					}

					// validate if order was created
					if ( empty( $payland_order ) || $payland_order['code'] !== 200 ) {
						Paylands_Logger::log( $payland_order );
						// load error page
						return header( "Location:" . $urls['ko'] );
					}

					$this->orders->save( $payland_order );
					$redirect_url = $this->api->getRedirectUrl() . $payland_order["order"]["token"] . "?lang={$this->gateway->form_lang}";

					header( "Location:" . $redirect_url ); // redirect to Payment Card Form
					exit();
				}

				$data['success']	   = true;
				$data['payment_url']   = $urls['tokenized'];
				$data['add_card_url']  = $urls['payment_form'];
				$data['delete_url']    = $urls['delete'];
				$data['order_id'] 	   = $order->get_id();
				$data['title'] 		   = __('Choose Card', 'paylands-woocommerce');
				$data['button_submit'] = __('Submit', 'paylands-woocommerce');
				$data['add_new_card']  = __('Add New Card', 'paylands-woocommerce');
				$data['delete_card']   = __('Delete Card', 'paylands-woocommerce');
				$data['ending_in']     = __('Ending in', 'paylands-woocommerce');
				$data['expires']       = __('Expires', 'paylands-woocommerce');
				$data['home_url']	   = home_url();
				$data['cards'] 		   = $customer_cards;

				return $data;
			}
		} catch ( Exception $e ) {
			$data['success'] = false;
			$data['error']   = $e->getMessage();
		}
	}

	/**
     * In case something fails and the users come form the
     * ko page. This url regenerate the order token
     * to can retry the payment.
     *
     * @return void
     */
    public function paylands_redirect() {
        // Load the PNP
        $this->paylands_api_loader();
        // Load Invoice.
        $order 			= wc_get_order( $_GET['order_id'] );
        $urls 			= $this->get_actions_url( $order->get_id() );
        $order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";

        $payland_order = $this->api->createOrder(
            $order->get_total(),
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
        if ( empty( $payland_order ) || $payland_order['code'] !== 200 ) {
            // load error page
            return header("Location:" . $urls['ko']);
        }

        $this->orders->save( $payland_order ); // Save Order sent from API.
        $redirect_url = $this->api->getRedirectUrl() . $payland_order["order"]["token"];
        return header("Location:".$redirect_url); // redirect to Payment Card Form
    }

	/**
	 * Paylands OK action
	 * @return [type] [description]
	 */
	public function paylands_ok() {
		$order = wc_get_order( $_GET['id'] );


		$data = array(
    		'face' 			=> ':)',
    		'title' 		=> __( 'Success Transaction!', 'paylands-woocommerce' ),
    		'headline' 		=> __( 'Success Transaction!', 'paylands-woocommerce' ),
    		'description' 	=> __( 'Thanks for using Paylands, to get order details please close this window and keep navigating through the store.', 'paylands-woocommerce' ),
            'retry' 		=> __( 'Try Again', 'paylands-woocommerce' ),
			'form_status' 	=> 'ok',
			'home_url'		=> home_url(),
    		'success_url' 	=> $order->get_checkout_order_received_url()
    	);

    	return $data;
	}

	public function paylands_ko() {

    	$data = array(
    		'face' 			=> ':(',
    		'title' 		=> __( 'Failed Transaction!', 'paylands-woocommerce' ),
    		'headline' 		=> __( 'Failed Transaction!', 'paylands-woocommerce' ),
    		'description' 	=> __( "Sorry, but the transaction was not successful. Please contact the store's administrator to get more details.", 'paylands-woocommerce' ),
            'retry' 		=> __( 'Try Again', 'paylands-woocommerce' ),
    		'form_status' 	=> 'ko',
    		'loading' 		=> __('Cargando...', 'paylands-woocommerce'),
			'order_id' 		=> $_GET['id'],
			'home_url'		=> home_url()
    	);

    	return $data;
	}

	/**
	 * Paylands Payment Form.
	 *
	 * This actions is fired when the user pay with a
	 * saved card. Is required the order id and the card uuid
	 *
	 * @return array - The URL to redirect.
	 */
	public function paylands_payment_form() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! isset( $_GET['order_id'] ) || ! isset( $_GET['card_uuid'] ) ) {
			return false;
		}

    	$this->paylands_api_loader();

    	// Load Invoice.
    	$order 			= wc_get_order( $_GET['order_id'] );
    	$urls 			= $this->get_actions_url();
    	$order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";
    	$data 			= array();

    	$payland_order = $this->api->createOrder(
            $order->get_total(),
            "AUTHORIZATION",
            (string)get_current_user_id(),
            $order_names,
            (string)$order->get_id(),
            $this->gateway->secure_payment,
            $urls['callback'],
            $urls['ok'],
            $urls['ko'],
            $_GET['card_uuid']
        );

        // validate if order was created
        if ( empty( $payland_order ) || $payland_order['code'] !== 200 ) {
            // send error page
            $data['redirect'] = $urls['ko'];
        } else {
            $this->orders->save( $payland_order ); // Save Order in db.
            $data['redirect'] = $this->api->getRedirectUrl() . $payland_order["order"]["token"];
        }

        return $this->response_json( $data );
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

		if ( ! isset( $_GET['order_id'] ) || ! isset( $_GET['card_uuid'] ) ) {
			return false;
		}

    	// Load the PNP Payland SDK
    	$this->paylands_api_loader();

    	// Load Order
    	$order 			= wc_get_order( $_GET['order_id'] );
    	$urls 			= $this->get_action_url();
    	$data 			= array();
    	$order_names	= "{$order->get_billing_first_name()} {$order->get_billing_last_name()} - {$order->get_id()}";

    	$payland_order = $this->api->createOrder(
            $order->get_total(),
            "AUTHORIZATION",
            (string)get_current_user_id(),
            $order_names,
            (string)$order->get_id(),
            $this->gateway->secure_payment,
            $urls['callback'],
            $urls['ok'],
            $urls['ko'],
            $_GET['card_uuid']
        );

        // validate if order was created
        if ( empty( $payland_order ) || $order['code'] !== 200 ) {
            // send error page
            $data['redirect'] = $urls['ko'];
        } else {
            $this->orders->save( $order ); // Save Order in db.
            $data['redirect'] = $this->api->getTokenized3DSUrl() . $payland_order["order"]["token"];
        }

        return $this->response_json( $data );
    }

	/**
	 * Paylands Callback
	 *
	 * Used to response the paylands callback after
	 * success a payment. This callback is called throught
	 * the WP API REST.
	 *
	 * @param  array $data 	The API Response data
	 */
	public function paylands_callback( $data ) {
		$data = $data->get_params();

		if ( ! empty( $data ) ) {
			$this->paylands_api_loader();

			$order_id = $data['order']['additional'];
			$order = wc_get_order( $order_id );

			switch ( $data['order']['status'] ) {
                case 'SUCCESS':
                    // Save card.
                    if ( $this->gateway->save_cards && is_user_logged_in() ) {
                    	$this->cards->save( $data );
                    }

                    $order->payment_complete();
					$note = __("Payland Charge Completed", "paylands-woocommerce");
                    break;
                case 'CREATED':
                    $note = __("Payland Charge Completed", "paylands-woocommerce");
                    break;
                case 'REFUSED':
                	$note = __("Payment Refused by merchant", "paylands-woocommerce");
                    break;
                case 'ERROR':
                	$note = __("Payment Error", "paylands-woocommerce");
                    break;
                case 'BLACKLISTED':
                	$note = __("Payment Blacklisted", "paylands-woocommerce");
                    break;
                case 'CANCELLED':
                	$note = __("Payment Cancelled", "paylands-woocommerce");
                    break;
                case 'EXPIRED':
                	$note = __("Payment Expired", "paylands-woocommerce");
                    break;
                case 'FRAUD':
                	$note = __("Payment Fraud", "paylands-woocommerce");
                    break;
                case 'PARTIALLY_REFUNDED':
                	$note = _("Payment Partially Refunded", "paylands-woocommerce");
                    break;
                case 'PENDIG_CONFIRMATION':
                	$note = __("Payment Pending of confirmation", "paylands-woocommerce");
                    break;
                case 'REFUNDED':
                	$note = __("Payment Refunded", "paylands-woocommerce");
                    break;
                case 'PENDING_PROCESSOR_RESPONSE':
                	$note = __("Payment Pending Processor Response", "paylands-woocommerce");
                    break;
                case 'PENDING_3DS_RESPONSE':
                	$note = __("Payment Pending 3DS Response", "paylands-woocommerce");
                    break;
                case 'PENDING_CARD':
                	$note = __("Payment Pending Card", "paylands-woocommerce");
                    break;
                default:
                	$note = __("Payment Error", "paylands-woocommerce");
            }

            $order->add_order_note( $note );
		}

		// return $data;
	}
}
