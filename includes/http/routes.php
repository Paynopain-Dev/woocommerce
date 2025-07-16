<?php


$router = new Router( 'paylands_routes' );
$controller = new Paylands_Controller();

$routes = array(
    'paylands_index' 		=> new Route('/paylands-actions', 'paylands-index', 'GET'),
    'paylands_ok'			=> new Route('/paylands-actions/ok', 'paylands-ok', 'GET'),
    'paylands_ko'			=> new Route('/paylands-actions/ko', 'paylands-ko', 'GET'),
    'paylands_callback' 	=> new Route('/paylands-actions/callback', 'paylands-callback', 'POST'),
    'paylands_tokenized'	=> new Route('/paylands-actions/tokenized', 'paylands-tokenized', 'GET', true),
    'paylands_delete'		=> new Route('/paylands-actions/delete', 'paylands-delete', 'GET'),
    'paylands_payment_form' => new Route('/paylands-actions/payment_form', 'paylands-payment-form', 'GET'),
    'paylands_redirect'		=> new Route('/paylands-actions/redirect', 'paylands-redirect', 'POST')
);

Processor::init( $router, $controller, $routes );
