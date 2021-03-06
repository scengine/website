<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2012 Colomban Wendling <ban@herbesfolles.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/* Display messages */

abstract class Msg {
	public static function error ($msg) {
		echo '<div class="message">
			<h2>Erreur</h2>
			<p>
				', $msg, '
			</p>
			</div>';
	}

	public static function info ($msg) {
		echo '<div class="message">
			<h2>Information</h2>
			<p>
				', $msg, '
			</p>
			</div>';
	}
}
