<?php
require_once('../../../wp-load.php');

$json = file_get_contents('php://input');
$data = json_decode($json);
if ($data) {
    $data_order = $data->order;
    if ($data_order){
        
        if($data_order->status == 'SUCCESS' || $data_order->status == 'PENDING_CONFIRMATION') {
            $order = wc_get_order($data_order->uuid);
    
            switch ( $data_order->status ) {
                case 'PENDING_CONFIRMATION':
                    $order->add_order_note( 'El cobro se realizará cuando el comercio envíe el/los producto/s. ¡Gracias!', true );
                    $order->add_order_note( 'Paylands uuid: ' . $data_order->uuid, false );
                    // if payment is deferred, add paylands uuid as meta to use it in the future confirmation.
                    if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
                        update_post_meta( $data_order->uuid, '_paylands_uuid', $data_order->uuid );
                    } else { 
                        $order = new WC_Order( $data_order->uuid ); 
                        $order->update_meta_data( '_paylands_uuid', $data_order->uuid );
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
        }

    }
}
echo 'OK';
exit();