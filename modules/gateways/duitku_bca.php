<?php
/**
 * WHMCS Duitku Payment Gateway Module
 *
 * Duitku Payment Gateway modules allow you to integrate Duitku Web with the
 * WHMCS platform.
 *
 * For more information, please refer to the online documentation.
 * @see http://docs.duitku.co.id
 *
 * Module developed based on official WHMCS Sample Payment Gateway Module
 * 
 * @author timur@chakratechnology.com
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
require_once(dirname(__FILE__) . '/duitku-lib/Duitku.php');
/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Meta_Data_Parameters
 *
 * @return array
 */
function duitku_bca_MetaData()
{
    return array(
        'DisplayName' => 'Duitku BCA Klikpay Payment Gateway Module',
        'APIVersion' => '1.0', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => true,
    );
}
/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function duitku_bca_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Duitku BCA Klikpay',
        ),
        // a text field type allows for single line text input
        'merchantcode' => array(
            'FriendlyName' => 'Duitku Merchant Code',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Input Duitku Merchant Code. ',
        ),
        // a text field type allows for single line text input
        'serverkey' => array(
            'FriendlyName' => 'Duitku API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Input Duitku API Key. ',
        ),
        // the dropdown field type renders a select menu of options
        'endpoint' => array(
            'FriendlyName' => 'Duitku Endpoint',
            'Type' => 'text',
			'Size' => '100',
			'Default' => 'https://passport.duitku.com/webapi',
            'Description' => 'Duitku Endpoint, mohon isi merchant code dan api key sebelum mengakses endpoint.',
        ),        
    );
}
/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function duitku_bca_link($params)
{
	//set parameters for Duitku inquiry
    $merchant_code = $params['merchantcode'];
    $amount = (int)$params['amount'];
	$order_id = $params['invoiceid'];	
	$serverkey = $params['serverkey'];
	$endpoint = $params['endpoint'];
	
	
	//System parameters
	$companyName = $params['companyname'];
	$systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
	$langPayNow = $params['langpaynow'];
	
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
          'paymentMethod' => "BK",
          'merchantOrderId' => $order_id,
          'productDetails' => $companyName . ' Order : #' . $order_id,
          'additionalParam' => '',
          'merchantUserInfo' => $firstname . " " .  $lastname,
          'customerVaName' => $firstname . " " .  $lastname,
		  'email' => $email,
		  'phoneNumber' => $phoneNumber,
          'signature' => $signature, 
          'expiryPeriod' => 1440,		  
          'returnUrl' => $systemUrl."/modules/gateways/callback/duitku_return.php",
          'callbackUrl' => $systemUrl."/modules/gateways/callback/duitku_callback.php",
		  'customerDetail' => $customerDetails,
		  'itemDetails' => $item_details,
    );        

    try {     
      $redirUrl = Duitku_WebCore::getRedirectionUrl($endpoint, $params);      
    }
    catch (Exception $e) {
      error_log('Caught exception: '.$e->getMessage()."\n");
    }		
	
	$img       = $systemUrl . "/modules/gateways/duitku-images/bca-klikpay.png"; 
    $htmlOutput .= '<img style="width: 152px;" src="' . $img . '" alt="BCA Klikpay"><br>';
	$htmlOutput .= '<button onClick="javascript:window.location.href=\'' . $redirUrl . '\'">' . $langPayNow . '</button>';
        
    return $htmlOutput;
}