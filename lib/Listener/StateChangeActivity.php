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

namespace OCA\TwoFactor_RCDevsOpenOTP\Listener;

use OCA\TwoFactor_RCDevsOpenOTP\Event\StateChanged;
use OCP\Activity\IManager as ActivityManager;
use Symfony\Component\EventDispatcher\Event;

class StateChangeActivity implements IListener {

	/** @var ActivityManager */
	private $activityManager;

	public function __construct(ActivityManager $activityManager) {
		$this->activityManager = $activityManager;
	}

	public function handle(Event $event) {
		if ($event instanceof StateChanged) {
			$user = $event->getUser();
			$subject = $event->isEnabled() ? 'openotp_enabled_subject' : 'openotp_disabled_subject';

			$activity = $this->activityManager->generateEvent();
			$activity->setApp('openotp_auth')
				->setType('security')
				->setAuthor($user->getUID())
				->setAffectedUser($user->getUID());
			$activity->setSubject($subject);
			$this->activityManager->publish($activity);
		}
	}
}