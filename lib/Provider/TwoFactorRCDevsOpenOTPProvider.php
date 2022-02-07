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
namespace OCA\TwoFactor_RCDevsOpenOTP\Provider;

use OCP\IUser;
use OCP\Template;
use OCP\ILogger;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator; 
use Exception;
use OCP\Authentication\TwoFactorAuth\TwoFactorException;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IL10N;
use OCA\TwoFactor_RCDevsOpenOTP\AuthService\OpenotpAuth;
use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;
use OCP\App\AppPathNotFoundException;
use OCP\App\IAppManager;

class OpenOTPsendRequestException extends Exception
{
}

class TwoFactorRCDevsOpenOTPProvider implements IProvider
{

	/** @obj IConfig $Config */
    private $config;
	/** $obj ILogger $logger */	
    private $logger;
	/** $obj IL10N $trans */	
    private $trans;
	/** $obj Session $session */	
    private $session;
	/** @obj IURLGenerator $urlGenerator */	
    private $urlGenerator;
	/** @obj OpenotpConfig $openotpconfig */
    private $openotpconfig;
	/** @array challenge_params */
    private $challenge_params = array();
	/** @var $openOTPsendRequest */
    private $openOTPsendRequestStatus = "";	
	/** @var IAppManager */
	private $appManager;	

    public function __construct(IConfig $config,
								IAppManager $appManager,
                                ILogger $logger, 
								IRequest $request,
                                IL10N $trans,
                                IURLGenerator $urlGenerator)
    {
        $this->config = $config;
		$this->appManager = $appManager;
        $this->logger = $logger;
        $this->trans = $trans;
        $this->request = $request;
		$this->session = \OC::$server->getSession();
		$this->openotpconfig = OpenotpConfig::$_openotp_configs;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Get unique identifier of this 2FA provider
     *
     * @return string
     */
    public function getId(): string
    {
        return 'rcdevsopenotp';
    }

    /**
     * Get the display name for selecting the 2FA provider
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'RCDevs OpenOTP';
    }

    /**
     * Get the description for selecting the 2FA provider
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Two-Factor RCDevs OpenOTP';
    }

    /**
     * Retrieve a value from openotp_auth
     *
     * @param string $key application config key
     * @param string $default application default value
     * @return string value
     */
    private function getAppValue($key, $default = NULL)
    {
        return $this->config->getAppValue('openotp_auth', $key, $default);
    }

    /**
     * Retrieve all values from openotp_auth
     *
     * @param string $key application config key
     * @param string $default application default value
     * @return string value
     */
    private function getAllAppValue()
    {
		$configs = array();
		foreach( $this->openotpconfig as $_openotp_confname => $_openotp_config ){				
			$configs[$_openotp_config['name']] = $this->getAppValue( $_openotp_config['name'], $_openotp_config['default_value']);
		}
		
        return $configs;
    }

	
    /**
     * 
     *
     * @UseSession
     * @param string $user user
     * @param string $otp OTP
     * @throws OpenOTPsendRequestException
     */
    private function openOTPsendRequest($user, $otp = NULL, $sample = NULL)
    {
		$message = array();
		$params = array();
		$username = $user->getUID();
		//Clean Session Nonce /!\  must be used only for Push request response
		$this->session->remove('rcdevsopenotp_nonce');
		$this->logger->info("********* New OpenOTP Authentication *********", array('app' => 'openotp_auth'));
		
		$params = $this->getAllAppValue();
		
		$params['rcdevsopenotp_remote_addr'] = $this->request->getRemoteAddress();
		try {
			$appPath = $this->appManager->getAppPath('openotp_auth');
		} catch (AppPathNotFoundException $e) {}		
		//TODO: OC_App - Static method of private class must not be called
		$appWebPath = \OC_App::getAppWebPath('openotp_auth');
		
		$openotpAuth = new OpenotpAuth($this->logger, $params, $appPath);
		
		// check OpenOTP WSDL file
		if (!$openotpAuth->checkFile('lib/Provider/openotp.wsdl')){
			$this->logger->error("Could not load OpenOTP WSDL file.", array('app' => 'openotp_auth'));
			$message = $this->trans->t("Could not load OpenOTP WSDL file.");
			throw new OpenOTPsendRequestException($message);
		}
			
		// Check SOAP extension is loaded
		if (!$openotpAuth->checkSOAPext()){
			$this->logger->error("Your PHP installation is missing the SOAP extension.", array('app' => 'openotp_auth'));
			$message = $this->trans->t("Your PHP installation is missing the SOAP extension.");
			throw new OpenOTPsendRequestException($message);
		}		
		
		// Get context cookie
		$context_name = $openotpAuth->getContext_name();
		$context_size = $openotpAuth->getContext_size();
		$context_time = $openotpAuth->getContext_time();
		
		if (isset($_COOKIE[$context_name])) $context = $_COOKIE[$context_name];
		else $context = bin2hex(openssl_random_pseudo_bytes($context_size/2));	
		
		$domain = "";
		$password = NULL;
		/* Don't check LDAP password, validate localy OR via third party User integration (LDAP plugin, etc...) */
		$option = "-LDAP,WEBAUTH";
		
		$POST = array();
		$POST[] = $this->request->getParam("password");		
		
		$u2f = isset($_POST['openotp_u2f']) ? $_POST['openotp_u2f'] : "";
		if ($u2f !== "") $otp = NULL;
		$state = isset($_POST['rcdevsopenotp_session']) ? $_POST['rcdevsopenotp_session'] : "";
		
		$t_domain = $openotpAuth->getDomain($username);
		if (is_array($t_domain)){
			$username = $t_domain['username'];
			$domain = $t_domain['domain'];
		}elseif (isset($_POST['rcdevsopenotp_domain']) && $_POST['rcdevsopenotp_domain'] !== "") $domain = $_POST['rcdevsopenotp_domain'];
		else $domain = $t_domain;
		if ($domain !== "") $this->logger->info("Domain found in username field", array('app' => 'openotp_auth'));
		
		if ($state !== "") {
			// OpenOTP Challenge
			$this->logger->info("New OpenOTP Challenge for user " . $username, array('app' => 'openotp_auth'));
			$resp = $openotpAuth->openOTPChallenge( $username, $domain, $state, $otp, $u2f, $sample );
		} else {
			// OpenOTP Login
			$this->logger->info( "New OpenOTP SimpleLogin for user " . $username, array('app' => 'openotp_auth'));
			$resp = $openotpAuth->openOTPSimpleLogin( $username, $domain, $password, $option, $context );
		}		
		
		if (!$resp || !isset($resp['code'])) {
			$this->logger->error("Invalid OpenOTP response for user " . $username, array('app' => 'openotp_auth'));
			$message[] = $this->trans->t("Invalid OpenOTP response for user") . " " . $username;
		}
		
		switch ($resp['code']) {
			 case 0:
				if ($resp['message']) $message[] = $resp['message'];
				else $message[] = $this->trans->t("OpenOTP Authentication failed for user ".$username);
				$this->logger->info("OpenOTP Authentication failed for user ".$username , array('app' => 'openotp_auth'));
				$this->openOTPsendRequestStatus = "error";
				break;
			 case 1:
				$this->logger->info("User $username has authenticated with OpenOTP.", array('app' => 'openotp_auth'));
				if(!$state){
					$this->openOTPsendRequestStatus = "pushSuccess";
					$PolicyNonce = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
					$rcdevsopenotp_nonce = sha1($PolicyNonce);

					$this->challenge_params['rcdevsopenotp_nonce'] = $rcdevsopenotp_nonce;
					$this->session->set('rcdevsopenotp_nonce', $rcdevsopenotp_nonce);
				}else $this->openOTPsendRequestStatus = "success";
				
				// set context cookie
				if (extension_loaded('openssl')) {			
					if (strlen($context) === $context_size)	setcookie($context_name, $context, time()+$context_time, '/', NULL, true, true);
				}else{
					$this->logger->info("Openssl extension not loaded - context authentication not available", array('app' => 'openotp_auth'));
				}
				
				break;
			 case 2:
				$this->logger->info("OpenOTP Response require Challenge", array('app' => 'openotp_auth'));

				$this->challenge_params = array( 'rcdevsopenotp_otpChallenge' => $resp['otpChallenge'],
										  'rcdevsopenotp_u2fChallenge' => $resp['u2fChallenge'],
										  'rcdevsopenotp_voiceLogin' => (strstr($resp['otpChallenge'], "VOICE")),
										  'rcdevsopenotp_voiceOnly' => (strcmp($resp['otpChallenge'], "VOICE") == 0),
										  'rcdevsopenotp_message' => $resp['message'],
										  'rcdevsopenotp_username' => $username,
										  'rcdevsopenotp_session' => $resp['session'],
										  'rcdevsopenotp_timeout' => $resp['timeout'],
										  'rcdevsopenotp_password' => $password,
										  'rcdevsopenotp_appPath' => $appPath,
										  'rcdevsopenotp_appWebPath' => $appWebPath,
										  'rcdevsopenotp_domain' => $domain,
									  );
				$this->openOTPsendRequestStatus = "challenge";
				break;
			 default:
			 	$this->trans->t("OpenOTP Authentication failed for user") . " " . $username;
				$this->logger->info("OpenOTP Authentication failed for user ".$username, array('app' => 'openotp_auth'));
				$this->openOTPsendRequestStatus = "error";
				break;
		}			
				
		if($message) throw new OpenOTPsendRequestException(implode(", ",$message));
	}	

    /**
     * Get the template for rending the 2FA provider view
     *
     * @param IUser $user
     * @return Template
     */
    public function getTemplate(IUser $user): Template
    {
        try {
			$this->openOTPsendRequest($user);
        } catch (OpenOTPsendRequestException $e) {
            $error_message = $e->getMessage();
        }

        $template = new Template('openotp_auth', 'challenge');
        $template->assign("userID", $user->getUID());
        $template->assign("status", $this->openOTPsendRequestStatus);
        $template->assign("error_msg", isset($error_message) ? $error_message : "" );
        $template->assign("challenge_params", $this->challenge_params);

        return $template;
    }


    /**
     * Verify the given challenge
     *
     * @UseSession
     * @param IUser $user
     * @param string $challenge => OTP password
     * @return Boolean, True in case of success
     */
    public function verifyChallenge(IUser $user, string $challenge): bool
    {
		/*$this->logger->info("----- verifyChallenge -------:" . $challenge, array('app' => 'openotp_auth'));
    	$this->logger->info("POST NONCE:" . $_POST['rcdevsopenotp_nonce'], array('app' => 'openotp_auth'));
		$this->logger->info("SESSION NONCE:" . $this->session->get('rcdevsopenotp_nonce'), array('app' => 'openotp_auth'));*/
		
		$rcdevsopenotp_nonce = "";
		$nonce = "";
	
		if ($this->session->get('rcdevsopenotp_nonce')){
			$rcdevsopenotp_nonce =  $this->session->get('rcdevsopenotp_nonce');
		 	$this->session->remove('rcdevsopenotp_nonce');
		}		
		if (isset($_POST['rcdevsopenotp_nonce'] )) $nonce = $_POST['rcdevsopenotp_nonce'];
		//$this->logger->info("SESSION NONCE SUPP:" . $this->session->get('rcdevsopenotp_nonce'), array('app' => 'openotp_auth'));
		if($challenge === "passme" && $nonce && $rcdevsopenotp_nonce && $nonce === $rcdevsopenotp_nonce) return true;
		
        try {
			$this->openOTPsendRequest($user, $challenge, isset($_POST['rcdevsopenotp_sample']) ? $_POST['rcdevsopenotp_sample'] : NULL);
        } catch (OpenOTPsendRequestException $e) {
            $error_message = $e->getMessage();
        }
		
		if( $this->openOTPsendRequestStatus && $this->openOTPsendRequestStatus === "success" ){ 
			return true;
		}else{
	        if (class_exists('TwoFactorException')) {
	            // OC >= 9.2
	            throw new TwoFactorException($error_message);
	        } else {
	            // OC <= 9.1
	            return false;
	        }
		}
    }

    /**
     * Decides whether 2FA is enabled for the given user
     * This method is called after the user has successfully finished the first
     * authentication step i.e.
     * He authenticated with username and password.
     *
     * @param IUser $user
     * @return boolean
     */
    public function isTwoFactorAuthEnabledForUser(IUser $user): bool
    {
		// Get User Config
		$user_enable_openotp = $this->config->getUserValue( $user->getUID(), 'openotp_auth', 'enable_openotp');
		$allow_user_administer_openotp = $this->getAppValue('rcdevsopenotp_allow_user_administer_openotp');
		$disable_otp_local_users = $this->getAppValue('rcdevsopenotp_disable_otp_local_users');
		$authentication_method = $this->getAppValue('rcdevsopenotp_authentication_method'); 
		// 0 => AUTHENTICATION_METHOD_STD (Standard)
		// 1 => AUTHENTICATION_METHOD_OTP (OTP)

		if ($disable_otp_local_users === "on" && $user->getBackend()->getBackendName() === "Database") {
			$this->logger->info("2FA NOT ACTIVED", array('app' => 'openotp_auth'));
			return false;
		}

		if (( $allow_user_administer_openotp === "off" && $authentication_method === "1") ||
		    ( $allow_user_administer_openotp === "on" && $user_enable_openotp === "yes") ){
			  $this->logger->info("2FA ACTIVED", array('app' => 'openotp_auth'));		
			  return true;
		  }
		  
		  $this->logger->info("2FA NOT ACTIVED", array('app' => 'openotp_auth'));
		  return false;
    }



}
