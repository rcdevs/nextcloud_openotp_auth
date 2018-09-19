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

namespace OCA\TwoFactor_RCDevsOpenOTP\Activity;

use OCP\Activity\ISetting;
use OCP\IL10N;

class Setting implements ISetting {

	/** @var IL10N */
	private $l10n;

	public function __construct(IL10N $l10n) {
		$this->l10n = $l10n;
	}

	public function canChangeMail() {
		return false;
	}

	public function canChangeStream() {
		return false;
	}

	public function getIdentifier() {
		return 'twofactor_rcdevsopenotp';
	}

	public function getName() {
		return $this->l10n->t('RCDevs OpenOTP (Authenticator app)');
	}

	public function getPriority() {
		return 10;
	}

	public function isDefaultEnabledMail() {
		return true;
	}

	public function isDefaultEnabledStream() {
		return true;
	}

}
