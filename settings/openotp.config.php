<?php
/**
 * Nexcloud - RCDevs OpenOTP Two-factor Authentication
 *
 * @package twofactor_rcdevsopenotp
 * @author Julien RICHARD
 * @copyright 2017 RCDEVS info@rcdevs.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * Displays <a href="http://opensource.org/licenses/AGPL-3.0">GNU AFFERO GENERAL PUBLIC LICENSE</a>
 * @license http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE
 *
 */

/**
 * OpenOTP authentication Config
 * @package twofactor_rcdevsopenotp
 */
namespace OCA\TwoFactor_RCDevsOpenOTP\Settings;

class OpenotpConfig
{
	static public $_openotp_configs = array( "server_url" => 
				array(	'name'	=>	'rcdevsopenotp_server_url',
						'label'	=> 'OpenOTP server URL',
						'type'	=> 'text',
						'default_value'	=> 'http://myserver:8080/openotp/',
						'title'	=>	'URL of the openOTP webservice - Should be like http://myserver:8080/openotp/'
					),
				"client_id" => 
				array(	'name'	=>	'rcdevsopenotp_client_id',
						'label'	=> 'OpenOTP client id',
						'type'	=> 'text',
						'default_value'	=> 'Owncloud',
						'title'	=>	'Give an Identifiant to the owncloud Application (Default Owncloud) - Allows OpenOTP server to apply specifics policies for requests from owncloud app / Displays when receiving OTP via email / Displays in OpenOTP Logs'
					),	
				"default_domain" => 	
				array(	'name'	=>	'rcdevsopenotp_default_domain',
						'label'	=> 'OpenOTP Default Domain',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Put the same name than the Domain you specify in WebADM (where your user are stored)'
					),	
				"user_settings" => 	
				array(	'name'	=>	'rcdevsopenotp_user_settings',
						'label'	=> 'OpenOTP User settings',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Enforce ie: OpenOTP.LoginMode=LDAPOTP or OpenOTP.OTPType=SMS (OpenOTP Application Settings, find it in WebADM) '
					),						
				"proxy_host" => 	
				array(	'name'	=>	'rcdevsopenotp_proxy_host',
						'label'	=> 'Proxy Host',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Proxy Host'
					),	
				"proxy_port" => 																	
				array(	'name'	=>	'rcdevsopenotp_proxy_port',
						'label'	=> 'Proxy Port',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Proxy Port'
					),	
				"proxy_username" => 		
				array(	'name'	=>	'rcdevsopenotp_proxy_username',
						'label'	=> 'Proxy Username',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Proxy Username'
					),			
				"proxy_password" => 															
				array(	'name'	=>	'rcdevsopenotp_proxy_password',
						'label'	=> 'Proxy Password',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'Proxy Password'
					),			
				"disable_openotp_on_remote" => 															
				array(	'name'	=>	'rcdevsopenotp_disable_openotp_on_remote',
						'label'	=> 'Force Remote Password on Desktop/Mobile Apps authentication. If unchecked, an LDAP only authentication request is sent to OTP server (User settings "openOTP.loginMode=LDAP" overwrites User settings configuration)',
						'type'	=> 'checkbox',
						'default_value'	=> 'on',
						'title'	=>	'Disable OTP on remote (webdav/Mobile Apps and sync)'
					),
				"allow_user_administer_openotp" => 															
				array(	'name'	=>	'rcdevsopenotp_allow_user_administer_openotp',
						'label'	=> 'Allow users to administer Two-factor on their profile settings page',
						'type'	=> 'checkbox',
						'default_value'	=> 'on',
						'title'	=>	'Disable OTP on remote (webdav/Mobile Apps and sync)'
					),
				"autocreate_users" =>
				array(	'name'	=>	'rcdevsopenotp_autocreate_user',
						'label'	=> 'Autocreate user on first login - Random password generated, displayname equals login name. If this option is disabled and the user does not exist, then the user will be not allowed to log in ownCloud.',
						'type'	=> 'checkbox',
						'default_value'	=> 'on',
						'title'	=>	'Autocreate user'
					),											
				"authentication_method" => 															
				array(	'name'	=>	'rcdevsopenotp_authentication_method',
						'label' => 'Authentication method',
						'type'	=> 'radio',
						'default_value'	=> '0',
						'radios' => array( 'authentication_method_std' => array('label' => 'Standard authentication (Disable OpenOTP)',
						 														'value' => '0',		
						 														'checked' => '1',	
																				'title'	=>	'User login with Owncloud password - OpenOTP'	
																				),
 										   'authentication_method_otp' => array('label' => 'Two-Factor authentication (Enable OpenOTP for all user)',
 						 														'value' => '1',		
 																				'title'	=>	'User login with OpenOTP password'	
 																				)
										)																																								
					),																							
				);
}
