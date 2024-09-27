/**
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
 */

const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

delete webpackConfig.entry['main']
webpackConfig.entry['challenge']		= path.join(__dirname, 'src', 'main-challenge.js')
webpackConfig.entry['settings']			= path.join(__dirname, 'src', 'main-settings.js')
webpackConfig.entry['login-setup']		= path.join(__dirname, 'src', 'main-login-setup.js')
webpackConfig.entry['admin-settings']	= path.join(__dirname, 'src', 'admin-settings.js');

module.exports = webpackConfig
