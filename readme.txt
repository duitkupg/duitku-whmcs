=== duitku WHMCS ===
/*
Plugin Name:  duitku-whmcs
Description:  Duitku Payment Gateway 
Version:      2.11.12
Author:       Duitku Development Team

Author URI:   http://duitku.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/
== Installation ==

1. Unduh plugin duitku whmcs [disini](file:///C:/Users/rayhan/Desktop/Duitku-Documentation/trunk/duitku-docs/common/files/duitku-whmcs-v2.9.zip)
2. Masuk ke file manager hosting Anda lalu ke folder/direktori instalasi WHMCS Anda
3. Unggah file plugin yang sebelumnya telah didownload
4. Setelah berhasil menggunggah file plugin, klik kanan pada file plugin dan pilih Extract
5. Setelah proses unggah selesai, log in ke dalam admin WHMCS anda.
6. Pada halaman admin WHMCS anda, klik menu Setup, arahkan kursor ke submenu Payments lalu pilih Payment Gateways.
7. Pada halaman Payment Gateways, klik tab All Payment Gateways, lalu klik pada Duitku BCA Klikpay, Duitku CreditCard, Duitku CIMB Clicks, Duitku Mandiri, Duitku VA Permata, atau modul payment gateway Duitku yang anda ingin aktifkan.
8. Apabila payment gateway sudah berubah menjadi hijau, maka payment gateway tersebut sudah di aktifkan.
9. Setelah payment gateway diaktifkan, klik tab Manage Existing Gateway.
10. Akan muncul tampilan konfigurasi untuk payment gateway yang anda aktifkan,
	- Duitku Merchant Code: masukkan Merchant Code anda yang anda dapatkan dari Project di laman merchant Duitku
	- Duitku API Key: masukkan Project API Key yang anda dapatkan dari Project di laman merchant Duitku
	- Duitku Endpoint: Apabila integrasi masih dalam tahap development, gunakan alamat ini https://sandbox.duitku.com/webapi
	- Duitku Endpoint: Apabila integrasi sudah dalam tahap production, gunakan alamat ini https://passport.duitku.com/webapi
11. Setelah selesai mengisi form konfigurasi, klik Save Changes, lalu ulangi pada payment gateway duitku yang lain.

For whmcs documentation you may see here: https://developers.whmcs.com/payment-gateways/installation-activation/
Install & activate the plugin. Modify some options in the settings page to suits your need.

== Screenshots ==

-
== Changelog ==

change 2.11.11 to 2.11.12 :
- remove sampoerna

improvement 2.11.10 to 2.11.11 :
- Convert rate amount only if base amount is IDR

improvement 2.11.9 to 2.11.10 :
- Enhance rate calculation

improvement 2.11.7 to 2.11.8 :
- Add Atome payment


improvement 2.11.6 to 2.11.7 :
- Add security hash whes request transaction before post to API and encode parameter

improvement 2.11.5 to 2.11.6 :
- Enhance transfer data process using post when request transaction

improvement 2.11.4 to 2.11.5 :
- Fix Bug response callback

= 2.11 Jun 23, 2022 =
improvement 2.11.3 to 2.11.4 :
- Fix Bug response callback

improvement 2.11.2 to 2.11.3 :
- Fix Bug Va BCA Facilitator

= 2.11 Jun 16, 2022 =

improvement 2.11 to 2.11.2 :
- Change Logo Bank Neo Commerce

= 2.10 Nov 11, 2021 =

improvement 2.10 to 2.11 :
- Add BRI Virtual Account
- Add QRIS by Nobu

improvement 2.9 to 2.10 :
- Remove deprecated Mandiri Virtual Accunt
- Add Bank Neo Commerce

= 2.9.1 Okt 18, 2021 =
- Add round up for decimal values
- Add Convert to IDR if default currency isn't Rupiah 

= 2.5 Nov 16, 2020 = 

improvement 2.4 to 2.5 :
- Change Mandiri Virtual Accunt become deprecated
- Add Mandiri H2H Virtual Account

= 2.4 Oct 13, 2020 = 

improvement 2.3 to 2.4 :
- Add BCA Virtual Accunt

improvement 2.2 to 2.3 :
- Add Sahabat Sampoerna
- Add Artha Graha
- Bug Double Payment

= 2.2 June 19, 2020 = 

improvement 2.1 to 2.2 :
- Add Shoppepay Applink
- Add navigator device detection

= 2.1 Mar 12, 2020 = 

improvement 2.0 to 2.1 :
- Improve parameter Duitku Expiry Period

= 2.0 Feb 06, 2020 = 

improvement 1.0 to 2.0 :
- upgrade API v2
- add ShopeePay
- add Indodana

= 1.0 =

Initial Public Release