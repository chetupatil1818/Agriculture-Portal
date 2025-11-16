<?php
	require_once "stripe-php-master/init.php";
	require_once "products.php";

	$stripeDetails = array(
		"secretKey" => "sk_test_51R7syj2MzV0AWEp03PtvyszO2Afmc2MgJKpiC1uLb2HTvVqyo8ou2pnFuiljwIVBVE1Fxcu7CQwyLNHMCdc49dST002tyRqX0d",  //Your Stripe Secret key
		"publishableKey" => "pk_test_51R7syj2MzV0AWEp0sOParjj0Q9FRRPGsMMYBux3unom0ye8A6ia6Gwm88vSYtT0UEHRPMJvHcxs7Qtq04NO017GB00yP06lxhl"  //Your Stripe Publishable key
	);

	// Set your secret key: remember to change this to your live secret key in production
	// See your keys here: https://dashboard.stripe.com/account/apikeys
	\Stripe\Stripe::setApiKey($stripeDetails['secretKey']);

	
?>
