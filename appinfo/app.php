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

use OCA\TwoFactor_RCDevsOpenOTP\AppInfo\Application;

$app = new Application();
// Register the configuration settings templates
$app->registerSettings();
\OC::$CLASSPATH['OCA\\TwoFactor_RCDevsOpenOTP\\Settings\\OpenotpConfig'] = 'twofactor_rcdevsopenotp/settings/openotp.config.php';
\OC::$CLASSPATH['OCA\\TwoFactor_RCDevsOpenOTP\\AuthService\\OpenotpAuth'] = 'twofactor_rcdevsopenotp/lib/Provider/openotp.class.php';

\OCP\Util::addStyle('twofactor_rcdevsopenotp', 'settings');
\OCP\Util::addScript('twofactor_rcdevsopenotp', 'context');
\OCP\Util::addScript('twofactor_rcdevsopenotp', 'fidou2f');

$isadmin = \OC_User::isAdminUser(\OC_User::getUser());
if($isadmin){
	\OCP\App::addNavigationEntry([
		'id' => 'twofactor_rcdevsopenotp',
		'order' => 100,
		'href' => \OCP\Util::linkToRoute('twofactor_rcdevsopenotp.settings.index'),
		'icon' => \OCP\Util::imagePath('twofactor_rcdevsopenotp', 'app.svg'),
		'name' => 'Rcdevs Openotp'
	]);
}

