<?php

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");

// check if the module is activated
/*--- start ---*/


if (empty($_REQUEST['resultCode']) || empty($_REQUEST['merchantOrderId']) || empty($_REQUEST['reference'])) {
	logActivity("Missing parameter resultCode, merchantOrderId or reference when returning to invoice after payment.", 0);
	header('Location: ' . $CONFIG['SystemURL']);
	exit;
}

$order_id = stripslashes($_REQUEST['merchantOrderId']);
$status = stripslashes($_REQUEST['resultCode']);
$reference = stripslashes($_REQUEST['reference']);
$clientId = stripslashes($_REQUEST['clientId']);

if ($status == '00') {				
		logActivity("User has successfully finish the transaction.", $clientId);
		$url = $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $order_id . "&paymentsuccess=true";		
}else if ($_REQUEST['resultCode'] == '01') {
		logActivity("User has generated the payment, waiting for payment to be done by user.", $clientId);
		$url = $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $order_id;
		header('Location: ' . $url);
}else {		
		logActivity("User has try to pay but seem's there a failing or canceled payment.", $clientId);
		$url = $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true";		
}				

//redirect to invoice with message status
header('Location: ' . $url);
die();
			