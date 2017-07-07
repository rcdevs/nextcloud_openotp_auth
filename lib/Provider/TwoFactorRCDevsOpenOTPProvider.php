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
namespace OCA\TwoFactor_RCDevsOpenOTP\Provider;

use OC_User;
use OCP\IUser;
use OCP\Template;
use OCP\ILogger;
use OCP\IConfig;
use OCP\IRequest;
use Exception;
use OCP\Authentication\TwoFactorAuth\TwoFactorException;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IL10N;
use OCA\TwoFactor_RCDevsOpenOTP\AuthService\OpenotpAuth;
use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;


class TwoFactorRCDevsOpenOTPProvider implements IProvider
{

    private $config;
    private $logger;
    private $trans;
    private $session;
	/**OpenOTP Config */
    private $openotpconfig;

    public function __construct(IConfig $config,
                                ILogger $logger, 
								IRequest $request,
                                IL10N $trans)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->trans = $trans;
        $this->request = $request;
		$this->session = \OC::$server->getSession();
		$this->openotpconfig = OpenotpConfig::$_openotp_configs;
    }

    /**
     * Get unique identifier of this 2FA provider
     *
     * @return string
     */
    public function getId()
    {
        return 'rcdevsopenotp';
    }

    /**
     * Get the display name for selecting the 2FA provider
     *
     * @return string
     */
    public function getDisplayName()
    {
        return 'RCDevs OpenOTP';
    }

    /**
     * Get the description for selecting the 2FA provider
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Two-Factor RCDevs OpenOTP';
    }

    /**
     * Retrieve a value from twofactor_rcdevsopenotp
     *
     * @param string $key application config key
     * @param string $default application default value
     * @return string value
     */
    private function getAppValue($key, $default = NULL)
    {
        return $this->config->getAppValue('twofactor_rcdevsopenotp', $key, $default);
    }

    /**
     * Retrieve all values from twofactor_rcdevsopenotp
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
     * @param string $key application config key
     * @param string $default application default value
     * @return string value
     */
    private function openOTPsendRequest($user, $otp = NULL)
    {
		$message = array();
		$params = array();
		$challenge_params = array();
		$status = "";
		$username = $user->getUID();
		//Clean Session Nonce /!\  must be used only for Push request response
		$this->session->remove('rcdevsopenotp_nonce');
		$this->logger->error("********* New OpenOTP Authentication *********", array('app' => 'twofactor_rcdevsopenotp'));
		
		$params = $this->getAllAppValue();
		
		$params['rcdevsopenotp_remote_addr'] = $this->request->getRemoteAddress();
		$appPath = \OC_App::getAppPath('twofactor_rcdevsopenotp');
		$appWebPath = \OC_App::getAppWebPath('twofactor_rcdevsopenotp');
		
		$openotpAuth = new OpenotpAuth($this->logger, $params, $appPath);
		
		// check OpenOTP WSDL file
		if (!$openotpAuth->checkFile('lib/Provider/openotp.wsdl')){
			$this->logger->error("Could not load OpenOTP WSDL file.", array('app' => 'twofactor_rcdevsopenotp'));
			$message[] = $this->trans->t("Could not load OpenOTP WSDL file.");
		}
			
		// Check SOAP extension is loaded
		if (!$openotpAuth->checkSOAPext()){
			$this->logger->error("Your PHP installation is missing the SOAP extension.", array('app' => 'twofactor_rcdevsopenotp'));
			$message[] = $this->trans->t("Your PHP installation is missing the SOAP extension.");
		}		
		
		$domain = NULL;
		$password = NULL;
		$context = "";
		/* Don't check LDAP password, validate ONLY localy*/
		$option = "-LDAP";
		
		$POST = array();
		$POST[] = $this->request->getParam("password");
		$POST[] = $this->request->getParam("context");
		$this->logger->info("POST: ".serialize($POST), array('app' => 'twofactor_rcdevsopenotp'));
		
		
		$u2f = isset($_POST['openotp_u2f']) ? $_POST['openotp_u2f'] : "";
		$context = isset($_POST['context']) ? $_POST['context'] : "";
		if( $u2f != "" ) $otp = NULL;
		$state = isset($_POST['rcdevsopenotp_session']) ? $_POST['rcdevsopenotp_session'] : "";
		
		$t_domain = $openotpAuth->getDomain($username);
		if (is_array($t_domain)){
			$username = $t_domain['username'];
			$domain = $t_domain['domain'];
		}elseif( isset($_POST['rcdevsopenotp_domain']) && $_POST['rcdevsopenotp_domain'] != NULL) $domain = $_POST['rcdevsopenotp_domain'];
		else $domain = $t_domain;
		if( $domain != "" ) $this->logger->info("Domain found in username field", array('app' => 'twofactor_rcdevsopenotp'));
		
		if ($state != NULL) {
			// OpenOTP Challenge
			$this->logger->info("New OpenOTP Challenge for user " . $username, array('app' => 'twofactor_rcdevsopenotp'));
			$resp = $openotpAuth->openOTPChallenge( $username, $domain, $state, $otp, $u2f );
		} else {
			// OpenOTP Login
			$this->logger->info( "New OpenOTP SimpleLogin for user " . $username, array('app' => 'twofactor_rcdevsopenotp'));
			$resp = $openotpAuth->openOTPSimpleLogin( $username, $domain, $password, $option, $context );
		}		
		
		if (!$resp || !isset($resp['code'])) {
			$this->logger->error("Invalid OpenOTP response for user " . $username, array('app' => 'twofactor_rcdevsopenotp'));
			$message[] = "Invalid OpenOTP response for user " . $username;
		}
		
		switch ($resp['code']) {
			 case 0:
				if ($resp['message']) $message[] = $resp['message'];
				else $message[] = $this->trans->t("OpenOTP Authentication failed for user ".$username);
				$this->logger->info("OpenOTP Authentication failed for user ".$username , array('app' => 'twofactor_rcdevsopenotp'));
				$status = "error";
				break;
			 case 1:
				$this->logger->info("User $username has authenticated with OpenOTP.", array('app' => 'twofactor_rcdevsopenotp'));
				if(!$state){
					$status = "pushSuccess";
					$PolicyNonce = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
					$rcdevsopenotp_nonce = sha1($PolicyNonce);
					$challenge_params['rcdevsopenotp_nonce'] = $rcdevsopenotp_nonce;
					$this->session->set('rcdevsopenotp_nonce', $rcdevsopenotp_nonce);
				}else $status = "success";
				break;
			 case 2:
				$this->logger->info("OpenOTP Response require Challenge", array('app' => 'twofactor_rcdevsopenotp'));

				$challenge_params = array( 'rcdevsopenotp_otpChallenge' => $resp['otpChallenge'],
										  'rcdevsopenotp_u2fChallenge' => $resp['u2fChallenge'],
										  'rcdevsopenotp_message' => $resp['message'],
										  'rcdevsopenotp_username' => $username,
										  'rcdevsopenotp_session' => $resp['session'],
										  'rcdevsopenotp_timeout' => $resp['timeout'],
										  'rcdevsopenotp_password' => $password,
										  'rcdevsopenotp_appPath' => $appPath,
										  'rcdevsopenotp_appWebPath' => $appWebPath,
										  'rcdevsopenotp_domain' => $domain,
									  );
				$status = "challenge";
				break;
			 default:
			 	$this->trans->t("OpenOTP Authentication failed for user ".$username);
				$this->logger->info("OpenOTP Authentication failed for user ".$username, array('app' => 'twofactor_rcdevsopenotp'));
				$status = "error";
				break;
		}			
				
		return array('message' => $message,  'challenge_params' => $challenge_params, 'status' => $status);
	}	

    /**
     * Get the template for rending the 2FA provider view
     *
     * @param IUser $user
     * @return Template
     */
    public function getTemplate(IUser $user)
    {
		$response = $this->openOTPsendRequest($user);

        $template = new Template('twofactor_rcdevsopenotp', 'challenge');
        $template->assign("userID", $user->getUID());
        $template->assign("status", $response['status']);
        $template->assign("error_msg", $response['message']);
        $template->assign("logout_attr", $l = OC_User::getLogoutAttribute());
        $template->assign("challenge_params", $response['challenge_params']);

        return $template;
    }


    /**
     * Verify the given challenge
     *
     * @param IUser $user
     * @param string $challenge => OTP password
     * @return Boolean, True in case of success
     */
    public function verifyChallenge(IUser $user, $challenge)
    {
		/*$this->logger->info("----- verifyChallenge -------:" . $challenge, array('app' => 'twofactor_rcdevsopenotp'));
    	$this->logger->info("POST NONCE:" . $_POST['rcdevsopenotp_nonce'], array('app' => 'twofactor_rcdevsopenotp'));
		$this->logger->info("SESSION NONCE:" . $this->session->get('rcdevsopenotp_nonce'), array('app' => 'twofactor_rcdevsopenotp'));*/
		
		$rcdevsopenotp_nonce = "";
		$nonce = "";
	
		if( $this->session->get('rcdevsopenotp_nonce')){
			$rcdevsopenotp_nonce =  $this->session->get('rcdevsopenotp_nonce');
		 	$this->session->remove('rcdevsopenotp_nonce');
		}		
		if( $_POST['rcdevsopenotp_nonce'] ) $nonce = $_POST['rcdevsopenotp_nonce'];
		//$this->logger->info("SESSION NONCE SUPP:" . $this->session->get('rcdevsopenotp_nonce'), array('app' => 'twofactor_rcdevsopenotp'));
		if($challenge == "passme" && $nonce && $rcdevsopenotp_nonce && $nonce === $rcdevsopenotp_nonce) return true;
		
		$response = $this->openOTPsendRequest($user, $challenge);
		if($response['status'] && $response['status'] == "success"){ 
			return true;
		}else{
	        if (class_exists('TwoFactorException')) {
	            // OC >= 9.2
	            throw new TwoFactorException(current($error_message));
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
    public function isTwoFactorAuthEnabledForUser(IUser $user)
    {
		// Get User Config
		$user_enable_openotp = $this->config->getUserValue( $user->getUID(), 'twofactor_rcdevsopenotp', 'enable_openotp');
		$allow_user_administer_openotp = $this->getAppValue('rcdevsopenotp_allow_user_administer_openotp');
		$authentication_method = $this->getAppValue('rcdevsopenotp_authentication_method'); 
		// 0 => AUTHENTICATION_METHOD_STD (Standard)
		// 1 => AUTHENTICATION_METHOD_OTP (OTP)

		if( ( $allow_user_administer_openotp === "off" && $authentication_method === "1") ||
		    ( $allow_user_administer_openotp === "on" && $user_enable_openotp === "yes") ){
			  //$this->logger->info("2FA ACTIVED", array('app' => 'twofactor_rcdevsopenotp'));		
			  return true;
		  }
		  
		  //$this->logger->info("2FA NOT ACTIVED", array('app' => 'twofactor_rcdevsopenotp'));		
		  return false;
    }



}
