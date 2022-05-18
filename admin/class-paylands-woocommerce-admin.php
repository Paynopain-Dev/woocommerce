<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://paylands.com/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/admin
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Paylands_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Paylands_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/paylands-woocommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Paylands_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Paylands_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/paylands-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	
	
	
	/**
	 * Fired when WC order status has changed
	 * 
	 */
	public function order_status_changed( $order_id ) {
		$paylands_gateway 		= WC()->payment_gateways->payment_gateways()['paylands'];
		$order 					= wc_get_order( $order_id );

		$order_status			= $order->data['status'];
		$order_uuid				= $order->get_meta('_paylands_uuid', true );
		$pay_type 				= $paylands_gateway->get_option( 'pay_type' );
		$payment_order_status 	= $paylands_gateway->get_option( 'payment_order_status' );
		$signature 				= $paylands_gateway->get_option( 'signature' );

		
		/** Pay type "WITHHOLD PAYMENT" is active */
		if( $pay_type == 'withhold_payment' && $order_uuid){
			
			$data = array(
				'signature' => $signature,
				'order_uuid' => $order_uuid,
			);

			// If the order change to the status as the configured in admin, the withhold amount will be paid.
			if( $order_status == $payment_order_status ){
				// Call to api
				$plOrder = $paylands_gateway->paymentConfirmation($data);

				// Add some notes to admin
				if( in_array($plOrder->order->status,["SUCCESS"]) ){
					$order->add_order_note( 'Paylands: CONFIRMED PAYMENT of order uuid ' . $plOrder->order->uuid, false );
				}else {
					$order->add_order_note( 'Paylands: Something went wrong during confirmation of order uuid ' . $plOrder->order->uuid, false );
				}

			}

			// If the order change to cancelled, we should release the amount that have been withhold
			if( $order_status == 'cancelled' ){
				// Call to api
				$plOrder = $paylands_gateway->paymentCancellation($data);

				// Add some notes to admin
				if( in_array($plOrder->order->status,["CANCELLED"]) ){
					$order->add_order_note( 'Paylands: CANCELLED PAYMENT of order uuid ' . $plOrder->order->uuid, false );
				}else {
					$order->add_order_note( 'Paylands: Something went wrong during cancelation of order uuid ' . $plOrder->order->uuid, false );
				}
			}

		}



	}



}
