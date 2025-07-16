<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $data['title'] ?></title>
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Passion+One:900" rel="stylesheet">

    <style media="all" type="text/css">
        * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            padding: 0;
            margin: 0;
        }

        #pnp {
            position: relative;
            height: 100vh;
            margin: 0 auto;
            max-width: 700px;
            max-height: 400px;
        }

        .container {
            position: relative;
            width: 100%;
            padding-left: 30px;
        }

        .container .item--face {
            float:left;
            top: 0;
            width: 40%;
        }

        .container .item--text {
            float:right;
            margin-top: 20%;
            width: 60%;
        }

        .container .item--face h1 {
            font-family: 'Passion One', cursive;
            color: #ef0643;
            font-size: 150px;
            width: 150px;
            letter-spacing: 15.5px;
        }

        .container h2 {
            font-family: 'Raleway', sans-serif;
            color: #292929;
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2.5px;
        }

        .container p {
            font-family: 'Raleway', sans-serif;
            font-size: 14px;
            font-weight: 400;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }

        .btn {
            background-color: #ef0643;
            padding: 12px 30px;
            color: #FFF;
            cursor: pointer;
            border-radius: 4px;
            display: inline-block;
        }
        #retry-button {
            float:left;
        }

        #go_back {
            float:right;
            margin-right:10px;
        }
    </style>
</head>
<body>
<div id="pnp">
    <div class="container">
        <div class="item--face">
            <h1><?php echo $data['face'] ?></h1>
        </div>
        <div class="item--text">
            <div class="text">
                <h2><?php echo $data['headline'] ?></h2>
                <p><?php echo $data['description'] ?></p>
                <?php if ( $data['form_status'] === 'ko' ) : ?>
                    <button onClick="retryPayment()" class="btn" id="retry-button">
                        <?php echo $data['retry'] ?>
                    </button>
                    <button onClick="parent.location.href='<?php echo $data['home_url']; ?>'" id="go_back" class="btn">
                        Go back to the shop
                    </button>
                    <div style="clear:both;"></div>
                <?php endif; ?>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">

    parent.jQuery("input[name='payland_payment_complete']").val("<?php echo $data['form_status'] ?>");
    parent.jQuery('.fancybox-toolbar').show();
    parent.jQuery('.fancybox-button--close').hide();

    function retryPayment() {
        $('#retry-button').attr('disable', true).text("<?php echo $data['loading'] ?>");

        window.location.href = "<?php echo $data['home_url'] . '/?paylands_routes=paylands_redirect&order_id=' . $data['order_id'] ?>";
		parent.jQuery('.fancybox-content')[0].style = "width: 700px;display: inline-block;"
    }
    </script>
</body>
</html>
