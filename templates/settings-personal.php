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

script('openotp_auth', 'script');
$ocConfig = \OC::$server->getConfig();
$allow_user_administer_openotp = $ocConfig->getAppValue('openotp_auth','rcdevsopenotp_allow_user_administer_openotp');
?>

<div id="openotp_personnal_settings" class="section">
	<form id="openotp_psettings" method="POST" action="">
		<h2><?php p($l->t('OpenOTP Two-Factor Authentication'));?></h2>
		<p>
			<label style="padding-right:10px;" for="enable_openotp"><?php p($l->t("Enable OpenOTP Two-Factor Authentication."));?></label>	
			<input id="enable_openotp_yes" name="enable_openotp" type="radio" value="yes" <?php if ( $_['enable_openotp'] === "yes"  ): ?> checked="checked"<?php endif; ?> <?php if ( $allow_user_administer_openotp !== "on" ): ?> disabled="disabled"<?php endif; ?> /> <label for="enable_openotp_yes"> <?php p($l->t("Yes"));?></label>
			<input id="enable_openotp_no" name="enable_openotp" type="radio" value="no" <?php if ($_['enable_openotp'] === "no" || !isset($_['enable_openotp'])): ?> checked="checked"<?php endif; ?>  <?php if ( $allow_user_administer_openotp !== "on" ): ?> disabled="disabled"<?php endif; ?>/> <label for="enable_openotp_no"> <?php p($l->t("No"));?></label>
		</p>		
		<?php if ( $allow_user_administer_openotp !== "off" ): ?> <input type="hidden" name="openotp_psettings_sent" value="1" /><?php endif; ?> 
	</form>
	<div id="message"></div>
</div>