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

require_once ('lib/Metadata.php'); /* gives MDI instance */
require_once ('lib/PHPTemplate.php');


class AboutTemplate extends PHPFileTemplate
{
	public function __construct ()
	{
		global $MDI;
		
		parent::__construct ('views/about.phtml');
		$this->version = $MDI->get_version ();
		$this->roles = array (
			'Engine development'	=> $MDI->get_authors (),
			'Documentation'				=> $MDI->get_documenters (),
			'Translation'					=> $MDI->get_translators (),
			'Graphists'						=> $MDI->get_graphists (),
			'Contributors'				=> $MDI->get_contributors ()
		);
	}
}


$tpl = new AboutTemplate ();

require_once ('include/top.minc');
$tpl->render ();
require_once ('include/bottom.minc');
