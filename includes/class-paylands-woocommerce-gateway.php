<?php

/**
 * The file that define the Payland Gateway for WooCommerce
 *
 * This file load the Payland Gateway class.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Payland Gateway.
 *
 * This define the payland gateway, load the required actions in the checkout
 * page and process the payments via paylands
 *
 * @since      1.0.0
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Gateway extends WC_Payment_Gateway {

	/**
	 * Payland Gateway Constructor
	 */
	public function __construct() {
		// Define the gateway stuffs.
		$this->id 					= 'paylands_woocommerce_gateway';
		$this->icon 				= 'https://paylands-web-assets.s3-eu-west-1.amazonaws.com/logos/paylands-woocommerce.png';
		$this->has_fields 			= true;
		$this->method_title 		= __( 'Paylands WooCommerce Gateway', 'paylands-woocommerce' );
		$this->method_description 	= __( 'Official Payment Gateway for Paylands.', 'paylands-woocommerce' );
		$this->support 				= array(
			'products',
			'add_payment_method'
		);

		$this->init_form_fields();
		$this->init_settings();

		// Load Setting Values
		$this->title 			= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->testmode 		= 'yes' === $this->get_option( 'testmode' );
		$this->api_key 			= $this->get_boolean_value( 'pnp_api_key' );
		$this->signature		= $this->get_boolean_value( 'pnp_signature_key' );
		$this->service 			= $this->get_boolean_value( 'pnp_service_key' );
		$this->save_cards		= 'yes' === $this->get_option( 'pnp_save_cards' );
		$this->secure_payment 	= 'yes' === $this->get_option( 'pnp_secure_payments' );
		$this->form_lang 		= $this->get_option( 'form_lang' );
		$this->order_status		= $this->get_option( 'order_status' );

		// Save hook for settings
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);

		// Enqueue for admin and theme.
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'change_order_status_after_payment' ) );
	}

	public function init_form_fields() {

		$fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable/Disable', 'paylands-woocommerce' ),
				'type'		=> 'checkbox',
				'label'		=> __( 'Enable Payland Gateway', 'paylands-woocommerce' ),
				'default'	=> 'no'
			),
			'form_lang'	=> array(
				'title'			=> __( 'Payment Form Language', 'paylands-woocommerce' ),
				'type'			=> 'select',
				'description'	=> __( 'Select the language used in the paylands modal payment form.', 'paylands-woocommerce' ),
				'default'		=> 'es',
				'options'		=> array(
					'en' => __( 'English', 'paylands-woocommerce' ),
					'es' => __( 'Spanish', 'paylands-woocommerce' ),
					'de' => __( 'German', 'paylands-woocommerce' ),
					'pt' => __( 'Portuguese', 'paylands-woocommerce' ),
					'fr' => __( 'French', 'paylands-woocommerce' ),
					'ca' => __( 'Catalan', 'paylands-woocommerce' )
				),
				'desc_tip'		=> true
			),
			'title'	=> array(
				'title'			=> __( 'Title', 'paylands-woocommerce' ),
				'type'			=> 'text',
				'description'	=> __( 'This is the title the user will see', 'paylands-woocommerce' ),
				'default'		=> __('Paylands Gateway', 'paylands-woocommerce'),
				'desc_tip'		=> true
			),
			'description' => array(
				'title'			=> __( 'Description', 'paylands-woocommerce' ),
				'type'			=> 'textarea',
				'description'	=> __( 'Payment method description that the customer will to see on your checkout.', 'paylands-woocommerce' ),
				'default'		=> __( 'Pay with a credit/debit card', 'paylands-woocommerce' ),
				'desc_tip'		=> true
			),
			'instructions' => array(
	            'title'       => __( 'Instructions', 'paylands-woocommerce' ),
	            'type'        => 'textarea',
	            'description' => __( 'Instructions that will be added to the thank you page and emails.', 'paylands-woocommerce' ),
	            'default'     => '',
	            'desc_tip'    => true,
			),
			'order_status' => array(
				'title'			=> __( 'Default Order Status', 'paylands-woocommerce' ),
				'type'			=> 'select',
				'description'	=> __( 'Define the order status when user pay with paylands', 'paylands-woocommerce' ),
				'default'		=> 'default',
				'options'		=> array_merge(array(
					'default'	=> __( 'Default order status', 'paylands-woocommerce' )
				), wc_get_order_statuses() ) ,
				'desc_tip'		=> true
			),
	        'testmode' => array(
				'title'       => __( 'Test mode', 'paylands-woocommerce' ),
				'label'       => __( 'Enable Test Mode', 'paylands-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode using test API keys.', 'paylands-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'test_pnp_api_key' => array(
				'title'       => __( 'Test API Key', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Api Key.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'test_pnp_signature_key' => array(
				'title'       => __( 'Test Signature Key', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Siganture.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'test_pnp_service_key' => array(
				'title'       => __( 'Test User Service', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Service.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'live_pnp_api_key' => array(
				'title'       => __( 'Live API Key', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Api Key.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'live_pnp_signature_key' => array(
				'title'       => __( 'Live Signature Key', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Siganture.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'live_pnp_service_key' => array(
				'title'       => __( 'Live User Service', 'paylands-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Paylands User Service.', 'paylands-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'pnp_save_cards' => array(
				'title'       => __( 'Save Cards', 'paylands-woocommerce' ),
				'label'       => __( 'Enable save cards?', 'paylands-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'The customer will can save their cards?.', 'paylands-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'pnp_secure_payments' => array(
				'title'       => __( 'Secure Payment', 'paylands-woocommerce' ),
				'label'       => __( 'Enable Secure Payment', 'paylands-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Active/Desactive the secure payment option', 'paylands-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			)
		);

		$this->form_fields = apply_filters( 'paylands_gateway_fields', $fields );
	}

	/**
	 * Payment Form shown on checkout page.
	 */
	public function payment_fields() {

		$description = $this->description;
		require dirname( __FILE__ ) . '/views/paylands-form.php';
	}

	/**
	 * Validate fields method
	 * @return [type] [description]
	 */
	public function validate_fields() {
		return true;
	}

	/**
	 * This method fires the main gateway process
	 * after the woocommmerce checkout has been completed.
	 * This process the payment directly.
	 *
	 * @param  integer $order_id - The WooCommerce Order ID
	 * @return array 				The Process Result.
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( 'processing' === $order->get_status() ) {
			return array(
				'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_order_received_url()
			);
		}

		WC()->cart->empty_cart();

		return array(
			'result' 	=> 'success',
			'redirect'	=> $order->get_checkout_payment_url()
		);


	}



	/**
	 * Scripts for admin pages
	 */
	public function admin_scripts() {
		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		wp_enqueue_script(
			'paylands_gateway_admin_scripts',
			PAYLANDS_WOOCOMMERCE_ASSETS . '/js/paylands-admin.js',
			array(), date( 'Ymdhs' ), true
		);
	}

	/**
	 * Scripts for gateway in theme
	 */
	public function payment_scripts() {
		// If Paylands is not enabled fail.
		if ( 'no' === $this->enabled ) {
			Paylands_Logger::log( 'Please enable the Paylands WooCommerce' );
			return;
		}

		// If keys are not set bail.
		if ( ! $this->are_keys_set() ) {
			Paylands_Logger::log( 'The Paylands keys are required' );
			return;
		}

		wp_enqueue_style(
			'fancy_box_styles',
			'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css'
		);

		wp_enqueue_script(
			'fancy_box_script',
			'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js',
			array(), date('Ymdsh'), true
		);

		wp_enqueue_script(
			'paylands_checkout_js',
			PAYLANDS_WOOCOMMERCE_ASSETS . '/js/paylands-checkout.js',
			array(), date( 'Ymdhs' ), true
		);

		if ( isset( $_GET['key'] ) && isset( $_GET['pay_for_order'] ) && $_GET['pay_for_order'] === 'true' ) {
			$order_id = wc_get_order_id_by_order_key( $_GET['key'] );
			$order    = wc_get_order( $order_id );

			$data = array(
				'order_id' 				=> $order_id,
				'payment_method_slug' 	=> $this->id,
				'order_payment_method'  => $order->get_payment_method(),
				'home_url'				=> home_url()
			);

			wp_localize_script( 'paylands_checkout_js', 'paylands_checkout_js_data', $data );
		}
	}

	/**
	 * Checks if keys are set.
	 *
	 * @return bool
	 */
	public function are_keys_set() {
		return ! empty( $this->api_key ) && ! empty( $this->signature ) && ! empty( $this->service );
	}

	/**
	 * Get a boolean value in case the plugin
	 * is in testmode.
	 *
	 * @param  string $slug - The Field slug
	 * @return string        The Option Value
	 */
	private function get_boolean_value( $slug ) {
		$slug = $this->testmode ? "test_{$slug}" : "live_{$slug}";
		return $this->get_option( $slug );
	}

	/**
	 * Update Order status after payment
	 *
	 * @param integer $order_id
	 * @since 3.5.0
	 */
	public function change_order_status_after_payment( $order_id = 0 ) {
		// Only if the user define another order status afte payment complete
		if ( 'default' !== $this->order_status ) {
			$order 		= wc_get_order( $order_id );
			$woo_status = explode( 'wc-', $this->order_status );
			$woo_status = count( $woo_status ) === 2 ? $woo_status[1] : $woo_status[0];

			// Update Order Status
			$order->update_status( $woo_status );
		}
	}
}
