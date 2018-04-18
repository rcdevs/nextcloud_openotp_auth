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

use OCA\TwoFactor_RCDevsOpenOTP\AppInfo\Application;
$app = new Application();
// Register the personal configuration settings 
$app->registerSettings();

if(class_exists('\\OCP\\AppFramework\\Http\\EmptyContentSecurityPolicy')) { 
	$manager = \OC::$server->getContentSecurityPolicyManager();
	$policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
    $policy->addAllowedScriptDomain('\'unsafe-inline\'');
	$manager->addDefaultPolicy($policy);
}

\OCP\Util::addStyle('twofactor_rcdevsopenotp', 'settings');
\OCP\Util::addScript('twofactor_rcdevsopenotp', 'script');
\OCP\Util::addScript('twofactor_rcdevsopenotp', 'fidou2f');

//TODO: OC_User - Static method of private class must not be called
/*$isadmin = \OC_User::isAdminUser(\OC_User::getUser());
if($isadmin){
	\OC::$server->getNavigationManager()->add(function () {
	    $urlGenerator = \OC::$server->getURLGenerator();
	    return [
	        'id' => 'twofactor_rcdevsopenotp',
	        'order' => 100,
	        'href' => $urlGenerator->linkToRoute('twofactor_rcdevsopenotp.settings.index'),
	        'icon' => $urlGenerator->imagePath('twofactor_rcdevsopenotp', 'app.svg'),
			'name' => "RCDevs OpenOTP"
	    ];
	});
}*/

