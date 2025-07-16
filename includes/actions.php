<?php

// add_action( 'admin_post_nopriv_paylands_callback', 'process_paylands_callback' );
// add_action( 'admin_post_paylands_callback', 'process_paylands_callback' );


// function process_paylands_callback() {
// 	Paylands_Controller::callback();
// }


add_action( 'rest_api_init', function () {
  register_rest_route( 'paylands-woocommerce/v1', '/callback', array(
    'methods' => 'POST',
    'callback' => 'process_paylands_callback',
  ) );

} );

function process_paylands_callback( WP_REST_Request $request ) {
	$controller = new Paylands_Controller();
	return $controller->paylands_callback( $request );
}
