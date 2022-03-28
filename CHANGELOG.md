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