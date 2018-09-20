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

namespace OCA\TwoFactor_RCDevsOpenOTP\AuthService;

use OCP\AppFramework\App;
use OCP\ILogger;
use Exception;

class OpenotpAuthException extends Exception
{
}

class OpenotpAuth{ 

	/** @var home */
	private $home;
	/** @var server_url */
	private $server_url;
	/** @var client_id */
	private $client_id;
	/** @var default_domain */
	private $default_domain;
	/** @var user_settings */
	private $user_settings;                                                                           
	/** @var proxy_host */
	private $proxy_host;                                                                              
	/** @var proxy_port */
	private $proxy_port;                                                                              
	/** @var proxy_username */
	private $proxy_username;
	/** @var proxy_password */
	private $proxy_password;
	/** SoapClientTimeout object */
	private $soap_client;
	/** Logger object */
    private $logger;
	/** @var context_name */
	private $context_name = '__Host-OpenOTPContext';
	/** @var context_size */
	private $context_size = 32;
	/** @var context_time */
	private $context_time = 2500000;	

    /**
	 * @param ILogger $logger
	 * @param Array $params
	 * @param String $home
	 */
	public function __construct(ILogger $logger, $params, $home='' ){

	    $this->home = $home;
		$this->logger = $logger;
		// load config		
		$this->server_url = $params['rcdevsopenotp_server_url'];
		$this->client_id = $params['rcdevsopenotp_client_id'];
		$this->remote_addr = $params['rcdevsopenotp_remote_addr'];
		$this->default_domain = $params['rcdevsopenotp_default_domain'];
		$this->user_settings = $params['rcdevsopenotp_user_settings'];                                                                                   
		$this->proxy_host = $params['rcdevsopenotp_proxy_host'];                                                                               
		$this->proxy_port = $params['rcdevsopenotp_proxy_port'];                                                                               
		$this->proxy_username = $params['rcdevsopenotp_proxy_username'];
		$this->proxy_password = $params['rcdevsopenotp_proxy_password'];
		
	}
	
	public function checkFile($file)
	{
		if (!file_exists($this->home."/".$file)) {
			return false;
		}
		return true;
	}
	
	public function checkSOAPext()
	{
		if (!extension_loaded('soap')) {
			return false;
		}
		return true;
	}
			
	public function getServer_url()
	{
		return $this->server_url;
	}
	
	public function getScope()
	{
		return $this->openotp_scope;
	}
		
	public function getDomain($username)
	{
		$pos = strpos($username, "\\");
		if ($pos) {
			$ret['domain'] = substr($username, 0, $pos);
			$ret['username'] = substr($username, $pos+1);
		} else {                                                                                                                      
			$ret = $this->default_domain;
		}
		return $ret;
	}
	public function getContext_name()
	{
		return $this->context_name;
	}
	public function getContext_size()
	{
		return $this->context_size;
	}	
	public function getContext_time()
	{
		return $this->context_time;
	}	
	
	public static function getOverlay($otpChallenge, $u2fChallenge, $message, $username, $session, $timeout, $ldappw, $path, $appWebPath, $domain=NULL){
		$appWebPath .= "/images";
		
		$overlay = <<<EOT

		$(document).ready(function(){
		function addOpenOTPDivs(){
			var overlay_bg = document.createElement("div");
			overlay_bg.id = 'openotp_overlay_bg';
			overlay_bg.style.position = 'fixed'; 
			overlay_bg.style.top = '0'; 
			overlay_bg.style.left = '0'; 
			overlay_bg.style.width = '100%'; 
			overlay_bg.style.height = '100%'; 
			overlay_bg.style.background = 'grey';
			overlay_bg.style.zIndex = "9998"; 
			overlay_bg.style["filter"] = "0.9";
			overlay_bg.style["-moz-opacity"] = "0.9";
			overlay_bg.style["-khtml-opacity"] = "0.9";
			overlay_bg.style["opacity"] = "0.9";
		
			var tokenform = document.getElementsByName("requesttoken")[0].value;
			var timezone = document.getElementById("timezone").value;
			var timezone_offset = document.getElementById("timezone_offset").value;
			var remember_login = document.getElementById("remember_login").value;
			var context = document.getElementsByName("context")[0].value;
			var overlay = document.createElement("div");
			overlay.id = 'openotp_overlay';
			overlay.style.position = 'absolute'; 
			overlay.style.top = '165px'; 
			overlay.style.left = '50%'; 
			overlay.style.width = '280px'; 
			overlay.style.marginLeft = '-180px';
			overlay.style.padding = '65px 40px 50px 40px';
			overlay.style.background = 'url($appWebPath/openotp_banner.png) no-repeat top left #E4E4E4';
			overlay.style.border = '5px solid #545454';
			overlay.style.borderRadius = '10px';
			overlay.style.MozBorderRadius = '10px';
			overlay.style.WebkitBorderRadius = '10px';
			overlay.style.boxShadow = '1px 1px 12px #555555';
			overlay.style.WebkitBoxShadow = '1px 1px 12px #555555';
			overlay.style.MozBoxShadow = '1px 1px 12px #555555';
			overlay.style.zIndex = "9999"; 
			overlay.innerHTML = '<a style="position:absolute; top:-12px; right:-12px; background-color:transparent;" href="index.php" title="close"><img src="$appWebPath/openotp_closebtn.png"/></a>'
			+ '<style>'
			+ 'blink { -webkit-animation: blink 1s steps(5, start) infinite; -moz-animation:    blink 1s steps(5, start) infinite; -o-animation:      blink 1s steps(5, start) infinite; animation: blink 1s steps(5, start) infinite; }'
			+ '	@-webkit-keyframes blink { to { visibility: hidden; } }'
			+ '@-moz-keyframes blink { to { visibility: hidden; } }'
			+ '@-o-keyframes blink { to { visibility: hidden; } }'
			+ '@keyframes blink { to { visibility: hidden; } }'
			+ '#openotp_overlay tbody tr:hover, #openotp_overlay tbody tr:active, #openotp_overlay tbody tr:focus{ background:none; }'
			+ '#body-login #openotp_overlay .button{ border:1px solid rgba(190, 190, 190, 0.9); }'
			+ '</style>'			
			+ '<div style="background-color:red; margin:0 -40px 0; height:4px; width:360px; padding:0;" id="count_red"><div style="background-color:orange; margin:0; height:4px; width:360px; padding:0;" id="div_orange"></div></div>'
			+ '<form style="margin-top:30px;" id="formlogin" name="login" method="POST">'
			+ '<input type="hidden" name="requesttoken" value="'+tokenform+'">'
			+ '<input type="hidden" id="timezone" name="timezone" value="'+timezone+'">'
			+ '<input type="hidden" id="timezone_offset" name="timezone_offset" value="'+timezone_offset+'">'
			+ '<input type="hidden" id="remember_login" name="remember_login" value="0" value="'+remember_login+'">'
			+ '<input type="hidden" name="context" value="'+context+'">'
			+ '<input type="hidden" name="openotp_state" value="$session">'
			+ '<input type="hidden" name="openotp_domain" value="$domain">'
			+ '<input type="hidden" name="user" value="$username">'
			+ '<input type="hidden" name="password" value="$ldappw">'
			+ '<table width="100%">'
			+ '<tr style="border:none;"><td style="text-align:center; font-weight:bold; font-size:14px; border:none;">$message</td></tr>'
			+ '<tr style="border:none;"><td id="timout_cell" style="text-align:center; padding-top:4px; font-weight:bold; font-style:italic; font-size:11px; border:none;">Timeout: <span id="timeout">$timeout seconds</span></td></tr>'
EOT;
			
			if( $otpChallenge || ( !$otpChallenge && !$u2fChallenge ) ){		
			$overlay .= <<<EOT
			+ '<tr style="border:none;"><td id="inputs_cell" style="text-align:center; padding-top:25px; border:none;"><input style="width:165px; border:1px solid grey; background-color:white; margin-bottom:0; padding:3px; vertical-align:middle;" type="password" size=15 name="openotp_password" id="openotp_password">&nbsp;'
			+ '<input style="vertical-align:middle; padding:5px 10px; margin:5px 5px 0 0;" type="submit" value="Ok" class="button btn btn-primary"></td></tr>'
EOT;
			}
			
			if( $u2fChallenge ){		
			$overlay .= "+ '<tr style=\"border:none;\"><td id=\"inputs_cell\" style=\"text-align:center; padding-top:5px; border:none;\"><input type=\"hidden\" name=\"openotp_u2f\" value=\"\">'";
				if( $otpChallenge ){		
					$overlay .= "+ '<b>U2F response</b> &nbsp; <blink id=\"u2f_activate\">[Activate Device]</blink></td></tr>'";
				} else { 
					$overlay .= "+ '<img src=\"" . $appWebPath . "/u2f.png\"><br><br><blink id=\"u2f_activate\">[Activate Device]</blink></td></tr>'";
				}			
			}			

			$overlay .= <<<EOT
			+ '</table></form>';
			
			document.body.appendChild(overlay_bg);    
			document.body.appendChild(overlay); 
		}
		
		addOpenOTPDivs();
		
		/* Compute Timeout */	
		var c = $timeout;
		var base = $timeout;
		function count()
		{
			plural = c <= 1 ? "" : "s";
			document.getElementById("timeout").innerHTML = c + " second" + plural;
			var div_width = 360;
			var new_width =  Math.round(c*div_width/base);
			document.getElementById('div_orange').style.width=new_width+'px';
			
			if( document.getElementById('openotp_password') ){
				document.getElementById('openotp_password').focus();
			}
			if(c == 0 || c < 0) {
				c = 0;
				clearInterval(timer);
				document.getElementById("timout_cell").innerHTML = " <b style='color:red;'>Login timedout!</b> ";
				document.getElementById("inputs_cell").innerHTML = "<input style='padding:3px 20px;' type='button' value='Retry' class='button btn btn-primary' onclick='window.location.href=\"./\"'>";
			}
			c--;
		}
		count();
		
		
		function getInternetExplorerVersion() {
		
			var rv = -1;
		
			if (navigator.appName == "Microsoft Internet Explorer") {
				var ua = navigator.userAgent;
				var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
				if (re.exec(ua) != null)
					rv = parseFloat(RegExp.$1);
			}
			return rv;
		}
		
		var ver = getInternetExplorerVersion();
		
		if (navigator.appName == "Microsoft Internet Explorer"){
			if (ver <= 10){
				toggleItem = function(){
					
				    var el = document.getElementsByTagName("blink")[0];
				    if (el.style.display === "block") {
				        el.style.display = "none";
				    } else {
				        el.style.display = "block";
				    }
				}
				var t = setInterval(function() {toggleItem; }, 1000);
			}
		}
		
		var timer = setInterval(function() {count();  }, 1000);
		});
		
EOT;
		
		if( $u2fChallenge ){ 
			
			$overlay .= " $(document).ready(function(){ " . "\r\n";
			$overlay .= "if (/chrome|chromium|firefox|opera/.test(navigator.userAgent.toLowerCase())) {
			    var u2f_request = ".$u2fChallenge.";
			    var u2f_regkeys = [];
			    for (var i=0, len=u2f_request.keyHandles.length; i<len; i++) {
			        u2f_regkeys.push({version:u2f_request.version,keyHandle:u2f_request.keyHandles[i]});
			    }
			    u2f.sign(u2f_request.appId, u2f_request.challenge, u2f_regkeys, function(response) {
					document.getElementsByName('openotp_u2f')[0].value = JSON.stringify(response); 
					document.getElementById('formlogin').submit();					
			    }, $timeout ); }" . "\r\n";
			$overlay .= " else { 
				var u2f_activate = document.getElementById('u2f_activate'); 
				u2f_activate.innerHTML = '[Not Supported]'; 
				u2f_activate.style.color='red'; 
				}" . "\r\n";
			$overlay .= " }); " . "\r\n";			
		}
		
		return $overlay;
	}
	
	private function soapRequest(){
	
		$options = array('location' => $this->server_url);
		if ($this->proxy_host !== NULL && $this->proxy_port !== NULL) {
			$options['proxy_host'] = $this->proxy_host;
			$options['proxy_port'] = $this->proxy_port;
			if ($this->proxy_username !== NULL && $this->proxy_password !== NULL) {
				$options['proxy_login'] = $this->proxy_username;
				$options['proxy_password'] = $this->proxy_password;
			}
		}
		
		$stream_context = stream_context_create(array('ssl' => array('verify_peer' => false)));
		if ($stream_context){
			$options['stream_context'] = $stream_context;
		}		
		
		try{	
			$soap_client = new SoapClientTimeout(dirname(__FILE__).'/openotp.wsdl', $options);
		}catch(exception $e){
			$message = __METHOD__.', exception: '.$e->getMessage();
			throw new OpenotpAuthException($message);
		}

		$soap_client->setTimeout(30);	
		$soap_client->setVersion(2);
		$soap_client->setLogger($this->logger);
		
		$this->soap_client = $soap_client;	
		return true;
	}
		
	public function openOTPSimpleLogin($username, $domain, $password, $option, $context){
		try{
			$this->soapRequest();
			$resp = $this->soap_client->openotpSimpleLogin($username, $domain, $password, $this->client_id, $this->remote_addr, $this->user_settings, $option, $context, null);
		}catch(exception $e){
			$message = __METHOD__.', exception: '.$e->getMessage();
			$this->logger->error($message, array('app' => 'rcdevsopenotp'));
		}
		return isset($resp) ? $resp : false;
	}
	
	public function openOTPChallenge($username, $domain, $state, $password, $u2f){
		try{
			$this->soapRequest();
			$resp = $this->soap_client->openotpChallenge($username, $domain, $state, $password, $u2f);
		}catch(exception $e){
			$message = __METHOD__.', exception: '.$e->getMessage();
			$this->logger->error($message, array('app' => 'rcdevsopenotp'));
		}
		return isset($resp) ? $resp : false;
	}
	
	public function openOTPStatus(){
		try{
			$this->soapRequest();
			$resp = $this->soap_client->openotpStatus();
		}catch(exception $e){
			$message = __METHOD__.', exception: '.$e->getMessage();
			$this->logger->error($message, array('app' => 'rcdevsopenotp'));
			throw new OpenotpAuthException($message);
		}

		return $resp;
	}
}



class SoapClientTimeout extends \SoapClient {
    private $timeout;
    private $version;
    private $logger;

    public function setTimeout ($timeout) {
        $this->timeout = $timeout;
    }
    public function setVersion ($version) {
        $this->version = $version;
    }
    public function setLogger ($logger) {
        $this->logger = $logger;
    }

    public function __doRequest($request, $location, $action, $version, $one_way=false) {
        if (!$this->timeout) {
            // Call via parent because we require no timeout
            $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        } else {
            // Call via Curl and use the timeout
            $curl = curl_init(trim($location));
			
			
			//$this->logger->error("*************  Location  **********" . trim($location), array('app' => 'rcdevsopenotp'));
			//$this->logger->error("*************  Request  **********" . $request, array('app' => 'rcdevsopenotp'));
			
            curl_setopt($curl, CURLOPT_VERBOSE, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "API-Version: ".strval($this->version)));
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);

            $response = curl_exec($curl);
            if (curl_errno($curl)) throw new Exception(curl_error($curl));
            curl_close($curl);
        }

        if (!$one_way) return ($response);
    }
}

