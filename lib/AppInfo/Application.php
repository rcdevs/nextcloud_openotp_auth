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

namespace OCA\TwoFactor_RCDevsOpenOTP\AppInfo;

use OCA\TwoFactor_RCDevsOpenOTP\Event\StateChanged;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\IListener;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\StateChangeActivity;
use OCA\TwoFactor_RCDevsOpenOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactor_RCDevsOpenOTP\Controller\SettingsController;
use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use \OCP\AppFramework\Http\TemplateResponse;
use OCP\Util;

require_once(__DIR__ . '/../../vendor/autoload.php');



class Application extends App
{
    public const APP_ID = 'openotp_auth';

    /**
     * @param array $urlParams
     */
    public function __construct(array $urlParams = array())
    {
        parent::__construct(self::APP_ID, $urlParams);

        if (class_exists('\\OCP\\AppFramework\\Http\\EmptyContentSecurityPolicy')) {
            $manager = \OC::$server->getContentSecurityPolicyManager();
            $policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
            $policy->addAllowedScriptDomain('\'unsafe-inline\'');
            $manager->addDefaultPolicy($policy);
        }

        $container = $this->getContainer();
        $eventDispatcher = $container->get(IEventDispatcher::class);
        $eventDispatcher->addListener(TemplateResponse::EVENT_LOAD_ADDITIONAL_SCRIPTS, function() {
            Util::addStyle(self::APP_ID, 'settings');
            Util::addScript(self::APP_ID, 'script');
            Util::addScript(self::APP_ID, 'fidou2f');
            Util::addScript(self::APP_ID, 'base64');
            Util::addScript(self::APP_ID, 'voice');
        });

        $eventDispatcher->addListener(StateChanged::class, function (StateChanged $event) use ($container) {
            /** @var IListener[] $listeners */
            $listeners = [
                $container->query(StateChangeActivity::class),
                $container->query(StateChangeRegistryUpdater::class),
            ];

            foreach ($listeners as $listener) {
                $listener->handle($event);
            }
        });
		
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
	}
}
