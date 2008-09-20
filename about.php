<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2008 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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


define (TITLE, 'À propos');
require_once ('include/top.minc');
require_once ('include/Metadata.php'); /* gave MDI instance */

?>
	<div id="presentation">
		<h2>À propos du SCEngine</h2>
		<p>
			Informations sur le moteur ; auteur(s), description du moteur...
		</p>
	</div>

	<div id="content">
		<dl>
			<dt>Version actuelle&nbsp;:</dt>
			<dd><a href="downloads.php"><?php echo $MDI->get_version (); ?></a></dd>
		</dl>
		
		<h3>Auteurs</h3>
		<?php 
			$items = array (
				array ('Développeurs',  get_authors),
				array ('Documentation', get_documenters),
				array ('Traducteurs',   get_translators),
				array ('Graphistes',    get_graphists),
				array ('Contributeurs', get_contributors),
			);
			
			foreach ($items as &$item) {
				$a = &$MDI->$item[1] ();
				if ($a) {
					echo '<h4>',$item[0],'</h4><ul>';
					foreach ($a as &$i)
						echo '<li>',$i,'</li>';
					echo '</ul>';
				}
			}
		?>
	</div>

<?php

require_once ('include/bottom.minc');

?>
