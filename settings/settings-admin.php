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
use OCA\TwoFactor_RCDevsOpenOTP\Settings\OpenotpConfig;

\OC_Util::checkAdminUser();
$_openotp_configs = OpenotpConfig::$_openotp_configs;
$_openotp_admintmpl = new OCP\Template('twofactor_rcdevsopenotp', 'settings-admin');
$_openotp_admintmpl->assign('openotp_allconfig', $_openotp_configs);

// Deprecated: before ajax call
/*if($_POST) {
	// CSRF check
	OCP\JSON::callCheck();
}*/

foreach( $_openotp_configs as $_openotp_confname => $_openotp_config ){
    if ($_POST && isset($_POST[$_openotp_config['name']]) ) {        
        OCP\Config::setAppValue('twofactor_rcdevsopenotp',$_openotp_config['name'],$_POST[$_openotp_config['name']]);
    }
    $_openotp_admintmpl->assign(
        $_openotp_config['name'],
        OCP\Config::getAppValue(
            'twofactor_rcdevsopenotp',$_openotp_config['name'],$_openotp_config['default_value']
        )
    );	
}

return $_openotp_admintmpl->fetchPage();