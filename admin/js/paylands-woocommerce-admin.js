(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$( window ).load(function() {
		
		/**
		 * Show/Hide field payment_order_status depend on pay_type selection
		 * 
		 */
		if( $('#woocommerce_paylands_pay_type').length ){

			// Show/hide payment_order_status val depending on stored value
			var payTypeSelected = $('#woocommerce_paylands_pay_type').val();
			if( payTypeSelected == 'withhold_payment' ){
				$('#woocommerce_paylands_payment_order_status').parents('tr').show();
			}else{
				$('#woocommerce_paylands_payment_order_status').parents('tr').hide();
			}

			// Show/hide payment_order_status val depending on selector change
			$('#woocommerce_paylands_pay_type').change(function(){
				var selected =  this.value;
				if( selected == 'withhold_payment' ){
					$('#woocommerce_paylands_payment_order_status').parents('tr').show();
				}else{
					$('#woocommerce_paylands_payment_order_status').parents('tr').hide();
				}
		
			})
		}
		
	});

})( jQuery );
