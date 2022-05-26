<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://paylands.com/
 * @since             1.0.0
 * @package           Paylands_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Paylands Woocommerce
 * Plugin URI:        https://paylands.com/
 * Description:       Acepta pagos con tarjeta a través de Paylands.
 * Version:           1.1.0
 * Author:            Paylands
 * Author URI:        https://paylands.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       paylands-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAYLANDS_WOOCOMMERCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-paylands-woocommerce-activator.php
 */
function activate_paylands_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce-activator.php';
	Paylands_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-paylands-woocommerce-deactivator.php
 */
function deactivate_paylands_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce-deactivator.php';
	Paylands_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_paylands_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_paylands_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-paylands-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_paylands_woocommerce() {

	$plugin = new Paylands_Woocommerce();
	$plugin->run();

}
run_paylands_woocommerce();


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
    add_filter( 'woocommerce_payment_gateways', 'paylands_add_gateway_class' );
    add_action( 'plugins_loaded', 'paylands_init_gateway_class' );
}

function paylands_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Paylands_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
function paylands_init_gateway_class() {

	class WC_Paylands_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
		  public function __construct() {

			$this->id = 'paylands'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom credit card form
			$this->method_title = 'Paylands Gateway';
			$this->method_description = 'Acepta pagos con tarjeta a través de Paylands'; // will be displayed on the options page
		
			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products'
			);
		
			// Method with all the options fields
			$this->init_form_fields();
		
			// Load the settings.
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->testmode = 'sandbox' === $this->get_option( 'environment' );
			$this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
		
			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		
			// We need custom JavaScript to obtain a token
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			
			// You can also register a webhook here
			add_action( 'woocommerce_api_{webhook paylands}', array( $this, 'webhook' ) );
		 }

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
		  public function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Habilitar/Desabilitar',
					'label'       => 'Habilitar Paylands Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => 'Título',
					'type'        => 'text',
					'description' => 'Título que se mostrará en el checkout',
					'default'     => 'Pago Paylands ',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Descripción',
					'type'        => 'textarea',
					'description' => '',
					'default'     => 'Pague con su tarjeta de crédito a través de nuestra pasarela de pago.',
				),
				'api_key' => array(
					'title'       => 'API Key',
					'type'        => 'text'
				),
				'signature' => array(
					'title'       => 'Signature',
					'type'        => 'text',
				),
				'service' => array(
					'title'       => 'Service UUID',
					'type'        => 'text'
				),
                'environment' => array(
                    'title'       => __( 'Modo' ),
                    'label'       => __( 'Environment' ),
                    'type'        => 'select',
                    'options'     => array(
                        'production' => __( 'Producción' ),
                        'sandbox'      => __( 'Sandbox' ),
                    ),
                ),
                'paylands_secure' => array(
                    'title'       => 'Habilitar 3DS',
                    'label'       => ' ',
                    'type'        => 'checkbox',
                    'description' => 'Si se habilita esta opción, las operaciones serán 3DS, lo que significa que el usuario deberá autenticarse contra su banco para finalizar la operación.',
                    'default'     => 'no',
                    'desc_tip'    => true, 
                    'default'     => 'yes',
                ),
                'mit' => array(
                  'title'       => 'Enviar RECURRING para pagos MIT',
                  'label'       => ' ',
                  'type'        => 'checkbox',
                  'description' => 'Si se habilita esta opción, se enviará un parámetro adicional en las operaciones con tarjetas previamente almacenadas. Algunas entidades adquirentes no admiten esta opción',
                  'default'     => 'no',
                  'desc_tip'    => true
                ),
                'save_card' => array(
                    'title'       => 'Guardar Tarjetas',
                    'label'       => ' ',
                    'type'        => 'checkbox',
                    'description' => 'Si se habilita esta opción, los usuarios podrán seleccionar una tarjeta utilizada previamente para realizar un cobro. Esto depende de si la entidad adquirente permite este tipo de operaciones.',
                    'default'     => 'no',
                    'desc_tip'    => true
                ),
                'pay_type' => array(
                    'title'       => __( 'Tipo de pago' ),
                    'label'       => __( 'Pay Type' ),
                    'type'        => 'select',
                    'description' => 'Si se selecciona “Pago inmediato” se realizará el pago en el momento de la compra. Si se selecciona “Retener importe”, se retendrá de la tarjeta en el momento del pago y se cobrará cuando el pedido cambie de estado por una acción del Administrador del site.',
                    'desc_tip'    => true,
                    'options'     => array(
                        'instant_payment'   => __( 'Pago inmediato' ),
                        'withhold_payment'  => __( 'Retener importe' ),
                    ),
                ),
                'payment_order_status' => array(
                    'title'       => __( 'Pago en estado' ),
                    'label'       => __( ' ' ),
                    'type'        => 'select',
                    'description' => 'Si se selecciona como tipo de pago “Retener importe”, el importe retenido se cobrará cuando el admin cambie el estado de pedido al seleccionado en este campo.',
                    'desc_tip'    => true,
                    'options'     => array(
                        'processing' => __( 'Procesando'),
                        'completed'  => __( 'Completado' )
                    ),
                ),
			);
		}


		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {

            if( $this->get_option( 'pay_type' ) == 'withhold_payment' ){
                echo wpautop( wp_kses_post( __('No se te cobrará nada en este momento. El cobro se realizará cuando el comercio envíe el/los producto/s.') ) );
            }

            if (!$this->get_option( 'service' ) && !$this->get_option( 'signature' ) && !$this->get_option( 'api_key' )) {
                echo wpautop( wp_kses_post(__('Configura tu módulo de Paylands')) );
                return;
            }
			// ok, let's display some description before the payment form
			if ( $this->description ) {
				// you can instructions for test mode, I mean test card numbers etc.
				if ( $this->testmode ) {
					$this->description .= ' MODO DE PRUEBA HABILITADO. En el modo de prueba, puede utilizar los números de tarjeta que se enumeran en la <a target="_blank" href="https://paylands.docs.apiary.io/#introduction/tarjetas-de-prueba">documentación.</a>';
					$this->description  = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}
   
			// I will echo() the form, but you can close PHP tags and print it directly in HTML
			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

			// Add this action hook if you want your custom payment gateway to support it
			do_action( 'woocommerce_credit_card_form_start', $this->id );
            $accountId = 0;

            try {
                $accountId = $this->getCustomerId();
                $customer = $this->createCustomer($accountId);
                $token = $customer->Customer->token;
            } catch (Exception $e) {
                echo wpautop(wp_kses_post(__('Paylands Configuration error: Las claves de producción son incorrectas.')));
                return;
            }

            $cards =  $this->getCustomerCards($accountId);
            $hasCards = count($cards) > 0;
            $saveCard = $this->get_option( 'save_card' ) == 'no' ? false : true;

            $configurations = [
                'account_id' => $accountId,
                'customer_id' => $customer,
                'secure' => $this->get_option( 'paylands_secure' ) == 'no' ? false : true ,
                'token' => $token,
                'has_cards' => $hasCards,
                'template' => $this->get_option( 'service' ),
                'mode' => $this->get_option( 'environment' ),
                'save_card' => $saveCard,
                'is_user_logged' => $this->usuario_registrado(),
            ];

            /* Expose js configuration to frontend*/
            echo '<script>
                var paylands_config = ' . json_encode($configurations) . '
            </script>'; 

            /** 
             * This is the modal showed for the user to enter the card details
             */
 
            if($saveCard && $hasCards && $this->usuario_registrado()){ 
                echo '<div class="select-card">
                    <p>Selecciona una tarjeta</p>';
                    foreach ($cards as $card) {
                        echo '<div class="card">
                            <input style="display:inline-block;" type="radio" name="paylands_card" class="paylands-card" value="' . $card->uuid . '">
                            <span class="card-number"><strong>Tarjeta: </strong>' . $card->last4 . '</span>
                            <span class="card-date"><strong>Fecha expiración: </strong>' . $card->expire_month . '/' . $card->expire_year . '</span>
                        </div>';
                    }
                    echo '<div class="card">
                        <p class="option">
                            <input style="display:inline-block;" type="radio" name="paylands_card" class="paylands-card" value="custom">
                            <strong class="card-number">Agregar nueva tarjeta</strong>
                        </p>
                        <div class="custom-form" style="display:none;">
                            <p class="paylands-messages"></p>
                            <div id="paylands-frame"></div>
                        </div>
                    </div>';
                echo '<input type="hidden" name="paylands_uuid" id="paylands-uuid"></div>';  
            } else {
                echo '<div class="custom-form">
                    <p class="paylands-messages"></p>
                    <div id="paylands-frame"></div>
                    <input type="hidden" name="paylands_uuid" id="paylands-uuid">
                </div>';
            }
			do_action( 'woocommerce_credit_card_form_end', $this->id );
			echo '<div class="clear"></div></fieldset>';
		}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
		public function payment_scripts() {
            wp_enqueue_script( 'paylands', "http://ws.paylands.loc/js/v1-iframe.js" );
			wp_enqueue_script( 'woocommerce_paylands' );
		}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields(){
			if( empty( $_POST[ 'billing_first_name' ]) ) {
				wc_add_notice(  'Nombre es requerido', 'error' );
				return false;
			}
			return true;
		}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
			global $woocommerce;
            try {
                $isSecure = $this->get_option( 'paylands_secure' ) == 'no' ? false : true;
                if ($isSecure) {
                    if(!isset($_POST["paylands_uuid"])) {
                        wc_add_notice(__('Ha ocurrido un error al crear el pedido.'), 'error');
                        return;
                    }
                    $order = wc_get_order( $order_id );
                    $response = $this->getPayment($_POST["paylands_uuid"],$order);
                    $tokenResponse = $response->order->token;
                    $paylandId = $response->order->uuid;
                    wc()->session->set( 'paylands_id', $paylandId );
                    wc()->session->set( 'order_id', $order_id );
                    $tokenUrl = $this->getRedirectUrl('payment/tokenized/'.$tokenResponse);

                    return array(
                        'result' => 'success',
                        'redirect' => $tokenUrl
                    );

                    
                } else {
                    if(!isset($_POST["paylands_uuid"])) {
                        wc_add_notice(__('Se produjo un error al crear el pedido.'), 'error');
                        return;
                    }
                    $order = wc_get_order( $order_id );
                    $plOrder = $this->getPayment($_POST["paylands_uuid"], $order);
                    $authData = array(
                        "card_uuid" => $_POST["paylands_uuid"],
                        "order_uuid" => $plOrder->order->uuid
                    );

                    $plOrder = $this->charge($authData);

                    /* The var $plOrder->order->status return SUCCESS when "Instant payment" is configured if is "Withhold payment" return PENDING_CONFIRMATION */
                    if (in_array($plOrder->order->status,["SUCCESS"]) || in_array($plOrder->order->status,["PENDING_CONFIRMATION"])) {
                        // we received the payment
                        $order->payment_complete();
                        $order->reduce_order_stock();

                        switch ( $plOrder->order->status ) {
                            case 'PENDING_CONFIRMATION':
                                $order->add_order_note( 'El cobro se realizará cuando el comercio envíe el/los producto/s. ¡Gracias!', true );
                                $order->add_order_note( 'Paylands uuid: ' . $plOrder->order->uuid, false );
                                // if payment is diferred, add paylands uuid as meta to use it in the future confirmation.
                                if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
                                    update_post_meta( $order_id, '_paylands_uuid', $plOrder->order->uuid );
                                } else { 
                                    $order = new WC_Order( $order_id ); 
                                    $order->update_meta_data( '_paylands_uuid', $plOrder->order->uuid );
                                    $order->save_meta_data();
                                }
                                
                                break;
                    
                            case 'SUCCESS':
                                $order->add_order_note( 'Tu orden ha sido pagada. ¡Gracias!', true );
                                break;
                                
                            default:
                                $order->add_order_note( 'Tu orden ha sido pagada. ¡Gracias!', true );
                                break;
                        }
                        $woocommerce->cart->empty_cart();
                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_return_url( $order )
                        );
                    } else {
                        throw new Exception(__(sprintf(__('La orden ha sido %s'),$plOrder->order->status)));
                    }
                }
            } catch (\Exception $e) {
                wc_add_notice(__($e->getMessage()), 'error');
            }
		}

        /**
         * Create redirect url
         * @param $url
         * @return string
         */
        public function getRedirectUrl($url)
        {
            return $this->getPaylandUrl().$url;
        }

        /**
         *
         * @param $authData
         * @return mixed
         * @throws LocalizedException
         */
        public function paymentConfirmation($authData)
        {
            return $this->request("payment/confirmation",$authData);
        }

        /**
         *
         * @param $authData
         * @return mixed
         * @throws LocalizedException
         */
        public function paymentCancellation($authData)
        {
            return $this->request("payment/cancellation",$authData);
        }

        /**
         *
         * @param $authData
         * @return mixed
         * @throws LocalizedException
         */
        public function charge($authData)
        {
            return $this->request("payment/direct",$authData);
        }

        /**
         *
         * @param $source_id
         * @param $order
         * @return mixed
         * @throws LocalizedException
         */
        public function getPayment($source_id,$order)
        {
            $data = $this->getPaymentData($source_id,$order);
            return $this->request("payment",$data);
        }

        /**
         * @param type $order
         * @return array
         */
        public function getPaymentData($source_id,$order)
        {
            $isSecure = $this->get_option( 'paylands_secure' ) == 'no' ? false : true;
            $saveCard = $this->get_option( 'save_card' ) == 'no' ? false : true;
            if (!$this->usuario_registrado()) {
                $saveCard = false;
            }
            $customerId = $this->getCustomerId();
            $payType    = $this->get_option( 'pay_type' ) == 'withhold_payment' ? 'DEFERRED' : 'AUTHORIZATION';
            $data = array(
                'secure' => $isSecure,
                'save_card' => $saveCard,
                'url_ok' => get_site_url(null,"wp-content/plugins/paylands-module-woocommerce/success.php"),
                'url_ko' => get_site_url(null, "wp-content/plugins/paylands-module-woocommerce/error.php"),
                //old
                'signature' => $this->get_option('signature'),
                'amount' => $order->get_total() * 100,
                'operative' => $payType,
                'customer_ext_id' => $customerId,
                'additional' => 'usuario',
                'service' => $this->get_option('service'),
                "url_post" => get_site_url(null,"wp-content/plugins/paylands-module-woocommerce/update.php"),
                "template_uuid" => $this->get_option('service'),
                "dcc_template_uuid" => "ea0d5f53-5901-4c6b-9d4a-7e7c9b0eeb7e",
                "description" => "Order No. " . $order->get_id(),
                'extra_data' => $this->getExtraData($order)
            );
            if ($isSecure) {
                $data['source_uuid'] =  $source_id;
            }
            return $data;
        }

        /**
         * @param type $quote
         * @return array
         */
        public function getExtraData($quote)
        {
            $address = $this->getAddressData($quote);
            $extraData = array(
                'profile' => $this->getProfileData($quote),
                "address" => $address,
                "shipping_address" => $address,
                "billing_address" => $address
            );

            $mit = $this->get_option( 'mit' ) == 'no' ? false : true;
            if ($mit) {
                $extraData["cof"]["reason"] = "RECURRING";
            }
            return $extraData;
        }

        /**
         * @param type $order
         * @return array
         */
        public function getProfileData($quote)
        {
            $data = $quote->get_data();
            $customerId = $this->getCustomerId();
            $profile = array(
                "first_name" => $data['billing']['first_name'],
                "last_name" => $data['billing']['last_name'],
                'external_id' => $customerId,
                "cardholder_name" => $data['billing']['first_name'],
                "email" => $data['billing']['email'],
                "phone" => array(
                    "number" => $data['billing']['phone'],
                ),
                "home_phone" => array(
                    "number" => $data['billing']['phone'],
                ),
                "work_phone" => array(
                    "number" => $data['billing']['phone'],
                ),
                "mobile_phone" => array(
                    "number" => $data['billing']['phone'],
                ),
            );
            return $profile;
        }


        public function usuario_registrado() {
            $user = wp_get_current_user();
            return $user->exists();
        }
        
        public function usuario_id() {
            $usuario = wp_get_current_user();
            return $usuario->ID;
        }

        /**
         * Return customer id
         * @return string
         */
        public function getCustomerId(): string
        {
           
            if (!$this->usuario_registrado()) {
                return 'wc-customer-cart-' . WC()->cart->get_cart_hash();
            } else {
                return 'wc-customer-' .  $this->usuario_id();
            }

        }

        /**
         *
         * @param type $quote
         * @return array
         */
        public function getAddressData($quote)
        {
            $codes = json_decode(file_get_contents('http://country.io/iso3.json'), true);
            $data = $quote->get_data();
            $country = $data['billing']['country'];
            if (isset($codes[$data['billing']['country']])) {
                $country = $codes[$data['billing']['country']];
            }
            $addressData = array(
                "city" => $data['billing']['city'],
                "country" => $country,
                "address1" => $data['billing']['address_1'],
                "address2" => $data['billing']['address_2'],
                "address3" => $data['billing']['company'],
                "zip_code" => $data['billing']['postcode'],
                "state_code" => $data['billing']['state'],
            );
            return $addressData;
        }


        /**
         *
         * @param $quote
         * @return mixed
         * @throws LocalizedException
         */
        public function createCustomer($customerId)
        {
            $data["customer_ext_id"] = $customerId;
            return $this->request("customer",$data);
        }

        /**
         * Get customer saved cards
         */
        private function  getCustomerCards($customerId)
        {
            $params['status'] = 'VALIDATED';
            $params['unique'] = true;
            $path = 'customer/' . $customerId . '/cards';
            $response = $this->request($path, $params, false);
            return $response->cards;
        }

        /**
         * Return environment url
         * @return string
         */
        public function getPaylandUrl()
        {
            $env = $this->get_option("environment");
            if ($env == 'sandbox') {
                return "http://ws.paylands.loc/v1/sandbox/";
            } else {
                return "http://ws.paylands.loc/v1/";
            }
        }

        /**
         * Call to webservice
         * @param $urlPath
         * @param $data
         * @param bool $post
         * @return mixed
         * @throws Exception
         */
        protected function request($urlPath,$data,$post = true)
        {
            $data['signature'] = $this->get_option('signature');
            $api_key = $this->get_option("api_key");
            $url = $this->getPaylandUrl().$urlPath;
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            if ($post) {
                $headers[] = 'Content-Length: '.  strlen(json_encode($data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

            $result = curl_exec($ch);
            $result = json_decode($result);
            $status = $header_size = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            if($status == 200) {
                if(property_exists($result,"error")) {
                    throw new Exception($result->message, $status);
                } else {
                    return $result;
                }
            } else {
                throw new Exception(__($result->message ." : ".$result->details),$status );
            }
        }
 	}
     
}