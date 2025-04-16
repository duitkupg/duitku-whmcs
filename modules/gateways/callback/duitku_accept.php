<?php
require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");

require_once __DIR__ . '/../duitku-lib/Duitku.php';

// check if the module is activated
/*--- start ---*/

if (empty($_REQUEST['order_id']) || empty($_REQUEST['paymentMethod']) || empty($_REQUEST['paymentName']) || empty($_REQUEST['params']) || empty($_REQUEST['securityHash'])) {
	logTransaction($_REQUEST['paymentName'], json_encode($_REQUEST, JSON_PRETTY_PRINT), "Failed before requesting to Duitku, Empty request data");
	logActivity("wrong query string, missing request data order_id, paymentMethod, paymentName, params, or securityHash.", 0);
	header('Location: ' . json_decode(base64_decode($_REQUEST['params']))->systemurl . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true");
	die();
}
	//get config data
	$config = getGatewayVariables($_REQUEST['paymentName']);
	
	//cek configuration
	if (empty($config['merchantcode']) || empty($config['serverkey']) || empty($config['environment'])) {
		logTransaction($_REQUEST['paymentName'], json_encode($config, JSON_PRETTY_PRINT), "Invalid Configuration for " . $_REQUEST['paymentName']);
		logActivity("Invalid Configuration for " . $_REQUEST['paymentName'], 0);
		header('Location: ' . $config['systemurl'] . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true");
		die();
	}

	//prepare for decription
	$password = $config['serverkey'];

	//get Params
	$params = json_decode(base64_decode($_REQUEST['params']));
	$systemUrl = $params->systemurl;
	$clientId = $params->cart->client->id;

	//check parameter for security
	if ($_REQUEST['securityHash'] != Duitku_Helper::metode_hash(base64_decode($_REQUEST['params']), $password)) {
		logActivity("User try to change payment data to Duitku.", $clientId);
		header('Location: ' . $systemUrl . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true");
		die();
	}
	
	//set parameters for Duitku inquiry
    $merchant_code = $config['merchantcode'];
    $amount = $params->amount;//(int)ceil($params['amount']);//
	$order_id = $params->invoiceid;	
	$serverkey = $config['serverkey'];
	$environment = $config['environment'];
	$endpoint = "";
	$expiryPeriod = $config['expiryPeriod'];
	$credcode = $config['credcode'];
	$currencyId = $params->currencyId;
	$additionalParam = $params->currency;

	if($environment == "sandbox"){
		$endpoint = "https://sandbox.duitku.com/webapi";
	}else{
		$endpoint = "https://passport.duitku.com/webapi";
	}
	
	//check if currency not IDR
	if ($params->currency != 'IDR') {
		$currencyCurrent = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("code"=>$params->currency)));
		$currencyDefault = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("id"=>'1')));

		//Check Default Currency
		if ($currencyDefault['code'] != 'IDR'){ 
			logTransaction($_REQUEST['paymentName'], json_encode($params, JSON_PRETTY_PRINT), "Default currency is not IDR");
			logActivity("Default currency is not IDR, please set default currencies to IDR to recieve payment from Duitku.", $clientId);
			header('Location: ' . $systemUrl . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true");
			die();
		}
		
		$amount = $amount / $currencyCurrent['rate'];
	}

	//round up amount for decimals
	$amount = (int)ceil($amount);

	//System parameters
	$companyName = $params->companyname;
    $returnUrl = $params->returnurl;
	$paymentMethod = $_REQUEST['paymentMethod'];
	
	// Client Parameters
    $firstname = $params->clientdetails->firstname;
    $lastname = $params->clientdetails->lastname;
    $email = $params->clientdetails->email;
	$phoneNumber = $params->clientdetails->phonenumber;
	$postalCode = $params->clientdetails->postcode;
	$country = $params->clientdetails->country;
	$address1 = $params->clientdetails->address1;
    $address2 = $params->clientdetails->address2;
	$city = $params->clientdetails->city;
	$description = $params->description;
	
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
          'additionalParam' => $additionalParam,
          'merchantUserInfo' => $firstname . " " .  $lastname,
          'customerVaName' => $firstname . " " .  $lastname,
		  'email' => $email,
		  'phoneNumber' => $phoneNumber,
          'signature' => $signature, 
          'expiryPeriod' => $expiryPeriod,		  
          'returnUrl' => $systemUrl."/modules/gateways/callback/duitku_return.php?clientId=" . $clientId,
          'callbackUrl' => $systemUrl."/modules/gateways/callback/duitku_callback.php",
		  'customerDetail' => $customerDetails,
		  'itemDetails' => $item_details
    );

    if ($params['paymentMethod'] == 'MG') {
    	$params['credCode'] = $credcode;
    }
	

	try {  

		$redirUrl = Duitku_WebCore::getRedirectionUrl($endpoint, $params);      

		//Set Log
		logActivity("Generate Trx Via Duitku (" . $_REQUEST['paymentName'] . " on " . $environment . ") with order id: " . $order_id, $clientId);
    }
    catch (Exception $e) {
		logActivity("Failed Generate Trx Via Duitku (" . $_REQUEST['paymentName'] . " on " . $environment . ") with order id: " . $order_id ."\n".$e->getMessage(), $clientId);
		$redirUrl = $systemUrl . "/viewinvoice.php?id=" . $order_id . "&paymentfailed=true";
    }
	
//redirect to Duitku Page
logActivity("Redirect to " . $redirUrl, $clientId);
header('Location: ' . $redirUrl);
die();
			