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

/*connecxion/déconnexion*/

require_once ('lib/User.php');
require_once ('lib/UrlTable.php');
require_once ('lib/string.php');
require_once ('lib/TypedDialog.php');

// adresse de redirection par défaut, utilisée si aucune autre n'est trouvée
$refresh = UrlTable::home ();

function is_local_uri ($uri)
{
	/* skip scheme */
	$server = strstr ($uri, "://");
	if ($server === false) {
		/* not a valid URI */
		return false;
	} else {
		$server = substr ($server, 3);
		return str_has_prefix ($uri, $_SERVER['SERVER_NAME']);
	}
}

if (isset ($_POST['redirect']) && is_local_uri ($_POST['redirect'])) {
	$refresh = $_POST['redirect'];
}
// on vérifie que le client a donné une adresse de page précédante
else if (isset ($_SERVER['HTTP_REFERER'])) {
	// on vérifie que la page pérécdante correspond à une page du site
	if (is_local_uri ($_SERVER['HTTP_REFERER'])) {
		$refresh = '';
		
		// si l'utilisateur viens d'une page d'admin, il serait redirigé vers un 404
		if (preg_match ('#/admin\.(?:php|html)#', $_SERVER['HTTP_REFERER'])) {
			$refresh = UrlTable::home ();
		}
		// sinon, on le renvoi d'où il vient (sans les args GET)
		// heu, pourquoi sans les GET ?
		else {
			$end = strpos ($_SERVER['HTTP_REFERER'], '?');
			if ($end === false)
				$refresh = $_SERVER['HTTP_REFERER'];
			else
				$refresh = substr ($_SERVER['HTTP_REFERER'], 0, $end);
		}
	}
}


$dialog = new TypedDialog (DIALOG_TYPE_INFO, $refresh);



// logout
if (isset ($_GET['act']) && $_GET['act'] == 'logout') {
	if (User::logout ()) {
		$dialog->add_info_message ('Log out successful');
	}
	else {
		$dialog->add_error_message ('Error while logging out.');
	}
} else if (User::get_logged ()) {
	$dialog->add_info_message ('You are already connected.');
}
// login
else {
	$show_form = true;
	
	if (isset ($_POST['username'], $_POST['password'])) {
		// 2592000 : 60 * 60 * 24 * 30 = 1 month of login
		// 0 : session time
		if (! User::login (isset ($_POST['remember']) ? time () + 2592000 : 0)) {
			$dialog->add_error_message ('Invalid username or password.');
		}
		else {
			$dialog->add_info_message ('Log in successful');
			$show_form = false;
		}
	}
	
	if ($show_form) {
		$form = '
			<h2>Log in</h2>
			<form method="post" action="' . UrlTable::login () . '" class="login">
				<p>
					<label>Username: <input type="text" name="username" /></label><br />
					<label>Password: <input type="password" name="password" /></label><br />
					<label><input type="checkbox" name="remember" />Remember me</label><br />
					<input type="submit" value="Log in" />
					<input type="hidden" name="redirect" value="'.$refresh.'" />
				</p>
			</form>
		';
		
		$dialog->set_title ('Log in');
		$dialog->set_redirect (false);
		$dialog->add_custom_data ($form);
	}
}


$dialog->flush ();
