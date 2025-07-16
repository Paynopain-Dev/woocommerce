<?php

/**
 * Define the Paylands Gateway Loader
 *
 * Load the Paylands Gateway and registe it into
 * WooCommerce.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define the Paylands Gateway Loader
 *
 *
 * @since      1.0.0
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Gateway_Loader {

	/**
	 * Paylands Payment Gateway Init.
	 *
	 * Registe the Paylands Instance for WooCommerce
	 */
	public function paylands_payment_gateway_init() {
		new Paylands_Gateway();
	}

	/**
	 * Add Paylands Gateway Instance to the WooCommerce
	 * Payment Method Class list.
	 *
	 * @param array $gateways - The Current Payment Gateways register in WooCommerce
	 * @return  array - The Payment Methods of WooCommerce with Paylands.
	 */
	public function add_pnp_paylands_gateway( $gateways ) {
		$gateways[] = 'Paylands_Gateway';
		return $gateways;
	}
}
