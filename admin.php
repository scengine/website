<?php;
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

/* admin :: accueil */

session_start();

require_once ('include/User.php');
require_once ('include/Header.php');
require_once ('include/defines.php');
require_once ('include/Metadata.php'); /* gave MDI instance */

// si l'utilisateur n'est pas loggué, on l'envois chier :D
if (!User::get_logged () && User::get_level () <= 3) {
	Header::h404 ();
}



//define (TITLE, 'Administration - '.ENGINE);
define (STYLE, 'default');
define (DESCRIPTION, 'Administration du '.$MDI->get_name ());


$name = urldecode($_GET['page']);

// on vérifie que la page existe et que l'admin est loggué :
if (file_exists( 'include/'.$name.'.inc' ) && User::get_logged ()) {
	$page = 'include/'.$name.'.inc';

	define (TITLE, ucfirst($name).' - Administration');
	define (PAGE, $name);
}
else {
	$page = 'include/accueil.inc';

	define (TITLE, 'Accueil - Administration');
	define (PAGE, 'accueil');
}

include('include/top.minc');
include($page);
include('include/bottom.minc');

?>
