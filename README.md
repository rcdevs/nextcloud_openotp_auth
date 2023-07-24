# RCDevs OpenOTP

RCDevs OpenOTP Plugin for Nextcloud version 1.5.0
Copyright (c) 2010-2023 RCDevs Security SA, All rights reserved.

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

OpenOTP plugin for Nextcloud enables multi-factor authentication on Admin and User portals.

Users' credentials can be validated: 
- Locally by Nextcloud (Nextcloud local accounts),
- Through an LDAP service (LDAP accounts)

Once the first step of the authentication is successfully validated, the authentication workflow continues through the OpenOTP Plugin for Nextcloud and OpenOTP server(s). 
The plugin will submit an authentication request to OpenOTP server(s) with the provided credentials during the first step (username).
In both scenarios (local and LDAP accounts), for the authentication to work with OpenOTP, the provided username must match a valid WebADM licensed account.

In order to use that plugin, you MUST HAVE OpenOTP Security Suite running in your infrastructure (on-premise or in the cloud).

## OpenOTP Authentication Server

OpenOTP™ is an enterprise-grade user authentication solution based on open standards.
OpenOTP is the most advanced authentication server for your Domain users. It supports the combination of single-factor and multi-factor authentication for user access with One-Time Password technologies (OTP), Mobile Push, FIDO2, Voice Biometrics, PKI and more.
It includes a set of integration plugins and bridges which cover near 100% of the enterprise needs.
OpenOTP is provided as an Enterprise product and a Cloud service, depending on your needs.


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
- Reintroduce the code to deal with CSP to avoid occasionnal issues

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


1.0.4-1
- Fixed Add all users to IRegistry when saving plugin configuration

1.0.4
- Add Statefull state to IRegistry: https://github.com/nextcloud/server/pull/9632
-  Admins can enable or disable 2FA for all users, this change give the possibility to be "statefull" in other word we have to register enable/disable state for all users in IRegistry during plugin configuration (all user IRegistry will be populated at first config)
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
- Add compatibility to NextCloud v12/13
- OC_User::getLogoutAttribute() is now deprecated
- Fixed ajax-loader img not showing while pressing Test button
- custom_csp in config deprecated - nonce used instead + addDefaultPolicy
- add Annotation @UseSession to store session
- add EventListener on DOMContentLoaded in template challenge
- implement contextual authentication

1.0.0
- Initial public release.
