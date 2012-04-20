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

//define ('LICENSE_FILE', 'http://www.gnu.org/licenses/gpl.txt');
define ('LICENSE_FILE', 'COPYING');

require_once ('lib/string.php');
require_once ('lib/Html.php');
require_once ('lib/Controller.php');


class LicenseController extends Controller
{
	public function index ()
	{
		$license = @file_get_contents (LICENSE_FILE);
		if ($license) {
			$license = nls2p (Html::escape ($license));
		} else {
			$license = '
			The <a href="http://www.gnu.org/">GNU website</a> is currently down.
			Please try later or check the license on the
			<a href="http://www.fsf.org/licensing/licenses/gpl.html">
			Free Software Foundation website</a>.';
		}
		
		return array ('license' => $license);
	}
}
