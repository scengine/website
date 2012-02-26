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


define ('TITLE', 'About');
require_once ('include/top.minc');
require_once ('lib/Metadata.php'); /* gave MDI instance */

?>
	<div id="presentation">
		<h2>About the SCEngine</h2>
		<p>
    Authors, miscellaneous information.
		</p>
	</div>

	<div id="content">
		<dl>
                    <dt>Current version:</dt>
			<dd><a href="downloads.php"><?php echo $MDI->get_version (); ?></a></dd>
		</dl>
		
		<h3>Authors</h3>
		<?php 
			$items = array (
				array ('Engine development',  'get_authors'),
				array ('Documentation', 'get_documenters'),
				array ('Translation',   'get_translators'),
				array ('Graphists',    'get_graphists'),
				array ('Contributors', 'get_contributors'),
			);
			
			foreach ($items as &$item) {
				$a = $MDI->$item[1] ();
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
