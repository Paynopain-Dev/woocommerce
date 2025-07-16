<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
		  integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<title><?php echo $data['title'] ?></title>
	<style type="text/css" media="all">
		#pnp-process {
			width: 300px;
			min-height: 500px;
			margin: 0 auto;
		}

		.vertical-center {
			min-height: 100%; /* Fallback for browsers do NOT support vh unit */
			min-height: 100vh; /* These two lines are counted as one :-)       */

			display: flex;
			align-items: center;
		}
	</style>
</head>
<body>
<div id="pnp-process" class="vertical-center">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-sm-12">
				<div class="card align-middle">
					<div class="card text-center">
						<div class="card-header">
							<div>
								<img style="width: 100px;" src="http://paylands.com/wp-content/uploads/2016/06/paylands-logo-dark.png">
							</div>
							<h3 class="panel-title"><?php echo $data['title'] ?></h3>
						</div>
						<div class="card-body">
							<ul class="list-group list-group-flush">
								<?php if ( $data['cards'] ) : ?>
								<?php foreach ( $data['cards'] as $card ) : ?>
									<li class="list-group-item">
										<label for="card-id-<?php echo $card['id'] ?>">
											<input name="card_uui" id="card-id-<?php echo $card['id'] ?>"
												   class="form-check-input" type="radio"
												   value="<?php echo $card['card_uuid'] ?>" onclick="enableButtons()">
											<?php echo $card['brand'] ?> <?php echo $data['ending_in'] ?> .....<?php echo $card['last4'] ?>  <?php echo $data['expires'] ?>
											: <?php echo $card['expire_month'] ?> / <?php echo $card['expire_year'] ?>
										</label>
										<div>
											<button class="btn btn-danger badge badge-danger" id="delete-card" data-id="<?php echo $card['card_uuid'] ?>" disabled>
												<?php echo $data['delete_card'] ?>
											</button>
										</div>
									</li>
								<?php endforeach; ?>
								<?php endif; ?>
								<li class="list-group-item">
									<button type="button" class="btn btn-link" id="add-new-cart">
										<?php echo $data['add_new_card'] ?>
									</button>
								</li>
							</ul>
						</div>
						<div class="card-footer text-muted">
							<button type="button" id="btn-submit" disabled
									class="btn btn-primary btn-lg btn-block">
										<?php echo $data['button_submit'] ?>
									</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
	function disableButtons() {
		$('#btn-submit').attr('disabled','disabled');
		$('#delete-card').attr('disabled','disabled');
		$('#add-new-cart').attr('disabled','disabled');
		$("input").attr('disabled','disabled');
		return true;
	}

	function enableButtons() {
		$('#btn-submit').removeAttr('disabled');
		$('#delete-card').removeAttr('disabled');
		$('#add-new-cart').removeAttr('disabled');
		$('input').removeAttr('disabled');
		return true;
	}

	parent.jQuery('.fancybox-toolbar').hide();

	$("#add-new-cart").on('click', function () {
		var self = $(this);

		$.ajax({
			url: "<?php echo $data['add_card_url'] ?>",
			dataType: 'json',
			method: "GET",
			data: {
				action: 'paylands_payment_form',
				order_id: <?php echo $data['order_id'] ?>,
			},
			beforeSend: function () {
				disableButtons();
				self.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
			},
			success: function (response) {
				if (response['success']) {
					window.location.replace(response['data']['redirect']);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$('#btn-submit').on('click', function () {
		var card_uuid = $("input[name='card_uui']").val();
		var self = $(this);
		$.ajax({
			url: "<?php echo $data['payment_url'] ?>",
			dataType: 'json',
			method: "POST",
			data: {
				action: 'paylands_tokenized',
				card_uuid: card_uuid,
				order_id: <?php echo $data['order_id'] ?>
			},
			beforeSend: function () {
				disableButtons();
				self.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
			},
			success: function (response) {
				if (response['data']['redirect']) {
					window.location.replace(response['data']['redirect']);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$('#delete-card').on('click', function () {
		var self = $(this);
		var id = self.data('id');
		$.ajax({
			url: "<?php echo $data['delete_url'] ?>",
			dataType: 'json',
			method: "POST",
			data: {
				action: 'paylands_delete',
				card_id: id,
			},
			beforeSend: function () {
				disableButtons();
			},
			success: function (response) {
				if (response['success'] === true) {
					self.closest('.list-group-item').fadeOut();
					enableButtons();
					$('#btn-submit').attr('disabled','disabled');
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	})
	</script>
</body>
</html>

