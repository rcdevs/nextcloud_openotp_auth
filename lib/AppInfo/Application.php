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

namespace OCA\TwoFactor_RCDevsOpenOTP\AppInfo;

use OCA\TwoFactor_RCDevsOpenOTP\Event\StateChanged;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\IListener;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\StateChangeActivity;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactor_RCDevsOpenOTP\Controller\SettingsController;


class Application extends \OCP\AppFramework\App
{
    /**
     * @param array $urlParams
     */
    public function __construct(array $urlParams = array())
    {
        parent::__construct('twofactor_rcdevsopenotp', $urlParams);
        $container = $this->getContainer();
			

		//Declaration openotp classes
		\OC::$CLASSPATH['OCA\\TwoFactor_RCDevsOpenOTP\\Settings\\OpenotpConfig'] = 'twofactor_rcdevsopenotp/lib/Settings/openotp.config.php';
		\OC::$CLASSPATH['OCA\\TwoFactor_RCDevsOpenOTP\\AuthService\\OpenotpAuth'] = 'twofactor_rcdevsopenotp/lib/Provider/openotp.class.php';
		
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
				$c->query('L10N'),
                $server->getConfig(),
				$server->getLogger(),
				$server->getAppManager(),
				$server->getUserManager(),
				$server->getEventDispatcher()
            );
        });
		
        $container->registerService('L10N', function($c) {
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });
		
		$dispatcher = $container->getServer()->getEventDispatcher();
		$dispatcher->addListener(StateChanged::class, function (StateChanged $event) use ($container) {
			/** @var IListener[] $listeners */
			$listeners = [
				$container->query(StateChangeActivity::class),
				$container->query(StateChangeRegistryUpdater::class),
			];

			foreach ($listeners as $listener) {
				$listener->handle($event);
			}
		});		
		
	}
	
    /**
     * register setting scripts
     */
	
    public function registerSettings()
    {
		//TODO: line   94: OCP\App::registerPersonal - Method of deprecated class must not be called
        \OCP\App::registerPersonal('twofactor_rcdevsopenotp',
            'lib/Settings/settings-personal');
    }	
		
}
