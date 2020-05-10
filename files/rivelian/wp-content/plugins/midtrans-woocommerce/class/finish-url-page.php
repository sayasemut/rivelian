<?php 
/**
 * Customizable payment finish page, for internet banking channel, mainly BCA KlikPay
 * Output HTML that display payment result of an internet banking transactions
 * based on order/transaction id found in GET/POST request
 */

// reference: https://www.cloudways.com/blog/creating-custom-page-template-in-wordpress/
try {
	if(isset($_GET['id'])){ // handler for BCA_Klikpay finish redirect
		$trx_id = $_GET['id'];
		// Get order from transaction_id meta data
		$order = wc_get_orders( array( '_mt_payment_transaction_id' => $trx_id ) );
		$plugin_id = isset($order) ? $order[0]->get_payment_method() : 'midtrans';
		$midtrans_notification = WC_Midtrans_API::getMidtransStatus($trx_id, $plugin_id);
	}else if(isset($_POST['response'])){ // handler for CIMB CLICKS finish redirect
		$response = preg_replace('/\\\\/', '', $_POST['response']);		
		$midtrans_notification = json_decode($response);	
	}
} catch (Exception $e) {
	error_log('Failed to do Midtrans Get Status: '.print_r($e,true));
	$midtrans_notification = new stdClass();
	$midtrans_notification->transaction_status = 'not found';
}

// OR uncomment this to redirect it to midtrans plugin callback handler
// echo "loading... <script>window.location = '".get_site_url(null, '/')."?wc-api=WC_Gateway_Midtrans&id=".$_GET['id']."'</script>";

get_header(); // WP Header
?>
 
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        // Customize this part
		if($midtrans_notification->transaction_status == 'settlement'){
			// TODO implement what to do when payment success
			?> 
				<h3>Payment Success!</h3>
				<hr>
				<p>We have received your payment, your order is being processed. Thank you!</p>
			<?php
		}else if($midtrans_notification->transaction_status == 'pending'){
			// TODO implement what to do when payment pending
			?> 
				<?php 
					if($midtrans_notification->payment_type == 'bca_klikpay'){
						// BCA Klikpay specific, all non-settlement are considered failure
				?>
					<h3>Payment Failed</h3>
					<hr>
					<p>Sorry, we are unable to receive your payment.</p>
				<?php 
					}else{
						// Other payment, pending is pending
				?>
					<h3>Order is Awaiting Your Payment</h3>
					<hr>
					<p>Please complete the payment as instructed earlier. Thank you!</p>
				<?php 
					}
				?>
			<?php
		}else{
			// TODO implement what to do when payment failed
			?> 
				<h3>Payment Is Not Received</h3>
				<hr>
				<p>Your payment is not yet completed. Please complete your payment or do another checkout. Thank you!</p>
			<?php
		}
        ?>
    </main><!-- .site-main -->
</div><!-- .content-area -->

<!-- WP Sidebar & Footer -->
<?php 
// get_sidebar(); // uncomment this if you need sidebar
get_footer(); 
?>