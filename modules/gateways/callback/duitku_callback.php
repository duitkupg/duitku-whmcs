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
	case "AG":
		$gatewayModuleName = "duitku_artha"; break;
	case "A1":
		$gatewayModuleName = "duitku_vaatmbersama"; break;
	case "A2":
		$gatewayModuleName = "duitku_pospay"; break;
	case "DA":
		$gatewayModuleName = "duitku_dana"; break;
	case "DN":
		$gatewayModuleName = "duitku_indodana"; break;
	case "B1":
		$gatewayModuleName = "duitku_vacimb"; break;
	case "I1":
		$gatewayModuleName = "duitku_vabni"; break;
	case "M2":
		$gatewayModuleName = "duitku_vamandirih2h"; break;
	case "M1":
		$gatewayModuleName = "duitku_vamandiri"; break;
	case "FT":
		$gatewayModuleName = "duitku_varitel"; break;
	case "S1":
		$gatewayModuleName = "duitku_sampoerna"; break;
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
	case "BA":
		$gatewayModuleName = "duitku_vabca"; break;
	case "LQ":
		$gatewayModuleName = "duitku_linkaja_qris"; break;
	case "IR":
		$gatewayModuleName = "duitku_indomaret"; break;
	case "BR":
		$gatewayModuleName = "duitku_briva"; break;
	case "NC":
		$gatewayModuleName = "duitku_bnc"; break;
	case "NQ":
		$gatewayModuleName = "duitku_nobu_qris"; break;
	case "AT":
		$gatewayModuleName = "duitku_atome"; break;
	case "GQ":
		$gatewayModuleName = "duitku_gudangvoucher_qris"; break;
	case "JP":
		$gatewayModuleName = "duitku_jenius_pay"; break;
	case "DM":
		$gatewayModuleName = "duitku_vadanamonh2h"; break;
	case "BV":
		$gatewayModuleName = "duitku_vabsi"; break;
    default:
		logTransaction($paymentCode, json_encode($_POST, JSON_PRETTY_PRINT), "Callback failed, payment method " . $paymentCode . " not recognize.");
		header("HTTP/1.0 200 OK");
		exit();
}

$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
	logTransaction($gatewayModuleName, json_encode($_POST, JSON_PRETTY_PRINT), "Callback failed, Module " . $gatewayModuleName . " not active.");
	header("HTTP/1.0 200 OK");
	exit();
}

/*--- end ---*/

if (empty($_POST['resultCode']) || empty($_POST['merchantOrderId']) || empty($_POST['reference'])) {
	logTransaction($gatewayModuleName, json_encode($_POST, JSON_PRETTY_PRINT), "Callback failed, param resultCode, merchantOrderId, or reference is empty.");
	header("HTTP/1.0 200 OK");
	exit();
}

$order_id = stripslashes($_POST['merchantOrderId']);
$status = stripslashes($_POST['resultCode']);
$reference = stripslashes($_POST['reference']);
$paymentAmount = stripslashes($_POST['amount']);
$additionalParam = stripslashes($_POST['additionalParam']);
//set parameters for Duitku inquiry
$merchant_code = $gatewayParams['merchantcode'];
$api_key = $gatewayParams['serverkey'];
$endpoint = "";
$environment = $gatewayParams['environment'];

if($environment == "sandbox"){
	$endpoint = "https://sandbox.duitku.com/webapi";
}else{
	$endpoint = "https://passport.duitku.com/webapi";
}

//check current currency
$currencyCurrent = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("code"=>$additionalParam)));
if ($currencyCurrent['code'] != 'IDR'){
	$currencyDefault = mysql_fetch_assoc(select_query('tblcurrencies', 'code, rate', array("id"=>'1')));
	
	//Check Default Currency
	if ($currencyDefault['code'] != 'IDR'){
		logTransaction($gatewayModuleName, json_encode($currencyDefault, JSON_PRETTY_PRINT), "Callback failed, Default currency is not IDR.");
		header("HTTP/1.0 200 OK");
		exit();
	}
	
	$paymentAmount = $paymentAmount * $currencyCurrent['rate'];
}

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

try {
	$validatedTransaction = Duitku_WebCore::validateTransaction($endpoint, $merchant_code, $order_id, $reference, $api_key);
}
catch (Exception $e) {
	logTransaction($gatewayModuleName, json_encode($e, JSON_PRETTY_PRINT), "Duitku Check Transaction Error for " . strtoupper($reference) . " with error message: " . $e->getMessage());
	logModuleCall('Duitku', "Check Transaction for " . strtoupper($reference), $e->getMessage(), "Duitku Check Transaction Error", "");
	header("HTTP/1.0 200 OK");
	exit();
}

if ($status == '00' && $validatedTransaction) {
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
	logTransaction($gatewayModuleName, json_encode($_POST, JSON_PRETTY_PRINT), "Callback finish, Payment success validated.");
	logModuleCall('Duitku', "Callback Transaction for " . strtoupper($reference), $_POST, "Payment success notification accepted", "");
	header("HTTP/1.0 200 OK");
	exit();
}
else{
	//log all the failed transaction
	logTransaction($gatewayModuleName, json_encode($_POST, JSON_PRETTY_PRINT), "Callback Invalid, status not success");
	logModuleCall('Duitku', "Callback Transaction for " . strtoupper($reference), $_POST, "Duitku Handshake Invalid", "");
	header("HTTP/1.0 200 OK");
	exit();
}
?>
