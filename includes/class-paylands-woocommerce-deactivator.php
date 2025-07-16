<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Woocommerce_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;

		$charset_collate 	= $wpdb->get_charset_collate();
		$order_table_name 	= $wpdb->prefix . 'paylands_orders';
		$card_table_name	= $wpdb->prefix . 'paylands_cards';

		$wpdb->query( "DROP TABLE $order_table_name" );
		$wpdb->query( "DROP TABLE $card_table_name" );
	}

}
