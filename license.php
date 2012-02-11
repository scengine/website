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


define ('TITLE', 'Licence');
require_once ('lib/string.php');
require_once ('include/top.minc');

//define ('LICENSE_FILE', 'http://www.gnu.org/licenses/gpl.txt');
define ('LICENSE_FILE', 'COPYING');

function print_gpl () {
	$gpl = @file_get_contents (LICENSE_FILE);
	if ($gpl)
		echo nls2p (htmlspecialchars ($gpl));
	else
	{
		echo '
		Le <a href="http://www.gnu.org/">site du projet
		<acronym title="GNU is Not Unix">GNU</acronym></a> est actuellement en
		dérangement. Nous vous prions de bien vouloir réessayer ultérieurement ou de
		consulter la licence GPL
		<a href="http://www.fsf.org/licensing/licenses/gpl.html">sur le site de la
		<abbr title="Free Software Foundation">FSF</abbr></a>.';
	}
}

?>
			<div id="presentation">
				<h2>Licence du SCEngine</h2>
				<p>
					Voici la licence GPL sous laquelle est distribué ce moteur de rendu 3D.
				</p>
				<p>
					La licence GPL est ci-dessous telle que disponible sur <a href="http://www.gnu.org/licenses/licenses.html#GPL">le site de <acronym title="GNU is Not Unix">GNU</acronym></a>.
				</p>
			</div>

			<div id="content">
				<h2>Licence GNU <abbr title="General Public License">GPL</abbr></h2>
				<p>
					<?php print_gpl (); ?>
				</p>
			</div>

<?php

require_once ('include/bottom.minc');
