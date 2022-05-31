var paylandsJs  = {
	init: function () {
		let payment = jQuery('input[name="payment_method"]:checked').val();
		if (payment == 'paylands') {
			console.log('>>>>>>>>>>>>>>>>>>>>><',paylands_config);

			setTimeout(function(){
				window.paylands.setMode(paylands_config.mode);
				window.paylands.setTemplate(paylands_config.template);
				window.paylands.initializate(paylands_config.token,"paylands-frame")
			}, 3000);
			jQuery('#paylands-uuid').val('');
		}
	},

	placePaylandsOrder: function () {
		if (paylands_config.save_card && paylands_config.is_user_logged) {
			if (paylands_config.has_cards) {
				//Configurada la opcion de guardar tarjeta y ya hay tarjetas
				let cardNumber = jQuery('input[name="paylands_card"]:checked').val();
				if (typeof cardNumber === 'undefined') {
					//No selecciono ninguna tarjeta
					this.errorAlert('Seleccione una tarjeta para pagar');
					return false;
				} else if (cardNumber == 'custom') {
					//Selecciono una tarjeta personalizada
					window.paylands.storeSourceCard();
					return false;
				} else {
					//Selecciono una tarjeta guardada
					jQuery('#paylands-uuid').val(cardNumber);
					return true;
				}
			} else {
				//Configurada la opcion de guardar tarjeta y no hay tarjetas
				window.paylands.storeSourceCard();
				return false;
			}
		}
		else {
			//No configurada la opcion de guardar tarjeta
			//Crear Orden
			window.paylands.storeSourceCard();
			return false;
		}
	},

	placeWooCommerceOrder: function () {
		jQuery( '#place_order' ).trigger('click');
	},

	errorAlert: function (message) {
		alert(message)
	}
}

/**
 * Catch js errors
 */
jQuery(document).on("error",function(event) {
	console.log(event);
	jQuery('#paylands-uuid').val('');
	paylandsJs.errorAlert('Revise la información de la tarjeta.');
});

/**
 * Catch js errors
 */
jQuery(document).on("errorServer",function(event) {
	jQuery('#paylands-uuid').val('');
	paylandsJs.errorAlert('Revise la información de la tarjeta.');
});

/**
 * Catch woocommerce errors
 */
jQuery(document).on("checkout_error",function(event) {
	jQuery('#paylands-uuid').val('');
});

/**
 * Catch success save card
 */
jQuery(document).on("savedCard",function(event) {
	window.paylands_card = event.originalEvent.data.source.uuid;
	jQuery('#paylands-uuid').val(window.paylands_card);
	paylandsJs.placeWooCommerceOrder();
});

var placeOrder = function() {
	let paylandsCard = jQuery('#paylands-uuid').val();
	if (!paylandsCard) {
		return paylandsJs.placePaylandsOrder();
	} else {
		return true;
	}
};

jQuery(function($){
	var checkout_form = $( 'form.woocommerce-checkout' );
	checkout_form.on( 'checkout_place_order', placeOrder );

	jQuery('form.woocommerce-checkout', function () {
		paylandsJs.init();
	});

	jQuery(document).on('updated_checkout', function () {
		paylandsJs.init();
	});

	jQuery(document).on('custom-form', function () {
		paylandsJs.init();
	});

	jQuery(document).on('click', '.paylands-card', function () {
		let value = jQuery('input[name="paylands_card"]:checked').val();
		if(value == 'custom') {
			jQuery('.custom-form').show();
			paylandsJs.init();
		} else {
			jQuery('.custom-form').hide();
		}
	});

	jQuery(document).on('payment_method_selected', function () {
		let payment = jQuery('input[name="payment_method"]:checked').val();
		if (payment == 'paylands' && window.paylands !== 'undefined') {
			setTimeout(function () {
				paylandsJs.init()
			}, '1000');
		}
	});
});