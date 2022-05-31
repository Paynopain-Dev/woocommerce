<?php
require_once('../../../wp-load.php');
global $woocommerce;

$paylandId = wc()->session->get('paylands_id');
$order_id = wc()->session->get('order_id');

if ($paylandId && $order_id) {
    $order = wc_get_order($order_id);
    $order->payment_complete();
    $order->reduce_order_stock();

    /** Configured Pay type */
    $paylands_gateway 		= WC()->payment_gateways->payment_gateways()['paylands'];
    $pay_type 				= $paylands_gateway->get_option( 'pay_type' );
    switch ( $pay_type ) {
        case 'withhold_payment':
            $order->add_order_note( 'El cobro se realizará cuando el comercio envíe el/los producto/s. ¡Gracias!', true );
            $order->add_order_note( 'Paylands uuid: ' . $paylandId, false );
            // if payment is diferred, add paylands uuid as meta to use it in the future confirmation.
            if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
                update_post_meta( $order_id, '_paylands_uuid', $paylandId );
            } else { 
                $order = new WC_Order( $order_id ); 
                $order->update_meta_data( '_paylands_uuid', $paylandId );
                $order->save_meta_data();
            }
            break;

        case 'instant_payment':
            $order->add_order_note( 'Tu orden ha sido pagada. ¡Gracias!', true );
            break;
            
        default:
            $order->add_order_note( 'Tu orden ha sido pagada. ¡Gracias!', true );
            break;
    }
    $woocommerce->cart->empty_cart();

    wc()->session->__unset( 'paylands_id');
    wc()->session->__unset( 'order_id');
    header('location: ' . $order->get_checkout_order_received_url());
} else {
    wc_add_notice(__('Payment has been declined.'), 'error');
    header('location: ' . wc_get_checkout_url());
}
exit();