<?php
require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");

require_once __DIR__ . '/../duitku-lib/Duitku.php';

// check if the module is activated
/*--- start ---*/

if (empty($_REQUEST['order_id']) || empty($_REQUEST['paymentMethod']) || empty($_REQUEST['paymentName']) || empty($_REQUEST['params']) || empty($_REQUEST['securityHash'])) {
	echo 'wrong query string please contact admin.';
	error_log('wrong query string please contact admin.');
	exit;
}
	//get config data
	$config = getGatewayVariables($_REQUEST['paymentName']);
	
	//cek configuration
	if (empty($config['merchantcode']) || empty($config['serverkey']) || empty($config['endpoint'])) {
		echo "Invalid setup payment method, Please contact this website owner";
		error_log("Please Check Duitku Configuration Payment");
		exit;
	}

	//prepare for decription
	$password = $config['serverkey'];

	//get Params
	// $params = json_decode(Duitku_Helper::metode_aes($_REQUEST['params'], $password, "decrypt"));
	$params = json_decode(base64_decode($_REQUEST['params']));

	//check parameter for security
	// if ($_REQUEST['securityHash'] != Duitku_Helper::metode_hash(Duitku_Helper::metode_aes($_REQUEST['params'], $password, "decrypt"), $password)) {
	if ($_REQUEST['securityHash'] != Duitku_Helper::metode_hash(base64_decode($_REQUEST['params']), $password)) {
		echo "Something wrong, please contact administrator!";
		error_log("User try to change payment data to Duitku");
		exit;
	}
	
	//set parameters for Duitku inquiry
    $merchant_code = $config['merchantcode'];
    $amount = $params->amount;//(int)ceil($params['amount']);//
	$order_id = $params->invoiceid;	
	$serverkey = $config['serverkey'];
	$endpoint = $config['endpoint'];
	$expiryPeriod = $config['expiryPeriod'];
	$credcode = $config['credcode'];
	$currencyId = $params->currencyId;
	$additionalParam = $params->currency;
	
	//check if currency not IDR
	if ($params->currency != 'IDR') {
		$currencyCurrent = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("code"=>$params->currency)));
		$currencyDefault = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("id"=>'1')));
		$currencyIDR = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("code"=>'IDR')));
		//check IDR rate existance
		if	($currencyIDR['code'] != 'IDR'){
			throw new Exception('No IDR rate for this site, please contact admin.');
		}else{
			//Check Default Currency
			if ($currencyDefault['code'] != 'IDR'){
				//Check if Used currency is default
				if ($currencyCurrent['code'] != $currencyDefault['code']){
					if ($currencyCurrent['code'] >= $currencyDefault['code']){
						$amount = $amount / $currencyCurrent['rate'];
						$amount = $amount * $currencyIDR['rate'];
					}else{
						$amount = $amount * $currencyCurrent['rate'];
						$amount = $amount / $currencyIDR['rate'];
					}
				}else{
					if($currencyIDR['rate'] >= $currencyDefault['rate']){
						$amount = $amount * $currencyIDR['rate'];
					}else{
						$amount = $amount / $currencyIDR['rate'];
					}
				}
			}else{
				if($currencyCurrent['rate'] >= $currencyDefault['rate']){
					$amount = $amount * $currencyCurrent['rate'];
				}else{
					$amount = $amount / $currencyCurrent['rate'];
				}
			}
		}
	}

	//round up amount for decimals
	$amount = (int)ceil($amount);

	//System parameters
	$companyName = $params->companyname;
	$systemUrl = $params->systemurl;
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
          'returnUrl' => $systemUrl."/modules/gateways/callback/duitku_return.php",
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
		logModuleCall('Duitku', $paymentMethod, $params, "", $redirUrl);
    }
    catch (Exception $e) {
      error_log('Caught exception: '.$e->getMessage()."\n");
	  echo $e->getMessage();
    }
	
//redirect to Duitku Page
header('Location: ' . $redirUrl);
die();
			