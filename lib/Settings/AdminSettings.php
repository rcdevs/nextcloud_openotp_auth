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
namespace OCA\TwoFactor_RCDevsOpenOTP\Settings;

use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;
use OCA\TwoFactor_RCDevsOpenOTP\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\ILogger;

class AdminSettings implements ISettings {

        /** @var IConfig */
        private $config;

        /** @var IL10N */
        private $l;
		
		/** OpenOTP Config */
	    private $openotpconfig;		
		
		/** $obj ILogger $logger */	
	    private $logger;		

        /**
         * Admin constructor.
         *
         * @param IConfig $config
         * @param IL10N $l
         * @param Ilogger $logger
         */
        public function __construct(IConfig $config, IL10N $l, ILogger $logger){
			$this->config = $config;
			$this->l = $l;
			$this->openotpconfig = OpenotpConfig::$_openotp_configs;
			$this->logger = $logger;
        }

        /**
         * @return TemplateResponse
         */
        public function getForm() {
				$parameters['openotp_allconfig'] = $this->openotpconfig;
				
				foreach( $this->openotpconfig as $_openotp_confname => $_openotp_config ){
					$parameters[$_openotp_config['name']] = $this->config->getAppValue('twofactor_rcdevsopenotp', $_openotp_config['name'], $_openotp_config['default_value']);						
				}				

                return new TemplateResponse('twofactor_rcdevsopenotp', 'settings-admin', $parameters);
        }

        /**
         * @return string the section ID
         */
        public function getSection() {
                return 'twofactor_rcdevsopenotp';
        }

        /**
         * @return int (position)
         */
        public function getPriority() {
                return 100;
        }

}

