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
* @author anggiyawan@chakratechnology.com
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
function duitku_shopeepay_applink_MetaData()
{
  return array(
    'DisplayName' => 'Duitku ShopeePay Applink',
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
function duitku_shopeepay_applink_config()
{
  return array(
    // the friendly display name for a payment gateway should be
    // defined here for backwards compatibility
    'FriendlyName' => array(
      'Type' => 'System',
      'Value' => 'Duitku ShopeePay Applink',
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
    'expiryPeriod' => array(
      'FriendlyName' => 'Duitku Expiry Period',
      'Type' => 'text',
      'Size' => '25',
      'Default' => '5',
      'Description' => '<br>The validity period of the transaction before it expires. Max 60 in minutes.',
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
function duitku_shopeepay_applink_link($params)
{
  //set parameters for Duitku inquiry
  $merchant_code = $params['merchantcode'];
  $amount = (int)$params['amount'];
  $order_id = $params['invoiceid'];
  $serverkey = $params['serverkey'];
  $endpoint = $params['endpoint'];
  $expiryPeriod = $params['expiryPeriod'];


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
    'paymentMethod' => "SA",
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
    'itemDetails' => $item_details,
  );

  try {
    $redirUrl = Duitku_WebCore::getRedirectionUrl($endpoint, $params);
  }
  catch (Exception $e) {
    error_log('Caught exception: '.$e->getMessage()."\n");
  }

  $img       = $systemUrl . "/modules/gateways/duitku-images/duitku_shopeepay_applink.png";
  $htmlOutput .= '<img style="width: 152px;" src="' . $img . '" alt="SHOPEEPAY APPLINK"><br>';
  $htmlOutput .= '<button id="pay-button" onClick="javascript:window.location.href=\'' . $redirUrl . '\'">' . $langPayNow . '</button>';

  $htmlOutput .= '<script>
  var isMobile = false;

  if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
  || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
    isMobile = true;
  }

  function checkDevice() {
    if (!isMobile) {
      if(document.querySelector("[class*=\"alert alert-info text-center\"]") !== null){
        document.querySelector("[class*=\"alert alert-info text-center\"]").innerText = "Mohon maaf sistem pembayaran ShopeePay Applink hanya tersedia untuk perangkat mobile";
        document.querySelector(\'[alt*="Loading"]\').style.display = "none";
        document.querySelector("form").setAttribute("onsubmit", "return false");
      }

      document.querySelector("#pay-button").innerText = "ShopeePay Applink tidak tersedia";
      document.querySelector("#pay-button").removeAttribute("onclick");

      var invoice_container = document.querySelector("[class*=\"container-fluid invoice-container\"]");
      if(invoice_container !== null) {
        var generatePanel = document.createElement("div");
        generatePanel.className = "panel panel-info";
        var generatePanelHeading = document.createElement("div");
        generatePanelHeading.className = "panel-heading";
        var generatePanelTitle = document.createElement("h3");
        generatePanelTitle.className = "panel-title";
        var generateTitleText = document.createElement("strong");
        generateTitleText.innerText = "Info Sistem Pembayaran";
        var generatePanelBody = document.createElement("div");
        generatePanelBody.className = "panel-body text-center";
        generatePanelBody.innerText = "Mohon gunakan perangkat mobile anda untuk menggunakan sistem pembayaran ShopeePay Applink";
        generatePanelTitle.appendChild(generateTitleText);
        generatePanelHeading.appendChild(generatePanelTitle);
        generatePanel.appendChild(generatePanelHeading);
        generatePanel.appendChild(generatePanelBody);
        invoice_container.appendChild(generatePanel);
      }
    }
  }

  window.addEventListener("load", function () {
    checkDevice();
  });

  </script>';

  return $htmlOutput;
}
