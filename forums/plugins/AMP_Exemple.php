<?php
/***********************************************************************

  Copyright (C) 2002-2005  Rickard Andersson (rickard@punbb.org)

  This file is part of PunBB.

  PunBB is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  PunBB is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/

##
##
##  Voici quelques notes intérrêssantes pour les aspirants auteurs de plugin :
##
##  1. Si vous voulez afficher un message par l’intermédiaire de la fonction 
##     message(), vous devez le faire avant d’appeler generate_admin_menu($plugin).
##
##  2. Les plugins sont chargés par admin_loader.php et ne doivent pas être terminés 
##     (par exemple en appelant exit()). Après que le script du plugin ait fini, le 
##     script du chargeur affiche le pied de page, ainsi inutil de vous souciez de cela. 
##     Cependant veuillez noter que terminer un plugin en appelant message() ou 
##     redirect() est très bien.
##
##  3. L’attribut action de toute balise <forme> et l’URL cible pour la fonction 
##     redirect() doit être placé à la valeur de $_SERVER[’REQUEST_URI’]. Cette 
##     URL peut cependant être étendue pour inclure des variables supplémentaires 
##     (comme l’ajout de &foo=bar dans le plugin exemple).
##
##  4. Si votre plugin est pour les administrateurs seulement, le nom de fichier 
##     doit avoir le préfixe AP_. S’il est pour les administrateurs et les modérateurs, 
##     utilisez le préfixe AMP_. Le plugin exemple a le préfixe AMP_ et est donc 
##     disponible dans le menu de navigation aux administrateurs et aux modérateurs.
##
##  5. Utilisez _ au lieu des espaces dans le nom de fichier.
##
##  6. Tant que les scripts de plugin sont inclus depuis le scripts admin_loader.php 
##     de PunBB, vous avez accès toutes les fonctions et variables globales de PunBB 
##     (par exemple $db, $pun_config, $pun_user etc.).
##
##  7. Faites de votre mieux pour garder l’aspect et l’ergonomie de votre interface 
##     utilisateur de plugins semblable au reste des scripts d’administration. 
##     N’hésitez pas à emprunter le marquage et le code aux scripts d’admin pour 
##     l’employer dans vos plugins.
##
##  8. Les plugins doivent êtres délivrés sous la licence d’utilisation GNU/GPL ou 
##     une licence compatible. Recopiez le préambule GPL (situé en haut des scripts 
##     de PunBB) dans votre script de plugin et changez l e copyright pour qu’il 
##     corresponde à l’auteur du plugin (c’est à dire vous).
##
##


// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);

//
// The rest is up to you!
//

// If the "Show text" button was clicked
if (isset($_POST['show_text']))
{
	// Make sure something something was entered
	if (trim($_POST['text_to_show']) == '')
		message('You didn\'t enter anything!');

	// Display the admin navigation menu
	generate_admin_menu($plugin);

?>
	<div class="block">
		<h2><span>Plugin exemple</span></h2>
		<div class="box">
			<div class="inbox">
				<p>Vous avez dit "<?php echo pun_htmlspecialchars($_POST['text_to_show']) ?>". Bon boulot.</p>
				<p><a href="javascript: history.go(-1)">Retour</a></p>
			</div>
		</div>
	</div>
<?php

}
else	// If not, we show the "Show text" form
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

?>
	<div id="exampleplugin" class="blockform">
		<h2><span>Plugin exemple</span></h2>
		<div class="box">
			<div class="inbox">
				<p>Ce plugin ne fait rien de bien utile. D'où le nom &quot;Exemple&quot;.</p>
				<p>Ce serait un bon endroit pour parler au sujet de votre plugin. Décrivez ce qu'il fait et comment il devrait être utilisé. Soyez bref, mais instructif.</p>
			</div>
		</div>

		<h2 class="block2"><span>Un formulaire d'exemple</span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;foo=bar">
				<div class="inform">
					<fieldset>
						<legend>Saisissez un bout de texte et cliquez "Afficher"&nbsp;!</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Texte à afficher<div><input type="submit" name="show_text" value=" Afficher le texte " tabindex="2" /></div></th>
								<td>
									<input type="text" name="text_to_show" size="25" tabindex="1" />
									<span>Le texte que vous voulez afficher.</span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
<?php

}

// Note that the script just ends here. The footer will be included by admin_loader.php.
