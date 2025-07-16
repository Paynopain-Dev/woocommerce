jQuery(document).ready(function($){

	if ( typeof paylands_checkout_js_data === 'undefined' ) return false;

	var order_review = $('#order_review');

	if (order_review) {

		if ( paylands_checkout_js_data.order_payment_method !== paylands_checkout_js_data.payment_method_slug ) {
			return false;
		}

		var order_id = paylands_checkout_js_data.order_id;
		$('#payment_method_paylands_woocommerce_gateway').click();

		$.fancybox.open({
			src: paylands_checkout_js_data.home_url + '/?paylands_routes=paylands_index&order_id=' + order_id,
			type: 'iframe',
			iframe : {
                css : {
                    width : '700px'
                }
            },
            beforeLoad: function (instance, slide) {
            	$('.fancybox-toolbar').hide();
            },
			onClose: function() {
				console.log("asdfa");
			},
            beforeClose: function (instance, current) {
                var redirect = $("input[name='payland_payment_complete']").val();
                if (redirect === '' || redirect === 'ko') {
                    return false;
                }

                window.location.href = $("input[name='payland_success_url']").val();

                // return true;
            }
		});
	}
});


