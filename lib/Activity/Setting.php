<?php

declare(strict_types=1);

/**
 *
 * @copyright Copyright (c) 2024, RCDevs (info@rcdevs.com)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace OCA\OpenOTPAuth\Activity;

use OCA\OpenOTPAuth\AppInfo\Application as OpenOTPAuthApp;
use OCP\Activity\ISetting;
use OCP\IL10N;

class Setting implements ISetting {

	/** @var IL10N */
	private $l10n;

	/**
	 * @param IL10N $l10n
	 */
	public function __construct(IL10N $l10n) {
		$this->l10n = $l10n;
	}

	/**
	 * @return boolean
	 */
	public function canChangeMail() {
		return false;
	}

	/**
	 * @return boolean
	 */
	public function canChangeStream() {
		return false;
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return OpenOTPAuthApp::APP_ID;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->l10n->t('OpenOTP (Authenticator app)');
	}

	/**
	 * @return int
	 */
	public function getPriority() {
		return 30;
	}

	/**
	 * @return boolean
	 */
	public function isDefaultEnabledMail() {
		return true;
	}

	/**
	 * @return boolean
	 */
	public function isDefaultEnabledStream() {
		return true;
	}
}
