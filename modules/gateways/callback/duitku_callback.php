<?php
require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");

require_once __DIR__ . '/../duitku-lib/Duitku.php';

$gatewayModuleName = "";

// check if the module is activated
/*--- start ---*/
$paymentCode= stripslashes($_POST['paymentCode']);

switch ($paymentCode) {
	case "OV":
		$gatewayModuleName = "duitku_ovo"; break;
	case "VC":
		$gatewayModuleName = "duitku_cc"; break;
	case "MG":
		$gatewayModuleName = "duitku_migs"; break;
	case "BK":
		$gatewayModuleName = "duitku_bca"; break;
	case "BT":
		$gatewayModuleName = "duitku_vapermata"; break;
	case "VA":
		$gatewayModuleName = "duitku_vamaybank"; break;
	case "A1":
		$gatewayModuleName = "duitku_vaatmbersama"; break;
	case "B1":
		$gatewayModuleName = "duitku_vacimb"; break;
	case "I1":
		$gatewayModuleName = "duitku_vabni"; break;
	case "M1":
		$gatewayModuleName = "duitku_vamandiri"; break;
	case "M2":
		$gatewayModuleName = "duitku_vamandirih2h"; break;
	case "FT":
		$gatewayModuleName = "duitku_varitel"; break;
	case "SP":
		$gatewayModuleName = "duitku_shopee"; break;
	case "SA":
		$gatewayModuleName = "duitku_shopeepay_applink"; break;
	case "LA":
		$gatewayModuleName = "duitku_linkaja_applink"; break;
	case "LF":
		$gatewayModuleName = "duitku_linkaja_applink"; break;		
	case "BC":
		$gatewayModuleName = "duitku_vabca"; break;
    default:
		throw new Exception('payment method not recognize.');
}

$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
	exit("Module Not Activated");
}

/*--- end ---*/

if (empty($_POST['resultCode']) || empty($_POST['merchantOrderId']) || empty($_POST['reference'])) {
	throw new Exception('wrong query string please contact admin.');
}

$order_id = stripslashes($_POST['merchantOrderId']);
$status = stripslashes($_POST['resultCode']);
$reference = stripslashes($_POST['reference']);
$paymentAmount = stripslashes($_POST['amount']);
//set parameters for Duitku inquiry
$merchant_code = $gatewayParams['merchantcode'];
$api_key = $gatewayParams['serverkey'];
$endpoint = $gatewayParams['endpoint'];

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 */

$order_id = checkCbInvoiceID($order_id, $gatewayParams['name']);
checkCbTransID($reference);
$success = false;

if ($status == '00' && Duitku_WebCore::validateTransaction($endpoint, $merchant_code, $order_id, $reference, $api_key)) {
	$success = true;
} else {
	$success = false;
}

if ($success) {
    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $order_id,
        $reference,
        $paymentAmount,
        0,
        $gatewayModuleName
    );
    echo "Payment success notification accepted";
}
else{
	//Adopted from paypal to log all the failed transaction
	$orgipn = "";
	foreach ($_POST as $key => $value) {
		$orgipn.= ("" . $key . " => " . $value . "\r\n");
	}
	logTransaction($gatewayModuleName, $orgipn, "Duitku Handshake Invalid");
	header("HTTP/1.0 406 Not Acceptable");
	exit();
}

?>
