<?php
/**
 * Nexcloud - RCDevs OpenOTP Two-factor Authentication
 *
 * @package twofactor_rcdevsopenotp
 * @author Charly ROHART
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

namespace OCA\TwoFactor_RCDevsOpenOTP\Settings;

use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;
use OCA\TwoFactor_RCDevsOpenOTP\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\IUserSession;
use OCP\ILogger;

class Personal implements ISettings {
	
	/** @var Application */
	private $app;

    /** @var IConfig */
    private $config;

    /** @var IL10N */
    private $l;
	
	/** OpenOTP Config */
    private $openotpconfig;		
	
	/** @var IUserSession */
	private $userSession;	
	
	/** $obj ILogger $logger */	
    private $logger;	
		
	public function __construct(Application $app, IConfig $config, IL10N $l, IUserSession $userSession, ILogger $logger) {
		$this->app = $app;
		$this->config = $config;
		$this->l = $l;
		$this->openotpconfig = OpenotpConfig::$_openotp_configs;
		$this->userSession = $userSession;		
		$this->logger = $logger;
	}

	/**
	 * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
	 * @since 9.1
	 */
	public function getForm() {	

		$enable_openotp = $this->config->getUserValue( $this->userSession->getUser()->getUID(), 'twofactor_rcdevsopenotp', 'enable_openotp');
		$parameters['enable_openotp'] = $enable_openotp;

		return new TemplateResponse('twofactor_rcdevsopenotp', 'settings-personal', $parameters);
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 * @since 9.1
	 */
	public function getSection() {
		return 'security';
	}

	/**
	 * @return int (position)
	 * @since 9.1
	 */
	public function getPriority() {
		return 100;
	}
}
