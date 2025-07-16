<?php
/**
 * This file have a helpers used on the Paylands Gatewy Class Ajax.
 *
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes/traits
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Ajax Helper Trait
 */
trait Paylands_Helpers {

	/**
	 * The Paylands Gateway Api Client Instance
	 * @var Object
	 */
	private $api;

	/**
	 * The Paylands Gateway WooCommerce Instance
	 * @var Object
	 */
	private $gateway;

	/**
	 * Validate if the gateway has the settings field saved.
	 * @var boolean
	 */
	private $is_gateway_ready;

	/**
	 * The Paylands Orders Model Instance
	 * @var Object
	 */
	private $orders;

	/**
	 * The Paylands Cards Model Instance.
	 * @var Object
	 */
	private $cards;

	/**
	 * Load Payland Models
	 */
	private function load_models() {
		$this->orders 	= new Paylands_Orders();
		$this->cards 	= new Paylands_Cards();
	}

	/**
	 * Initialize the Paylands Api
	 */
	public function paylands_api_loader() {
		$this->gateway 				= new Paylands_Gateway();
		$this->is_gateway_ready 	= $this->gateway->are_keys_set();
		$this->load_models();

		$this->api = new Paylands_Api_Client(
	        $this->gateway->api_key,
	        $this->gateway->signature,
	        $this->gateway->service,
	        ["mode" => $this->gateway->testmode ? 'sandbox' : 'live'],
	        $this->gateway->form_lang
	    );
	}

	/**
	 * Get Customer Id
	 *
	 * If is user logged in, load return the user id, but if not
	 * just return a random uniqid
	 *
	 * @return integer - The customer id
	 */
	public function get_customer_id() {
		return is_user_logged_in() ? get_current_user_id() : '';
	}

	/**
	 * Get Actions URL for Paylands Responses.
	 *
	 * @return array - The Action URLs
	 */
	private function get_actions_url( $id ) {
		if ( get_option( 'permalink_structure' ) ) {
			return array(
	    		'callback'		=> get_site_url() . "/wp-json/paylands-woocommerce/v1/callback",
	    		'ok' 			=> get_site_url() . "/paylands-actions/ok?id={$id}",
	    		'ko' 			=> get_site_url() . "/paylands-actions/ko?id={$id}",
	    		'tokenized' 	=> get_site_url() . "/wp-admin/admin-ajax.php",
	    		'payment_form' 	=> get_site_url() . "/wp-admin/admin-ajax.php",
	    		'delete'		=> get_site_url() . "/wp-admin/admin-ajax.php",
	    	);
		}

		return array(
    		'callback'		=> get_site_url() . "/index.php?rest_route=/paylands-woocommerce/v1/callback",
    		'ok' 			=> get_site_url() . "/?paylands_routes=paylands_ok&id={$id}",
    		'ko' 			=> get_site_url() . "/?paylands_routes=paylands_ko&id={$id}",
    		'tokenized' 	=> get_site_url() . "/wp-admin/admin-ajax.php",
    		'payment_form' 	=> get_site_url() . "/wp-admin/admin-ajax.php",
    		'delete'		=> get_site_url() . "/wp-admin/admin-ajax.php",
    	);
	}

	/**
     * Hooks a function on to a specific action.
     *
     * @param     $tag
     * @param     $function
     * @param int $priority
     * @param int $accepted_args
     */
    public function action( $tag, $function, $priority = 10, $accepted_args = 1 ) {
        add_action( $tag, [ $this, $function ], $priority, $accepted_args );
    }

    /**
     * Hooks a function on to a specific filter.
     *
     * @param     $tag
     * @param     $function
     * @param int $priority
     * @param int $accepted_args
     */
    public function filter( $tag, $function, $priority = 10, $accepted_args = 1 ) {
        add_filter( $tag, [ $this, $function ], $priority, $accepted_args );
    }

    /**
     * Verify request nonce
     *
     * @param  string  the nonce action name
     *
     * @return void
     */
    public function verify_nonce( $action ) {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'kindhumans' ) );
        }
    }

    /**
     * Wrapper function for sending success response
     *
     * @param  mixed $data
     *
     * @return void
     */
    public function send_success( $data = null ) {
        wp_send_json_success( $data );
    }

    /**
     * Wrapper function for sending error
     *
     * @param  mixed $data
     *
     * @return void
     */
    public function send_error( $data = null ) {
        wp_send_json_error( $data );
    }

    /**
     * Method/Shortcut to return a response as content/type application json
     *
     * @param  array $data - The data sent from method.
     * @return json - data in json format {}
     */
    private function response_json( $data ) {
    	header("Content-Type: application/json");
    	echo json_encode( $data );
    }
}
