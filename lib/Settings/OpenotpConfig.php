<?php
/**
 * Nexcloud - RCDevs OpenOTP Two-factor Authentication
 *
 * @package openotp_auth
 * @author RCDevs
 * @copyright 2018 RCDEVS info@rcdevs.com
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
 * @package openotp_auth
 */
namespace OCA\TwoFactor_RCDevsOpenOTP\Settings;

class OpenotpConfig
{	
	static public $_openotp_configs = array( "server_url1" =>
				array(	'name'	=>	'rcdevsopenotp_server_url1',
						'label'	=>  'OpenOTP server URL #1',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'URL of the openOTP webservice - Should be like http://myserver1:8080/openotp/'
					),
				"server_url2" =>
				array(	'name'	=>	'rcdevsopenotp_server_url2',
						'label'	=>  'OpenOTP server URL #2',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'URL of the openOTP webservice - Should be like http://myserver2:8080/openotp/'
					),
				"client_id" => 
				array(	'name'	=>	'rcdevsopenotp_client_id',
						'label'	=> 'OpenOTP client id',
						'type'	=> 'text',
						'default_value'	=> 'Nextcloud',
						'title'	=>	'Give an Identifiant to the Nextcloud Application (Default Nextcloud) - Allows OpenOTP server to apply specifics policies for requests from Nextcloud app / Displays when receiving OTP via email / Displays in OpenOTP Logs'
					),	
				"api_key" => 
				array(	'name'	=>	'rcdevsopenotp_api_key',
						'label'	=> 'OpenOTP API key',
						'type'	=> 'text',
						'default_value'	=> '',
						'title'	=>	'API key defined in WebADM ; mandatory for WebADM Cloud'
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
				"allow_user_administer_openotp" => 															
				array(	'name'	=>	'rcdevsopenotp_allow_user_administer_openotp',
						'label'	=> 'Allow users to administer Two-factor on their profile settings page',
						'type'	=> 'checkbox',
						'default_value'	=> 'on',
						'title'	=>	'Disable OTP on remote (webdav/Mobile Apps and sync)'
					),
				"disable_otp_local_users" =>
				array(	'name'	=>	'rcdevsopenotp_disable_otp_local_users',
						'label'	=> 'Disable OpenOTP for local users (use standard authentication)',
						'type'	=> 'checkbox',
						'default_value'	=> 'off',
						'title'	=>	'Disable OpenOTP for local users (use standard authentication)'
					),
				"authentication_method" => 															
				array(	'name'	=>	'rcdevsopenotp_authentication_method',
						'label' => 'Authentication method',
						'type'	=> 'radio',
						'default_value'	=> '0',
						'radios' => array( 'authentication_method_std' => array('label' => 'Standard authentication (Disable OpenOTP)',
						 														'value' => '0',		
						 														'checked' => '1',	
																				'title'	=>	'User login with Nextcloud password - OpenOTP'	
																				),
 										   'authentication_method_otp' => array('label' => 'Two-Factor authentication (Enable OpenOTP for all user)',
 						 														'value' => '1',		
 																				'title'	=>	'User login with OpenOTP password'	
 																				)
										)																																								
					),																							
				);
}
