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
function duitku_cc_MetaData()
{
    return array(
        'DisplayName' => 'Duitku Credit Card Payment Gateway Module',
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
function duitku_cc_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Duitku Creditcard',
        ),
        // a text field type allows for single line text input
        'merchantcode' => array(
            'FriendlyName' => 'Duitku Merchant Code',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Input Duitku Merchant Code.',
        ),
        // a text field type allows for single line text input
        'serverkey' => array(
            'FriendlyName' => 'Duitku API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Input Duitku API Key.',
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
function duitku_cc_link($params)
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
	
	$signature = md5($merchant_code.$order_id.$amount.$serverkey);
	
	// Prepare Parameters	
    $params = array(
          'merchantCode' => $merchant_code, // API Key Merchant /
          'paymentAmount' => $amount, //transform order into integer
          'paymentMethod' => "VC",
          'merchantOrderId' => $order_id,
          'productDetails' => $companyName . ' Order : #' . $order_id,
          'additionalParam' => '',
          'merchantUserInfo' => $firstname . " " .  $lastname,
		  'email' => $email,
		  'phoneNumber' => $phoneNumber,
          'signature' => $signature,          
          'returnUrl' => $systemUrl."/modules/gateways/callback/duitku_return.php",
          'callbackUrl' => $systemUrl."/modules/gateways/callback/duitku_callback.php"
    );         

    try {     
      $redirUrl = Duitku_VtWeb::getRedirectionUrl($endpoint, $params);      
    }
    catch (Exception $e) {
      error_log('Caught exception: '.$e->getMessage()."\n");
    }		
	
	$img       = $systemUrl . "/modules/gateways/duitku-images/duitku_creditcard.png"; 
    $htmlOutput .= '<img style="width: 152px;" src="' . $img . '" alt="creditcard"><br>';
	$htmlOutput .= '<button onClick="javascript:window.location.href=\'' . $redirUrl . '\'">' . $langPayNow . '</button>';
    
    
    return $htmlOutput;
}