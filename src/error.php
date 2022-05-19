<?php
require_once('../../../wp-load.php');
wc_add_notice(__('Payment has been declined.'), 'error');
header('location: ' . wc_get_checkout_url());
exit();