<?php
/**
 * Nexcloud - RCDevs OpenOTP Two-factor Authentication
 *
 * @package twofactor_rcdevsopenotp
 * @author Julien RICHARD
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

namespace OCA\TwoFactor_RCDevsOpenOTP\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IConfig;
use OCP\ILogger;
use Exception;
use OCA\TwoFactor_RCDevsOpenOTP\AuthService\OpenotpAuth;
use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;


class OpenotpAuthException extends Exception
{
}
class SettingsController extends Controller {
	/** @var IL10N */
	private $l10n;
    /** User Object */
	private $User;
	/** configuration object */
    private $config;
	/** Logger object */
    private $logger;
	/** OpenOTP Config */
    private $openotpconfig;
	
    /**
	 * @param string $appName
	 * @param User $User
	 * @param IRequest $request
	 * @param IL10N $l10n
	 * @param IConfig $config
	 * @param ILogger $logger
	 */
	public function __construct($AppName, $User, IRequest $request, IL10N $l10n, IConfig $config, ILogger $logger) {
		parent::__construct($AppName, $request);
		$this->l10n = $l10n;
		$this->User = $User;
        $this->config = $config;
		$this->logger = $logger;
		$this->openotpconfig = OpenotpConfig::$_openotp_configs;
	}
	
	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	
	public function index() {
		
		foreach( $this->openotpconfig as $_openotp_confname => $_openotp_config ){
		    $params[$_openotp_config['name']] = $this->config->getAppValue( 'twofactor_rcdevsopenotp',$_openotp_config['name'],$_openotp_config['default_value'] );
		}
		
		$this->logger->debug("++ User ++  from Class " . get_class($this->User), array('app' => 'rcdevsopenotp'));
		$params['user'] = $this->User->getUID();
		$params['openotp_allconfig'] = $this->openotpconfig;

		return new TemplateResponse('twofactor_rcdevsopenotp', 'settings-admin', $params);
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function saveconfig( $post ){
		parse_str($post, $POST);		

	    // Admin Settings && Application Settings page
		if( $POST && isset($POST["openotp_settings_sent"]) ){
			if( $POST['rcdevsopenotp_server_url'] === "" &&  $POST['rcdevsopenotp_client_id'] === ""
			&&	$POST['rcdevsopenotp_default_domain']  === "" && $POST['rcdevsopenotp_proxy_host']  === "" 
			&&	$POST['rcdevsopenotp_proxy_port']  === "" && $POST['rcdevsopenotp_proxy_login']  === ""
			&&	$POST['rcdevsopenotp_proxy_password']  === "" )
				return new DataResponse(['status' => "error", 'message' => "You must fill openotp settings before saving" ]);
			
			foreach( $this->openotpconfig as $_openotp_confname => $_openotp_config ){
				if($_openotp_config['type'] === "checkbox" && !isset( $POST[$_openotp_config['name']] ) )
					$this->config->setAppValue('twofactor_rcdevsopenotp', $_openotp_config['name'], "off");
				else{
					if( isset($POST[$_openotp_config['name']]) && $POST[$_openotp_config['name']] == "" && isset($_openotp_config['default_value']) ){
						$this->config->setAppValue( 'twofactor_rcdevsopenotp', $_openotp_config['name'], $_openotp_config['default_value'] );
					}else{
						//$this->logger->debug("setAppValue Name: " . $_openotp_config['name'], array('app' => 'rcdevsopenotp'));
						//$this->logger->debug("setAppValue Value: " . $POST[$_openotp_config['name']], array('app' => 'rcdevsopenotp'));
						$this->config->setAppValue( 'twofactor_rcdevsopenotp', $_openotp_config['name'], $POST[$_openotp_config['name']] );
					}
				}
			}
			return new DataResponse(['status' => "success", 'message' => "Your settings have been saved succesfully" ]);
	    }
		// Personnal Settings
	    if( !$POST ) return new DataResponse(['status' => "error", 'message' => "An error occured, please contact administrator" ]);
		
		if( $POST && isset($POST["openotp_psettings_sent"]) ){	
			if( isset($POST["enable_openotp"]) ) $this->config->setUserValue( $this->User->getUID(), 'twofactor_rcdevsopenotp', 'enable_openotp', $POST["enable_openotp"] );
			
			return new DataResponse(['status' => "success", 'message' => "Your settings have been saved succesfully" ]);
		}else
			return new DataResponse(['status' => "error", 'message' => "An error occured, please contact administrator" ]);
	}
	
	/**
	 * @NoAdminRequired
	 */	
	public function checkServerUrl(){
		$this->logger->info("********* New OpenOTP Authentication Status *********", array('app' => 'twofactor_rcdevsopenotp'));

		$server_url = $_POST['server_url'];
		if( $server_url === "" ) return false;

		// get App Configs
		$_openotp_configs = OpenotpConfig::$_openotp_configs;
		
		foreach( $_openotp_configs as $_openotp_confname => $_openotp_config ){				
	        $params[$_openotp_config['name']] = $this->config->getAppValue(
	            'twofactor_rcdevsopenotp', $_openotp_config['name'], $_openotp_config['default_value']
	        );
		}
		$params['rcdevsopenotp_remote_addr'] = $this->request->getRemoteAddress();
		$params['rcdevsopenotp_server_url'] = stripslashes($server_url);
		$appPath = \OC_App::getAppPath('twofactor_rcdevsopenotp');
		
		$openotpAuth = new openotpAuth($this->logger, $params, $appPath);
		try{
			$resp = $openotpAuth->openOTPStatus();
		}catch(exception $e){}
		
		if( isset($resp) )
			return new DataResponse(['status' => "success", 'openotpStatus' => $resp['status'], 'message' => nl2br($resp['message']) ]);
		else{
			$this->logger->error("Could not connect to host", array('app' => 'twofactor_rcdevsopenotp'));
			return new DataResponse(['status' => "error", 'message' => 'Could not connect to host' ]);
		}		
	}
}

