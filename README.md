# Rcdevs Openotp

RCDevs OpenOTP Plugin for Nextcloud version 1.5.0
Copyright (c) 2010-2022 RCDevs SA, All rights reserved.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

### **********   DESCRIPTION   **********

OpenOTP plugin enables two-factor authentication to login to Nextcloud Admin/User Panel.Username/Email and Password are validated locally, and next step the plugin handle the Second Factor, i.e. the Challenge, as a "Two-Factor Vendor".
OpenOTP plugin manage several Login Mode including:

- NextCloud Password (NCP) + OTP (either fill in the input text, or easier by Pressing OK when receiving the Push Notification on mobile).
- NCP + FidoU2F (U2F Fido authentication method, see https://fidoalliance.org/ for more details.)
- NCP + OTP Or FidoU2F

User must exists in both Local Database and OpenOTP server (=LDAP integration). Nextcloud User Name must be the same as OTP Server (LDAP) Login Name (or email in NC and UPN/Alias in OpenOTP)
but password could be different (simple passwords are not sent to OTP server). The reason is the way how Nextcloud designs TwoFactor vendor integration, most of the time all the login steps
could  be validated to a single User backend (e.g. Authentication Server) avoiding duplicate accounts.
The new plugin is compatible with user_ldap core application. So, with just a little attribute to configure in the LDAP app, it's possible to have all
the user account in one place, your LDAP Directory (Nextcloud need local accounts to work properly but they are auto generated with LDAP integration app)

(No more contextual auth, account auto-creation while first Login on OpenOTP, Local OR Remote password validation (avoiding user blocking during configuration), remote password management (handle now by core), Global or per user permission)
When using Desktop client, you have to generate an Application password in your Dashboard, OpenOTP server is not contacted to authenticate.
On the other hand, for Mobile application, OpenOTP Plugin handle login requests in the same way as for Application in your Web Browser, e.g. if you have configured Push notification on OpenOTP Authentication Server,
you don't have to leave your Mobile, click on the notification and Confirm login.

## OPENOTP SERVER

OpenOTP is the RCDevs user authentication solution. OpenOTP is a server
application which provides multiple (highly configurable) authentication
schemes for your LDAP users, based on one-time passwords (OTP) technologies
 and including: - OATH HOTP/TOTP/OCRA Software/Hardware Tokens - Google
Authenticator - Mobile-OTP (mOTP) Software Tokens - SMS One-Time Passwords

- Mail / Secure Mail One-Time Passwords - Yubikey: visit [RCDevs OpenOTP](https://www.rcdevs.com/products/openotp/)
- Follow the quick start guide: [RCDevs WebADM installation](https://docs.rcdevs.com/howtos/webadm_install/webadm_install/)
- or download our appliances: [VMWare appliances](https://www.rcdevs.com/downloads/vmware-appliances/)

### **********   INSTALLATION   **********

Compatible Nextcloud 24.x to 26.X (Tested on 26.0.0)
Version 1.5.0

1. If your PHP installation does not have the soap extension, install the php-soap
   ..	package for your Linux distribution. With RedHat, do it with 'yum install php-soap'.
2. Upload openotp_auth directory under the 'apps' directory of your Nextcloud.
3. RCDevsOpenOTP Application folder should have read write permission for the web server
   ..	user (under debian/ubuntu : chown -R www-data:www-data openotp_auth)
4. Navigate to the 'Apps' page in Admin.
   ..	Click on 'OpenOTP Two Factor Authentication' in the application list. Then click the 'Enable' button.

### **********   USAGE  **********

- Navigate to the 'Admin' page / Additional settings, or go directly to the configuration via Admin button in the header
- Set at least the server url and the Client Id, Click 'Save'
- Allow users to administer Two-factor on their profile settings page or not. When activated, User goes to Personnal section
  to enable or not Two-Factor on his account.
- It's possible to use LDAP/AD Integration (user_ldap) application with RCDevs OpenOTP (openotp_auth) application. Be sure to configure
  LDAP plugin to create your local user with the uid/samaccountname, otherwise a random generated string is used for username when accounts
  are auto-created during import process. To do this, click on Expert tab, and fill in "Override UUID detection" with the correct login name
  based on your LDAP directory (uid/samaccountname...)
- Contextual authentication: Change the LoginMode to LDAP-only for requests comming from trusted devices on trusted IPs.
  One user device gets trusted for a specifc IP address after a successful two-factor authentication.
  Contextual Authentication need a persistant cookie after logout to work properly. Nextcloud implement Clear-Site-Data HTTP response header (https://www.w3.org/TR/clear-site-data/#grammardef-cookies),
  this mechanism clear all cache, cookie and storage from the browser, included OpenOTP context cookie. To enable this feature, you need to MANUALLY edit this file:
  core/Controller/LoginController.php and comments this line 123 in public function logout():
  //$response->addHeader('Clear-Site-Data', '"cache", "cookies", "storage", "executionContexts"'); or delete ["cookies",] from the same line.
- !! IMPORTANT !! keep an admin user working without otp in case of a problem. If not you can:
  ->  Use occ command line to disable/enable two-factor authentication for a user: (sudo -u www-data) ./occ twofactorauth:enable username
  ->  Switch authentication method to Standard (Nextcloud password):
  "UPDATE *PREFIX*appconfig SET configvalue = 0 WHERE appid = 'openotp_auth' AND configkey = 'rcdevsopenotp_authentication_method'
  ->  Disable openOTP authentication for one (admin?) user:
  "DELETE FROM *PREFIX*appconfig WHERE userid = '%yourusername%' AND appid = 'openotp_auth' AND configkey = 'enable_openotp'
  Replace *PREFIX* by Nextcloud table prefix 'oc_' by default

### **********   CHANGELOG  **********

1.5.0
	- Add API Key
1.4.7
	- Issue with signature
	- Compatibility with v24 and v25 only
1.4.6
	- Add compatibility for NextCloud v25
	- Documentation URL updated
1.4.5
	- Add dependencies
1.4.4
	- Add compatibility for NextCloud v24
1.4.3
	- Remove checks of SOAP extension and WSDL file
	- Reintroduce the code to deal with CSP to avoid occasionnal
	  issues
1.4.2
	- Fix some users not being able to administer their Two-factor
	- Remove now useless setting to ignore SSL/TLS certificate errors
	- Drop support for Nextcloud versions lower than 21
	- Remove depreciation and improve performance
1.4.1
	- Switch from SoapClient to NuSOAP
	- Fix broken challenge authentication
1.4.0
	- Add a setting to allow disabling OpenOTP authentication for local users
1.3.0
	- Add a 2nd OpenOTP server URL in settings to be used as a failover if the first one is not responding
	- Ignoring SSL/TLS certificate errors now allows to use an IP address instead of a domain name
1.2.0
	- Add compatibility for NextCloud v23
1.1.0
	- Add compatibility from NextCloud v16 to NextCloud v22
	- Add support for Voice Biometrics authentication
	- Add a setting to ignore SSL/TLS certificate errors
	- Fix FIDO / FIDO2 for Safari
1.0.5
1.0.4-1
	- Fixed Add all users to IRegistry when saving plugin configuration
1.0.4
	- Add Statefull state to IRegistry: https://github.com/nextcloud/server/pull/9632
	-  Admins can enable or disable 2FA for all users, this change give the possibility to be "statefull" in other word
	   we have to register enable/disable state for all users in IRegistry during plugin configuration (all user IRegistry will be populated at first config)
	- FIDO2 now supported, All FIDO protocols fully supported:
		 U2F: OpenOTP operated with CTAP1 only
		 FIDO2: OpenOTP operates with CTAP2 only (WebAuthn)
	- Activate Multilingual
	- Add type to IProvider implemented classes
1.0.3
	- Update register settings scripts
	- Update deprecated classes
1.0.2
	- app:check-code integrity
	- Create new Administration menu entry in left side
	- Personal settings are now situated in Security Section
1.0.1
	Add compatibility to NextCloud v12/13
	- OC_User::getLogoutAttribute() is now deprecated
	- Fixed ajax-loader img not showing while pressing Test button
	- custom_csp in config deprecated - nonce used instead + addDefaultPolicy
	- add Annotation @UseSession to store session
	- add EventListener on DOMContentLoaded in template challenge
	- implement contextual authentication
1.0.0
     Initial public release.
