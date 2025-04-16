<?php
require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");

require_once __DIR__ . '/../duitku-lib/Duitku.php';

// check if the module is activated
/*--- start ---*/

if (empty($_REQUEST['order_id']) || empty($_REQUEST['paymentMethod']) || empty($_SESSION['duitkuOrder'])) {
	error_log('wrong query string please contact admin.');
	exit;
}

	//get Params Session
	$params = $_SESSION['duitkuOrder'];

	//set parameters for Duitku inquiry
    $merchant_code = $params['merchantcode'];
    $amount = (int)$params['amount'];
	$order_id = $params['invoiceid'];	
	$serverkey = $params['serverkey'];
	$endpoint = $params['endpoint'];
	$expiryPeriod = $params['expiryPeriod'];
	
	if (empty($merchant_code) || empty($serverkey) || empty($endpoint)) {
		echo "Please Check Duitku Configuration Payment";
		exit;
	}
	
	//System parameters
	$companyName = $params['companyname'];
	$systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
	$paymentMethod = $_REQUEST['paymentMethod'];
	
	// Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
	$phoneNumber = $params['clientdetails']['phonenumber'];
	$postalCode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$description = $params["description"];
	
	$ProducItem = array(
		'name' => $description,
		'price' => intval($amount),
		'quantity' => 1
	);
	
	$item_details = array ($ProducItem);
	
	$billing_address = array(
	  'firstName' => $firstname,
	  'lastName' => $lastname,
	  'address' => $address1 . " " . $address2,
	  'city' => $city,
	  'postalCode' => $postalCode,
	  'phone' => $phoneNumber,
	  'countryCode' => $country
	);
	
	$customerDetails = array(
		'firstName' => $firstname,
		'lastName' => $lastname,
		'email' => $email,
		'phoneNumber' => $phoneNumber,
		'billingAddress' => $billing_address,
		'shippingAddress' => $billing_address
	);

	$signature = md5($merchant_code.$order_id.$amount.$serverkey);
	
	// Prepare Parameters	
    $params = array(
          'merchantCode' => $merchant_code, // API Key Merchant /
          'paymentAmount' => $amount, //transform order into integer
          'paymentMethod' => $paymentMethod,
          'merchantOrderId' => $order_id,
          'productDetails' => $companyName . ' Order : #' . $order_id,
          'additionalParam' => '',
          'merchantUserInfo' => $firstname . " " .  $lastname,
          'customerVaName' => $firstname . " " .  $lastname,
		  'email' => $email,
		  'phoneNumber' => $phoneNumber,
          'signature' => $signature, 
          'expiryPeriod' => $expiryPeriod,		  
          'returnUrl' => $systemUrl."/modules/gateways/callback/duitku_return.php",
          'callbackUrl' => $systemUrl."/modules/gateways/callback/duitku_callback.php",
		  'customerDetail' => $customerDetails,
		  'itemDetails' => $item_details
    );
	

	try {  

		$redirUrl = Duitku_WebCore::getRedirectionUrl($endpoint, $params);      

		//Set Log
		logModuleCall('Duitku', $paymentMethod, $params, "", $redirUrl);
    }
    catch (Exception $e) {
      error_log('Caught exception: '.$e->getMessage()."\n");
	  echo $e->getMessage();
    }
	
//redirect to Duitku Page
header('Location: ' . $redirUrl);
die();
			