Duitku WHMCS Extension
===================================

This is the Duitku extension for the WHMCS platform.

## Requirements
- WHMCS, Tested up to 8.12.1
- Duitku account, [register here](https://dashboard.duitku.com/Account/Register)
- [API Key](https://docs.duitku.com/en/account/#account-integration--getting-api-key)

## Installation
1. Download the Duitku WHMCS plugin from [here](https://docs.duitku.com/payment-gateway/plugin).
2. Go to your hosting file manager and navigate to your WHMCS installation directory.
3. Upload the plugin file you downloaded earlier.
4. Once the upload is complete, right-click on the plugin file and select Extract.
5. After the extraction is done, log in to your WHMCS admin panel.
6. In the WHMCS admin area, go to Setup > hover over Payments > then click Payment Gateways.
7. On the Payment Gateways page, click the All Payment Gateways tab, then click on Duitku payment module you wish to activate.
8. Once the payment gateway turns green, it means it has been successfully activated.
9. After activation, go to the Manage Existing Gateways tab.
10. You will see the configuration form for the activated Duitku payment gateway. Fill it out as follows:
    - Duitku Merchant Code: Enter the Merchant Code you obtained from your Project on the Duitku merchant dashboard.
    - Duitku API Key: Enter the Project API Key from the Duitku merchant dashboard.
    - Duitku Environment:   Select sandbox for testing, or production if you're ready to accept real payments.
    - Duitku Expiry Period: For some payment methods, you can set an expiration time (in minutes) for users to complete the payment.
11. Once you have completed the configuration, click Save Changes, and repeat the process for other Duitku payment gateways you wish to enable.

## Support

If you have a feature request or spotted a bug or a techical problem, [create an issue here](https://github.com/duitkupg/duitku-whmcs/issues/new/choose).
For other questions, please contact duitku through live chat on your dashboard.
