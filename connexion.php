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

/*connecxion/déconnexion*/

require_once ('include/User.php');
require_once ('include/TypedDialog.class.php');

// adresse de redirection par défaut, utilisée si aucune autre n'est trouvée
$refresh = 'index.php';

// on vérifie que le client a donné une adresse de page précédante
if ($_SERVER['HTTP_REFERER']) {
	// on vérifie que la page pérécdante correspond à une page du site
	if (preg_match ('#^http://'.$_SERVER['SERVER_NAME'].'#U', $_SERVER['HTTP_REFERER'])) {
		$refresh = '';
		
		// si l'utilisateur viens d'une page d'admin, il serait redirigé vers un 404
		if (preg_match ('#/admin\.php#', $_SERVER['HTTP_REFERER'])) {
			$refresh = 'index.php';
		}
		// sinon, on le renvoi d'où il vient (sans les args GET)
		else {
			for ($i = 0; $_SERVER['HTTP_REFERER'][$i] && 
			             $_SERVER['HTTP_REFERER'][$i] != '?'; $i++)
				$refresh .= $_SERVER['HTTP_REFERER'][$i];
		}
	}
}


$dialog = &new TypedDialog (DIALOG_TYPE_INFO, $refresh);



// logout
if ($_GET['act'] == 'logout') {
	if (User::logout ()) {
		$dialog->add_info_message ('Déconnexion réussie');
	}
	else {
		$dialog->add_error_message ('Erreur lors de la désconnexion.');
	}
}
// login
else {
	// 2592000 : 60 * 60 * 24 * 30 = 1 month of login
	// 0 : session time
	if (! User::login (($_POST['remember']) ? time () + 2592000 : 0)) {
		/* tring to find what's the error */
		if($_POST['username'] || $_POST['password'])
			$dialog->add_error_message ('Mot de passe ou login faux.');
		else
			$dialog->add_error_message ('Pas de login ou mot de passe.');
	}
	else
		$dialog->add_info_message ('Login réussi');
}


$dialog->flush ();
