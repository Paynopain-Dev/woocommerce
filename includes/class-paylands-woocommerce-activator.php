<?php

/**
 * Fired during plugin activation
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 * @author     Paylands <soporte@paylands.com>
 */
class Paylands_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate 	= $wpdb->get_charset_collate();
		$order_table_name 	= $wpdb->prefix . 'paylands_orders';
		$card_table_name	= $wpdb->prefix . 'paylands_cards';

		$order_table_sql	= "CREATE TABLE IF NOT EXISTS $order_table_name (
         	`id` int(11) NOT NULL AUTO_INCREMENT,
        	`customer_id` VARCHAR (100) NOT NULL,
        	`additional` VARCHAR(500),
        	`order_uuid` VARCHAR(100) NOT NULL,
        	`client_uuid` VARCHAR(100) NOT NULL,
        	`oc_order_id` INT(11) NOT NULL,
        	`refunded` VARCHAR(100),
        	`antifraud` VARCHAR(100),
        	`order_token` VARCHAR(500) NOT NULL,
        	`ip` VARCHAR(100) NOT NULL,
        	`amount` DECIMAL (10, 2) NOT NULL,
        	`currency` VARCHAR(10) NOT NULL,
        	`status` VARCHAR(11) NOT NULL DEFAULT 'SUCCESS',
        	`paid` TINYINT(1) NOT NULL,
        	`service` VARCHAR(15) NOT NULL DEFAULT 'REDSYS',
        	`safe` TINYINT(1) NOT NULL,
        	`raw_order` TEXT NOT NULL,
        	`created_at` DATETIME NOT NULL,
        	`updated_at` DATETIME NOT NULL,
          PRIMARY KEY (`id`)
        ) $charset_collate;";

        $card_table_sql 	= "CREATE TABLE IF NOT EXISTS $card_table_name (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `card_uuid` VARCHAR(100) NOT NULL,
            `client_uuid` VARCHAR(100) NOT NULL,
            `type` VARCHAR(10) NOT NULL DEFAULT 'CREDIT',
            `card_token` VARCHAR(500) NOT NULL,
            `last4` CHAR(4) NOT NULL,
            `brand` VARCHAR(15) NOT NULL,
            `holder` VARCHAR(50) NOT NULL,
            `bin` INT(11) NOT NULL,
            `expire_month` CHAR(2) NOT NULL,
            `expire_year` CHAR(2) NOT NULL,
            `bank` VARCHAR(100),
            `raw_card` TEXT NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        $order_table 	= $wpdb->query( $order_table_sql );
        $card_table 	= $wpdb->query( $card_table_sql );
	}

}
