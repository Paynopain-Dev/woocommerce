<?php
/**
 * The file that define the Paylands API Client
 *
 * This file have a paylands api client,. Load the the requests
 * and let us to create paylands request.
 *
 * @link       http://paylands.com/contacto/
 * @since      1.0.0
 *
 * @package    Paylands_Woocommerce
 * @subpackage Paylands_Woocommerce/includes/api
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paylands_Api_Client Class
 */
class Paylands_Api_Client {
	const POST_URL = "https://api.paylands.com/v1";
	const SANDBOX = "/sandbox";
	const REDIRECT_URL = "/payment/process/";
	const CREATE_ORDER_URL = "/payment";
	const TOKENIZED_3DS_URL = "/payment/tokenized/";
	const DIRECT_PAYMENT_URL = "/payment/direct";
	const PROFILE = "/api-key/profiles";
	const CUSTOMER = "/customer";
	const CARD = "/payment-method/card";

	/**
	 * PNP User Api key - obtained from User PNP account
	 * @var string $api_key
	 */
	private $api_key;

	/**
	 * PNP User signature - obtained from User PNP account
	 * @var string $signature
	 */
	private $signature;

	/**
	 * PNP User service - obtained from User PNP account
	 * @var string $service
	 */
	private $service;

	/**
	 * Extra config like mode or other stuff that can fit here
	 * @var array
	 */
	private $config;

	/**
	 * The Lang defined by the user.
	 *
	 * @var string
	 */
	private $lang;

	/**
	 * PNPPaylands constructor.
	 * @param string $api_key
	 * @param string $signature
	 * @param string $service
	 * @param array $config
	 */
	public function __construct($api_key, $signature, $service, $config = ['mode' => 'sandbox'], $lang = 'es') {
		$this->api_key = $api_key;
		$this->signature = $signature;
		$this->service = $service;
		$this->config = $config;
		$this->lang = $lang;
	}

	/**
	 * Get Environment
	 * @return string
	 */
	private function getEnvironment() {
		if ($this->config['mode'] == 'sandbox') {
			return 'sandbox';
		}

		return 'prod';
	}

	/**
	 * Get Api URl
	 * @return string
	 */

	private function getApiUrl() {
		$mode = $this->getEnvironment();
		if ($mode == 'sandbox') {
			return self::POST_URL.self::SANDBOX;
		}

		return self::POST_URL;
	}

	/**
	 * @param float $number
	 * @return float
	 */
	private function toCents($number) {
		return floor(100 * $number);
	}

	/**
	 * @param string $url
	 * @param array $data
	 * @return array
	 */
	private function sendRequest($url, $data) {
		// error_log("PNP::REQUEST {$url} => " . json_encode($data, JSON_PRETTY_PRINT), 4);
		// Paylands_Logger::log( "PNP::REQUEST {$url} => " . json_encode($data, JSON_PRETTY_PRINT) );
		$payload_json = json_encode($data);
		$token = $this->api_key;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				"Content-Type: application/json",
				"Authorization: Bearer ".$token,
			)
		);

		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);

		if ( !$response ) {
			throw new Exception(__('Something went wrong processing the request', 'paylands-woocommerce'));
		}

		return $response;
	}

	/**
	 * Get Create Order URL
	 * @return string
	 */
	public function getCreateOrderUrl() {
		return $this->getApiUrl().self::CREATE_ORDER_URL."?lang={$this->lang}";
	}

	/**
	 * Get Direct Payment URl
	 * @return string
	 */
	public function getDirectPaymentUrl() {
		return $this->getApiUrl().self::DIRECT_PAYMENT_URL;
	}

	/**
	 * Get Tokenized 3DS URL
	 * @return string
	 */
	public function getTokenized3DSUrl() {
		return $this->getApiUrl().self::TOKENIZED_3DS_URL;
	}

	/**
	 * Get Redirect URL
	 * @return string
	 */
	public function getRedirectUrl() {
		return $this->getApiUrl().self::REDIRECT_URL;
	}

	/**
	 * Get Ip Address
	 * @return string
	 */
	public function getRealIpAddr() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * @param float $amount
	 * @param string $operative
	 * @param string $customer_ext_id
	 * @param string $description
	 * @param string $additional
	 * @param bool $secure
	 * @param string $url_post
	 * @param string $url_ok
	 * @param string $url_ko
	 * @param string $source_uuid
	 * @return array
	 */
	public function createOrder(
		$amount,
		$operative,
		$customer_ext_id = '',
		$description,
		$additional,
		$secure,
		$url_post,
		$url_ok,
		$url_ko,
		$source_uuid = ''
	) {
		$amount_in_cents = $this->toCents($amount);
		$payload = [
			"amount" => $amount_in_cents,
			"operative" => $operative,
			"signature" => $this->signature,
			"description" => $description,
			"service" => $this->service,
			"secure" => $secure,
			"additional" => $additional,
			"url_post" => $url_post,
			"url_ok" => $url_ok,
			"url_ko" => $url_ko,
		];

		if (!empty($source_uuid)) {
			$payload['source_uuid'] = $source_uuid;
		}

		if (!empty($customer_ext_id)) {
			$payload['customer_ext_id'] = $customer_ext_id;
		}

		$api_url = $this->getCreateOrderUrl();

		return $this->sendRequest($api_url, $payload);
	}

	/**
	 * @param string $customer_ip
	 * @param string $order_uuid
	 * @param string $card_uuid
	 * @return array
	 */
	public function directPayment($customer_ip, $order_uuid, $card_uuid) {
		$payload = [
			"customer_ip" => $customer_ip,
			"order_uuid" => $order_uuid,
			"card_uuid" => $card_uuid,
			"signature" => $this->signature,
		];

		$direct_payment_url = $this->getDirectPaymentUrl();
		return $this->sendRequest($direct_payment_url, $payload);
	}

	public function paymentProfile() {
		$url = $this->getApiUrl().self::PROFILE;
		$token = $this->api_key;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				"Content-Type: application/json",
				"Authorization: Bearer ".$token,
			)
		);

		$response = json_decode(curl_exec($ch), true);
		curl_close($ch);

		return $response;
	}

	public function retrieveProfile($customer_id) {
		$url = $this->getApiUrl().self::CUSTOMER . '/profile/' . $customer_id;

		return $this->sendRequest($url, ['external_id' => (string)$customer_id]);
	}

	public function createProfile($customer_id) {
		$url = $this->getApiUrl().self::CUSTOMER;
		$data = array(
			'customer_ext_id' => (string)$customer_id,
			'signature' => $this->signature
		);

		return $this->sendRequest($url, $data);
	}
}
