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
function duitku_atome_MetaData()
{
    return array(
        'DisplayName' => 'Duitku Atome Payment Gateway Module',
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
function duitku_atome_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Duitku Atome',
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
function duitku_atome_link($params)
{
		
	//set session Order
	// $_SESSION['duitkuOrder'] = "";
	
	//System parameters
	$order_id = $params['invoiceid'];
	$systemUrl = $params['systemurl'];
    $paymentName = $params['paymentmethod'];
	$langPayNow = $params['langpaynow'];
	$password = $params['serverkey'];
	$paymentMethod = "AT"; // PaymentMethod For Duitku

    //unset settings
    unset($params['merchantcode']);
    unset($params['serverkey']); 
    unset($params['endpoint']); 
    unset($params['expiryPeriod']);
    unset($params['password']);
    unset($params['clientdetails']['password']);
	$securityHash = Duitku_Helper::metode_hash(json_encode($params), $password);
	
	$img       = $systemUrl . "/modules/gateways/duitku_atome/logo.png"; 
    $htmlOutput .= '<img style="width: 152px;" src="' . $img . '" alt="ATOME"><br>';
    
	// $_SESSION['duitkuOrder'] = $params;
    $htmlOutput .= '<form method="post" action="' . $systemUrl."/modules/gateways/callback/duitku_accept.php" . '">';
	$htmlOutput .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
	$htmlOutput .= '<input type="hidden" name="paymentMethod" value="' . $paymentMethod . '" />';
	$htmlOutput .= '<input type="hidden" name="paymentName" value="' . $paymentName . '" />';
	$htmlOutput .= '<input type="hidden" name="securityHash" value="' . $securityHash . '" />';
	// $htmlOutput .= '<input type="hidden" name="params" value="' . Duitku_Helper::metode_aes(json_encode($params), $password) . '" />';
	$htmlOutput .= '<input type="hidden" name="params" value="' . base64_encode(json_encode($params)) . '" />';
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

	return $htmlOutput;
	
}