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
use OCP\IUserManager;
use Exception;
use OCA\TwoFactor_RCDevsOpenOTP\AuthService\OpenotpAuth;
use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;
use OCA\TwoFactor_RCDevsOpenOTP\Event\StateChanged;
use OCP\App\IAppManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


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
	/** @var IAppManager */
	private $appManager;	
	/** @var IUserManager */
	protected $userManager;
	/** @var EventDispatcherInterface */
	private $eventDispatcher;	
	
	
    /**
	 * @param string $appName
	 * @param User $User
	 * @param IRequest $request
	 * @param IL10N $l10n
	 * @param IConfig $config
	 * @param ILogger $logger
	 * @param IAppManager $appManager
	 * @param IUserManager $userManager
	 */

	public function __construct($AppName, $User, IRequest $request, IL10N $l10n, IConfig $config, ILogger $logger, IAppManager $appManager, IUserManager $userManager, EventDispatcherInterface $eventDispatcher) {
		parent::__construct($AppName, $request);
		$this->l10n = $l10n;
		$this->User = $User;
		
        $this->config = $config;
		$this->appManager = $appManager;		
		$this->userManager = $userManager;		
		$this->eventDispatcher = $eventDispatcher;		
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
				return new DataResponse(['status' => "error", 'message' => $this->l10n->t("You must fill openotp settings before saving") ]);
			
			foreach( $this->openotpconfig as $_openotp_confname => $_openotp_config ){
				if( $_openotp_config['type'] === "checkbox" && !isset( $POST[$_openotp_config['name']] ) )
					$this->config->setAppValue('twofactor_rcdevsopenotp', $_openotp_config['name'], "off");
				else{
					if( isset($POST[$_openotp_config['name']]) && $POST[$_openotp_config['name']] === "" && isset($_openotp_config['default_value']) ){
						$this->config->setAppValue( 'twofactor_rcdevsopenotp', $_openotp_config['name'], $_openotp_config['default_value'] );
					}else{
						//$this->logger->debug("setAppValue Name: " . $_openotp_config['name'], array('app' => 'rcdevsopenotp'));
						//$this->logger->debug("setAppValue Value: " . $POST[$_openotp_config['name']], array('app' => 'rcdevsopenotp'));
						$this->config->setAppValue( 'twofactor_rcdevsopenotp', $_openotp_config['name'], $POST[$_openotp_config['name']] );
					}
				}
			}

			if( !isset( $POST["rcdevsopenotp_allow_user_administer_openotp"] ) && $POST["rcdevsopenotp_authentication_method"] === 1 ){
				  $this->logger->debug("*********  2FA state is Enabled for everybody  ********* ", array('app' => 'twofactor_rcdevsopenotp'));
				  $stateChanged = true;
			}elseif( !isset( $POST["rcdevsopenotp_allow_user_administer_openotp"] ) && $POST["rcdevsopenotp_authentication_method"] === 0 ){
				  $this->logger->debug("*********  2FA state is disabled for everyBody  ********* ", array('app' => 'twofactor_rcdevsopenotp'));
				  $stateChanged = false;
			}else{
				  $this->logger->debug("*********  Silence is golden - No 2FA State  ********* ", array('app' => 'twofactor_rcdevsopenotp'));				
				  $stateChanged = "";
			}

			// https://github.com/nextcloud/server/pull/9632
			// Admins can enable or disable 2FA for all users, this change give the possibility to be "statefull" in other word
			// we have to register enable/disable state for all users in IRegistry during plugin configuration (all user IRegistry will be populated at first config)						
			foreach($this->userManager->getBackends() as $backend) {
				$limit = 500;
				$offset = 0;
				do {
					$users = $backend->getUsers('', $limit, $offset);
					foreach ($users as $user) {
						if( $stateChanged !== "" ){
						  $this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($this->userManager->get($user), $stateChanged));
						} 
					}
					$offset += $limit;
				} while(count($users) >= $limit);
			}			
			return new DataResponse(['status' => "success", 'message' => $this->l10n->t("Your settings have been saved succesfully") ]);
	    }

		// Personal Settings
	    if( !$POST ) return new DataResponse(['status' => "error", 'message' => $this->l10n->t("An error occured, please contact administrator") ]);
		
		if( $POST && isset($POST["openotp_psettings_sent"]) ){	
			if( isset($POST["enable_openotp"]) ){
				$this->config->setUserValue( $this->User->getUID(), 'twofactor_rcdevsopenotp', 'enable_openotp', $POST["enable_openotp"] );
				
				if( $POST["enable_openotp"] === "yes" ){
					$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($this->User, true));
					$this->logger->debug("*********  2FA state is Enable for user  ********* ".$this->User->getUID(), array('app' => 'twofactor_rcdevsopenotp'));
				}else{
					$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($this->User, false));
					$this->logger->debug("*********  2FA state is Disable for user  ********* ".$this->User->getUID(), array('app' => 'twofactor_rcdevsopenotp'));
				}
				return new DataResponse(['status' => "success", 'message' => $this->l10n->t("Your settings have been saved succesfully") ]);
			}
		}else
			return new DataResponse(['status' => "error", 'message' => $this->l10n->t("An error occured, please contact administrator") ]);
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
		try {
			$appPath = $this->appManager->getAppPath('twofactor_rcdevsopenotp');
		} catch (AppPathNotFoundException $e) {}		
		
		$openotpAuth = new openotpAuth($this->logger, $params, $appPath);
		try{
			$resp = $openotpAuth->openOTPStatus();
		}catch(exception $e){}
		
		if( isset($resp) )
			return new DataResponse(['status' => "success", 'openotpStatus' => $resp['status'], 'message' => nl2br($resp['message']) ]);
		else{
			$this->logger->error("Could not connect to host", array('app' => 'twofactor_rcdevsopenotp'));
			return new DataResponse(['status' => "error", 'message' => $this->l10n->t('Could not connect to host') ]);
		}		
	}
}

