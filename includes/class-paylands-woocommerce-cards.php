<?php
/**
 * This file is a class that define the conection with the Paylands Card Table
 *
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Paylands Orders Class
 */
class Paylands_Cards {

	/**
	 * The Table Name saved in the database.
	 * @var string
	 */
	private $table_name;

	/**
	 * The WordPress DB Global Handler.
	 * @var object
	 */
	private $wpdb;

	/**
	 * The Paylands Orders Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb 		= $wpdb;
		$this->table_name 	= $this->wpdb->prefix . 'paylands_cards';
	}

	/**
	 * Get paylands card by customer id.
	 *
	 * @param  integer $id - The Customer ID
	 * @return object       - The Customer Cards
	 */
	public function get_by_customer( $id ) {
		$cards = array();

		if ( is_user_logged_in() ) {
			$cards = $this->wpdb->get_results(
				"SELECT * FROM {$this->table_name} WHERE customer_id = {$id}",
				ARRAY_A
			);
		}

		return $cards;
	}

	public function save( $order ) {
		$card 			= $order['order']['transactions'][0]['source'];
		$query 			= $this->wpdb->prepare(
			"SELECT id FROM $this->table_name WHERE card_token = %s",
			$card['token']
		);
		$saved_cards 	= $this->wpdb->get_results( $query );

		if ( ! $saved_cards ) {

			$raw_card = json_encode( $card );
	        $now = date('Y-m-d H:i:s');

	        $data = array(
	        	'customer_id'	=> $order['order']['customer'],
	        	'card_uuid'		=> $card['uuid'],
	        	'client_uuid'	=> $order['client']['uuid'],
	        	'type'			=> $card['type'],
	        	'card_token'	=> $card['token'],
	        	'holder'		=> $card['holder'],
	        	'bin'			=> $card['bin'],
	        	'last4'			=> $card['last4'],
	        	'brand'			=> $card['brand'],
	        	'expire_month'	=> $card['expire_month'],
	        	'expire_year'	=> $card['expire_year'],
	        	'bank'			=> $card['bank'],
	        	'raw_card'		=> $raw_card,
	        	'created_at'	=> $now,
	        	'updated_at'	=> $now
	        );

	        $this->wpdb->insert( $this->table_name, $data );
		}
	}

	public function delete( $id ) {
		$condition = array( 'card_uuid' => $id );
		$this->wpdb->delete( $this->table_name, $condition );
	}
}
