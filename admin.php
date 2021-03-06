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

/* admin :: accueil */

session_start ();

require_once ('include/defines.php');
require_once ('lib/Metadata.php');
require_once ('lib/User.php');
require_once ('lib/Header.php');
require_once ('lib/PHPTemplate.php');

class AdminPage
{
	private $path;
	private $title;
	
	public function __construct ($name)
	{
		$path = $this->get_path ($name);
		/* if page doesn't exists or user not logged, go home */
		if (! file_exists ($path) && User::get_logged ()) {
			$name = 'accueil';
			$path = $this->get_path ($name);
		}
		
		$this->title = ucfirst ($name).' - Administration';
		define ('PAGE', $name);
		$this->path = $path;
	}
	
	private function get_path ($name)
	{
		return './include/'.$name.'.inc';
	}
	
	public function get_title ()
	{
		return $this->title;
	}
	
	public function render ()
	{
		include ($this->path);
	}
}

// si l'utilisateur n'est pas loggué, on l'envois chier :D
if (! User::has_rights (ADMIN_LEVEL_MINIMAL)) {
	Header::h404 ();
}

$name = (isset ($_GET['page'])) ? urldecode ($_GET['page']) : '__dummy__';
$page = new AdminPage ($name);

$layout = new PHPFileTemplate (
	'views/layout.phtml',
	array (
		'controller' => 'admin.php',
		'template' => $page,
		'site_title' => Metadata::get_instance ()->get_name (),
		'page_title' => $page->get_title ()
	)
);
$layout->render ();
