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
function duitku_linkaja_applink_MetaData()
{
  return array(
    'DisplayName' => 'Duitku LinkAja Applink',
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
function duitku_linkaja_applink_config()
{
  return array(
    // the friendly display name for a payment gateway should be
    // defined here for backwards compatibility
    'FriendlyName' => array(
      'Type' => 'System',
      'Value' => 'Duitku LinkAja Applink',
    ),
	'tipe' => array(
      'FriendlyName' => 'Tipe',
      'Type' => 'dropdown',
      'Options' => array(
			'LA' => 'Fee Percentage',
			'LF' => 'Fee Fixed',
		),
      'Description' => 'Fee Percentage Or Fixed.',
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
    'environment' => array(
            'FriendlyName' => 'Duitku Environment',
            'Type' => 'dropdown',
            'Options' => array(
                'sandbox' => 'Sandbox',
                'production' => 'Production',
            ),
            'Description' => 'Duitku Environment, select one that match with your merchant code and api key.',
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
function duitku_linkaja_applink_link($params)
{
  
  //set session Order
	// $_SESSION['duitkuOrder'] = "";
	
	$type = (isset($params['tipe'])) ? $params['tipe'] : null;
	
	//System parameters
	$order_id = $params['invoiceid'];
	$systemUrl = $params['systemurl'];
  $paymentName = $params['paymentmethod'];
	$langPayNow = $params['langpaynow'];
	$password = $params['serverkey'];
	
	if ( isset($type) ) {
		$paymentMethod = $type;
	} else {
		$paymentMethod = "LA";
	}

  //unset settings
  unset($params['merchantcode']);
  unset($params['serverkey']); 
  unset($params['endpoint']); 
  unset($params['expiryPeriod']);
  unset($params['password']);
  unset($params['clientdetails']['password']);
	$securityHash = Duitku_Helper::metode_hash(json_encode($params), $password);
	
	$img       = $systemUrl . "/modules/gateways/duitku_linkaja_applink/logo.png";
	$htmlOutput .= '<img style="width: 152px;" src="' . $img . '" alt="LINKAJA APPLINK"><br>';
  
	// $_SESSION['duitkuOrder'] = $params;
  $htmlOutput .= '<form method="post" action="' . $systemUrl."/modules/gateways/callback/duitku_accept.php" . '">';
	$htmlOutput .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
	$htmlOutput .= '<input type="hidden" name="paymentMethod" value="' . $paymentMethod . '" />';
	$htmlOutput .= '<input type="hidden" name="paymentName" value="' . $paymentName . '" />';
	$htmlOutput .= '<input type="hidden" name="securityHash" value="' . $securityHash . '" />';
	// $htmlOutput .= '<input type="hidden" name="params" value="' . Duitku_Helper::metode_aes(json_encode($params), $password) . '" />';
	$htmlOutput .= '<input type="hidden" name="params" value="' . base64_encode(json_encode($params)) . '" />';
  $htmlOutput .= '<input type="submit" id="pay-button" value="' . $langPayNow . '" />';
  $htmlOutput .= '</form>';
	
  $htmlOutput .= '<script>
  var isMobile = false;

  if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
  || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
    isMobile = true;
  }

  function checkDevice() {
    if (!isMobile) {
      if(document.querySelector("[class*=\"alert alert-info text-center\"]") !== null){
        document.querySelector("[class*=\"alert alert-info text-center\"]").innerText = "Mohon maaf sistem pembayaran LinkAja Applink hanya tersedia untuk perangkat mobile";
        document.querySelector(\'[alt*="Loading"]\').style.display = "none";
        document.querySelector("form").setAttribute("onsubmit", "return false");
      }

      document.querySelector("#pay-button").innerText = "LinkAja Applink tidak tersedia";
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
        generatePanelBody.innerText = "Mohon gunakan perangkat mobile anda untuk menggunakan sistem pembayaran LinkAja Applink";
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
