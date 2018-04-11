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

namespace OCA\TwoFactor_RCDevsOpenOTP\AppInfo;

use OCA\TwoFactor_RCDevsOpenOTP\Controller\SettingsController;
use OCP\App;


class Application extends \OCP\AppFramework\App
{
    /**
     * @param array $urlParams
     */
    public function __construct(array $urlParams = array())
    {
        parent::__construct('twofactor_rcdevsopenotp', $urlParams);
        $container = $this->getContainer();
		
        /**
         * Controllers
         */
        $container->registerService('UserSession', function($c) {
            return $c->query('ServerContainer')->getUserSession();
        });
			   
	    $container->registerService('SettingsController', function ($c) {
            $server = $c->getServer();
            return new SettingsController(
                $c->getAppName(),
				$c->query('UserSession')->getUser(),
                $server->getRequest(),
                $server->getL10N($c->getAppName()),
                $server->getConfig(),
				$server->getLogger()
            );
        });
    }

    /**
     * register setting scripts
     */
    public function registerSettings()
    {
        App::registerAdmin('twofactor_rcdevsopenotp',
            'settings/settings-admin');

        App::registerPersonal('twofactor_rcdevsopenotp',
            'settings/settings-personnal');
    }


}
