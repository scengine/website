<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2009 Colomban "Ban" Wendling <ban@herbesfolles.org>
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
?>
			<div id="presentation">
				<h2>Administration</h2>
			</div>
			
			<div id="content">
			<p>
				Bienvenue sur la partie d'administration du site du SCEngine&nbsp;!
				<a href="index.php">Retour au site</a>
			</p>
<?php
	require_once('lib/User.php');

	if(User::get_logged ())
	{
?>
		<p>
			<a href="connexion.php?act=logout"
				onclick="window.location.replace(this.href)">
				<input type="button" value="Déconnexion" />
			</a>
		</p>
		<h3>Pages</h3>
		<ul>
<?php
	$dir = opendir ('include'); // dossier contenant les pages
	$tpages = Array (); // tableau des pages
	
	// getting files from directory
	for ($i = 0; False !== ($file = readdir ($dir)); $i++) {
		// vreifying filename
		if (substr ($file, strlen ($file) -4) == '.inc')
			$tpages[$i] = substr ($file, 0, strlen ($file) -4);
	}
	
	closedir ($dir); // fermeture du répertoire
	
	sort ($tpages); // sorting array
	
	// dispaying result
	foreach ($tpages as $tpage) {
		$tname = ucfirst ($tpage);
		
		echo '<li><a href="?page=', $tpage, '">', $tname, '</a></li>';
	}
?>
		</ul>
<?php
	}
	else
	{
		if($_POST['username'] || $_POST['password'])
		{
?>
			<p>
				<span class="error">Erreur&nbsp;:</span> mot de passe ou login incorrect&nbsp;!
			</p>
<?php
		}
?>
			<form method="post" action="connexion.php?act=login">
				<div>
					<label>Nom d'utilisateur&nbsp;: <input type="text" name="username" /></label><br />
					<label>Mot de passe&nbsp;: <input type="password" name="password" /></label><br />
					<input type="submit" value="Valider" />
				</div>
			</form>
<?php
	}
?>

		</div>
