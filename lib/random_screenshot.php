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

require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/medias.php');
require_once ('lib/MyDB.php');


function random_screenshot_print ()
{
	$type = MediaType::SCREENSHOT;
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->random_row ('`id`', '`type`=\''.$type.'\'');
	$media = $db->fetch_response ();
	if ($media)
	{
		media_unescape_db_array ($media);
		
		echo '
		<div class="media center">
			<a href="',UrlTable::medias ($media['id'], true),'">
				<img src="',MEDIA_DIR_R,'/',$media['tb_uri'],'" alt="',$media['desc'],'"
					style="max-width: 100%;" />
			</a>
		</div>';
	}
	else
	{
		echo '
		<p>
			Aucun média à afficher&nbsp;!
		</p>';
	}
}
