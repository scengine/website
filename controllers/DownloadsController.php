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

require_once ('lib/LayoutController.php');
require_once ('lib/User.php');
require_once ('lib/medias.php');
require_once ('lib/misc.php');
require_once ('lib/Html.php');


class DownloadsModel
{
	public function find ()
	{
		$medias = media_get_medias (
			array (MediaType::RELEASE),
			array (),
			array (
				'mdate' => 'DESC',
				'desc'  => 'ASC'
			)
		);
		/* Adjust medias */
		foreach ($medias as &$media) {
			if ($media['desc'] == '') {
				$media['desc'] = 'No description';
			}
			$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
			
			/* FIXME: should the date & size formatting be done here rather than in
			 * the template? */
		}
		
		return $medias;
	}
}

class DownloadsController extends LayoutController
{
	public function __construct ()
	{
		$this->Downloads = new DownloadsModel ();
	}
	
	public function index ()
	{
		return array (
			'is_admin'	=> User::has_rights (ADMIN_LEVEL_MEDIA),
			'medias'		=> $this->Downloads->find ()
		);
	}
}
