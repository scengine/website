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

/* page d'administration des admins */

require_once ('include/defines.php');
require_once ('lib/misc.php');
require_once ('lib/UrlTable.php');
require_once ('lib/User.php');
require_once ('lib/MyDB.php');



function display_table()
{
	global $master;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table(USERS_TABLE);
	$db->select('*', '');

	echo '<table>
				<caption>Liste des administrateurs</caption>
				<tr>
					<th>Pseudonyme</th>
					<th>Niveau</th>
					<th>Loggué ?</th>
					<th>Éditer</th>
					<th></th>
				</tr>';
	
	while (false !== ($response = $db->fetch_response ()))
	{
		echo '<tr>
			<td>
				<a href="',UrlTable::admin_admins ('show', $response['username']),'"
				   title="Voir la fiche de ',$response['username'],'">',$response['username'],'</a>
			</td>
			<td>',$response['level'],'</td>
			<td>',($response['logged']) ? 'Oui' : 'Non','</td>
			<td>';
		
		if ($master || User::get_name() == $response['username'])
		{
			echo '
			<a href="',UrlTable::admin_admins ('edit', $response['username']),'"
			   title="Éditer la fiche de ',$response['username'],'">
				<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
			</a>';
		}
		else
			echo ' - ';
		
		echo '
		</td>
		<td>';
		
		if ($master)
		{
			echo '
			<a href="',UrlTable::admin_admins ('rm', $response['username']),'"
			   title="Supprimer ',$response['username'],'">
				<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
			</a>';
		}
		else
			echo ' - ';
		
		echo '
		</td>
		</tr>';
	}
	
	echo '</table>';
	
	if ($master)
	{
		echo '
		<p>
			',print_button ('Ajouter un administrateur', UrlTable::admin_admins ('add')),'
		</p>';
	}
	
	unset ($db);
}

function display_user($pseudo)
{
	global $master;
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table(USERS_TABLE);
	$db->select('*', "`username`='$pseudo'");
	
	$response = $db->fetch_response ();
	
	echo '
	<h3>Fiche de ',$response['username'],'</h3>
	<table>
	<tr>
		<th>Pseudonyme</th>
		<th>Niveau</th>
		<th>Loggué ?</th>
		<th>Éditer</th>
		<th></th>
	</tr>
	<tr>
		<td>',$response['username'],'</td>
		<td>',$response['level'],'</td>
		<td>',($response['logged']) ? 'Oui' : 'Non','</td>
		<td>';
	if ($master || User::get_name() == $response['username'])
	{
		echo '
		<a href="',UrlTable::admin_admins ('edit', $response['username']),'"
		   title="Éditer la fiche de ',$response['username'],'">
			<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
		</a>';
	}
	else
		echo ' - ';
	
	echo '
	</td>
	<td>';
	
	if ($master)
	{
		echo '
		<a href="',UrlTable::admin_admins ('rm', $response['username']),'"
		   title="Supprimer ',$response['username'],'">
			<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
		</a>';
	}
	else
		echo ' - ';
	
	echo '
			</td>
		</tr>
	</table>
	<p>
		',print_button ('Retour', UrlTable::admin_admins (),
		                'Retour à la gestion des administrateurs'),'
	</p>';
	
	unset ($db);
}

function edit_user ($pseudo)
{
	global $master;

	if ($master || User::get_name () == $_GET['pseudo'])
	{
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (USERS_TABLE);
		$db->select ('*', "`username`='$pseudo'");
		
		$response = $db->fetch_response ();
		
		echo '
		<h3>Éditer la fiche de ',$response['username'],'</h3>
		<form method="post" action="',UrlTable::admin_admins ('save', $response['username']),'">
			<div>
				<input type="hidden" name="oldpseudo" value="',$response['username'],'" />
				<label>Pseudonyme&nbsp;: <input type="text" name="pseudo" value="',$response['username'],'" /></label>
				<br />
				<label>Nouveau mot de passe&nbsp;: <input type="password" name="newpasswd" /></label>
				<br />
				<label>Répéter le mot de passe&nbsp;: <input type="password" name="newpasswdconf" /></label>
				<br />
			';
		if ($master)
		{
			echo '
				<input type="hidden" name="oldlevel" value="',$response['level'],'" />
				<label>Niveau d\'administration&nbsp;: 
					<select name="level">
						<option value="0" ',($response['level'] == 0) ? 'selected="selected"' : '','>0</option>
						<option value="1" ',($response['level'] == 1) ? 'selected="selected"' : '','>1</option>
						<option value="2" ',($response['level'] == 2) ? 'selected="selected"' : '','>2</option>
						<option value="3" ',($response['level'] == 3) ? 'selected="selected"' : '','>3</option>
					</select>
				</label>
			';
		}
		echo '
			</div>
			<p>
				<input type="submit" value="Enregistrer" />
				<input type="reset" value="Réinitialiser"/>
				',print_button ('Retour', UrlTable::admin_admins (),
				                'Retour à la gestion des administrateurs'),'
			</p>
		</form>';

		unset ($db);
	}
	else
	{
		echo '<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
	}
}

function save_edit_user ($oldpseudo, $pseudo, 
                         $mdp, $mdp2, // ne sont de toutes façons là que si l'on en change
                         $oldlevel, $level)
{
	global $master;
	$return = true;
	
	// si l'ancien nom n'existe pas, on quitte pour éviter les erreurs
	if (! $oldpseudo) 
	{
		echo '<h3>Erreur</h3>
		<p>
			Une erreur interne s\'est produite, et l\'action a été arrêtée pour 
			éviter des erreurs. Veuillez contacter votre administrateur principal
			et lui comuniquer le message suivant&nbsp;:
		</p>
		<fieldset class="error">
			<legend>Erreur&nbsp;:</legend>
				failed to get <span class="phpvar">$oldpseudo</span> (value: 
				"<span class="phpval">',$oldpseudo,'</span>") in function 
				"<span class="phpfunc">',__FUNCTION__,'()</span>" in file "', 
				__FILE__,'"
		</fieldset>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	
	/* on vérifie que l'utilisateur a le droit de faire ce qu'il fait 
	 * (si c'est un superadmin ou qu'il modifie son compte */
	if (! $master && User::get_name() != $oldpseudo)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	
	// si tout est OK, on commence les choses sérieuses :
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (USERS_TABLE);
	
	echo '<h3>Données mises à jour</h3>';
	
	// pseudo
	if ($pseudo != $oldpseudo) // si le pseudo a été changé
	{
		if (is_string ($pseudo) && // si le pseudo est une chaîne de caractères
		    strlen ($pseudo) >= 3) // et qu'il fait au moins 3 caractères de long
		{
			if ($db->update("`username`='$pseudo'", "`username`='$oldpseudo'"))
				echo '<p>Login mis à jour avec succès !</p>';
			else
			{
				echo '
				<p>
					<span class="error">Erreur de modification du login&nbsp;:</span> il
					est probable que vous ayez choisi un login déjà utilisé par un autre
					administrateur.
				</p>';
			}
			/* si on modifie l'utilisateur connecté, on met aussi le login de la 
			 * session à jour pour ne pas être automatiquement déconnecté */
			if (User::get_name () == $oldpseudo)
				User::set_name ($pseudo);
		}
		else // si ça n'a pas marché
		{
			echo '
			<p>
				<span class="error">Erreur&nbsp;:</span> la mise à jour du pseudo à échouée&nbsp;: ';
			if (strlen ($pseudo) < 3)
				echo 'le nouveau pseudo est trop court (3 caractères minimum sont requis).';
			elseif (! is_string ($pseudo))
				echo 'le pseudo n\'a pas été reçu comme chaîne de caractère. <span class="error">Veuillez contacter votre administrateur principal.</span>';
			else
			{
				echo '
				<span class="error">(erreur inconnue)</span>
				<span class="error">Veuillez contacter votre administrateur principal.</span>';
			}
			echo '
			</p>';
		}
	}
	
	// mot de passe
	if (! empty($mdp))
	{
		if (is_string ($mdp) &&  // si le nouveau pass est bien une chaîne de caractères
		    strlen ($mdp) >= 6 && // et que le nouveau pass fait au moins 6 caractères de long
		    $mdp === $mdp2) // et que le nouveau pass est identique à la confirmation
		{
			$db->update("`password`='".md5($mdp)."'", "`username`='$oldpseudo'");
			/* si on modifie l'utilisateur connecté, on met aussi le mdp de la 
				session à jour pour ne pas être automatiquement déconnecté */
			if (User::get_name() == $oldpseudo)
				User::set_password ($mdp);
			
			echo '<p>Mot de passe mis à jour avec succès !</p>';
		}
		else // si ça a chié
		{
			echo '
			<p>
				<span class="error">Erreur&nbsp;:</span> la mise à jour du pseudo à échouée&nbsp;: ';
			
			if (strlen ($mdp) < 6)
				echo 'le nouveau mot de passe est trop court (6 caractères minimum sont requis).';
			elseif (! is_string ($mdp))
				echo 'le mot de passe n\'a pas été reçu comme chaîne de caractère. <span class="error">Veuillez contacter votre administrateur principal.</span>';
			elseif ($mdp !== $mdp2)
				echo 'le mot de passe ne correspond pas au mot de passe de confirmation.';
			else
			{
				echo '
				<span class="error">(erreur inconnue)</span>
				<span class="error">Veuillez contacter votre administrateur principal.</span>';
			}
			echo '
			</p>';
		}
	}
	
	// niveau d'admin
	if ($level != $oldlevel) // si le niveau a changé
	{
		if (is_int ($level) && // si le niveau est un entier
		    $level >= 0)
		{
			// il n'y a que les maîtres qui ont le droit de changer le niveau d'admin
			if($master)
			{
				$db->update("`level`='$level'", "`username`='$oldpseudo'");
				/* si on modifie l'utilisateur connecté, on met aussi le niveau de la 
					session à jour pour effectuer le modif tout de suite */
				if(User::get_name() == $oldpseudo)
					User::set_level($level);
			
				echo '<p>Niveau d\'administration mis à jour avec succès !</p>';
			}
			else // si ce n'est pas un maître
			{
				echo '<p>Vous n\'avez pas le niveau requis pour effectuer cette action.</p>';
				$return = false;
			}
		}
		else
		{
			echo '
			<p>
				<span class="error">Erreur&nbsp;:</span> la mise à jour du pseudo à échouée&nbsp;: ';
			
			if ($level < 0)
				echo 'le niveau n\'est pas valide (le niveau doit être supérieur ou égal à 0)';
			elseif (is_int ($level) != 'integer')
				echo 'le niveau n\'a pas été reçu comme entier. <span class="error">Veuillez contacter votre administrateur principal.</span>';
			else
			{
				echo '
				<span class="error">(erreur inconnue)</span>
				<span class="error">Veuillez contacter votre administrateur principal.</span>';
			}
			echo '
			</p>';
		}
	}
	
	unset ($db);
	
	echo '
	<p>
		',print_button ('Retour', UrlTable::admin_admins (),
		                'Retour à la gestion des administrateurs'),'
	</p>';
	
	return $return;
}

function remove_user_query ($pseudo)
{
	global $master;
	
	if (!$master)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	
	if (! $pseudo)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Aucun utilisateur sélectionné !
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	$pseudo = addslashes ($pseudo);
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (USERS_TABLE);
	$db->select ('*', "`username`='$pseudo'");
	$response = $db->fetch_response ();
	unset ($db);
	
	echo '
	<h3>Supprimer «&nbsp;',$response['username'],'&nbsp;»&nbsp;?</h3>
	<p>
		Êtes-vous sûr de vouloir supprimer l\'administrateur «&nbsp;',$response['username'],'&nbsp;»&nbsp;?
	</p>
	<table>
		<caption>Caractéristique de «&nbsp;',$response['username'],'&nbsp;»</caption>
		<tr>
			<th>Pseudonyme</th>
			<th>Niveau</th>
			<th>Loggué ?</th>
		</tr>
		<tr>
			<td>',$response['username'],'</td>
			<td>',$response['level'],'</td>
			<td>',($response['logged']) ? 'Oui' : 'Non','</td>
		</tr>
	</table>
	<form method="post" action="',UrlTable::admin_admins ('rm', $response['username']),'">
		<p>
			<input type="hidden" name="rmuser" value="yes" />
			<input type="submit" value="Supprimer" />
			',print_button ('Annuler', UrlTable::admin_admins ()),'
		</p>
	</form>';
	
	return true;
}

function remove_user ($pseudo)
{
	global $master;
	$return = true;
	
	if (! $pseudo)
		return false;
	
	if (! $master)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	$pseudo = addslashes ($pseudo);
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (USERS_TABLE);
	if(! $db->delete ("`username`='$pseudo'"))
		$return = false;
	unset ($db);
	
	if ($return) // s'il n'y a pas de problème
	{
		echo '
		<h3>Suppression réussie&nbsp;!</h3>
		<p>
			L\'administrateur ', $pseudo, ' a été supprimé avec succès.
		</p>
		<p>
			',print_button ('Retour', UrlTable::admin_admins (),
			                'Retour à la gestion des administrateurs'),'
		</p>';
	}
	else
	{
		echo '
		<h2>Échec de la suppression</h2>
		<p>
			La suppression de l\'administrateur ',$pseudo,' a échouée.<br />
			Veuillez reporter ce problème à votre administrateur principal.
		</p>
		<p>
			',print_button ('Retour', UrlTable::admin_admins (),
			                'Retour à la gestion des administrateurs'),'
		</p>';
	}
	
	return $return;
}

function add_user_query()
{
	global $master;
	
	if (! $master)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	
	echo '
	<h3>Ajouter un administrateur</h3>
	<form method="post" action="?',UrlTable::admin_admins ('add'),'">
		<div>
			<label>Pseudonyme&nbsp;: <input type="text" name="pseudo" /></label>
			<br />
			<label>Mot de passe&nbsp;: <input type="password" name="passwd" /></label>
			<br />
			<label>Répéter le mot de passe&nbsp;: <input type="password" name="passwdconf" /></label>
			<br />
			<label>Niveau d\'administration&nbsp;: 
				<select name="level">
					<option value="0">0</option>
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3" selected="selected">3</option>
				</select>
			</label>
			<br />
			<input type="submit" value="Enregistrer" /> <input type="reset" value="Réinitialiser"/>
		</div>
	</form>
	<p>
		',print_button ('Retour', UrlTable::admin_admins (),
		                'Retour à la gestion des administrateurs'),'
	</p>';
	
	return true;
}

function add_user ($pseudo, $pass, $passconf, $level)
{
	global $master;
	$return = true;
	$error = false;
	
	if (! $master)
	{
		echo '
		<h3>Erreur</h3>
		<p>
			Vous n\'avez pas la permission d\'effectuer cette action.
		</p>
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
		
		return false;
	}
	
	if (is_string ($pseudo) && strlen ($pseudo) >= 3 && 
	    is_string ($pass) && strlen ($pass) >= 6 && $pass === $passconf &&
	    is_int ($level) && $level >= 0)
	{
		/* cryptage du mot de passe */
		$pass = md5($pass);
		//$passconf = md5($passconf);
		
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (USERS_TABLE);
		$return = $db->insert("'$pseudo', '$pass', '$level', 'false'");
		$error = ! $return;
		unset ($db);
	}
	else
	{
		/* ... */
		$error = true;
	}

	if (! $error)
	{
		echo '
		<h3>Ajout de l\'administrateur ',$pseudo,'</h3>
		<p>
			L\'administrateur ',$pseudo,' a été ajouté avec succès&nbsp;!
		</p>
		<p>
			<a href="',UrlTable::admin_admins ('show', $pseudo),'">Voir la fiche de ',$pseudo,'</a>
		</p>
		<p>
			',print_button ('Retour', UrlTable::admin_admins (),
			                'Retour à la gestion des administrateurs'),'
		</p>';
	}
	else
	{
		echo '
		<h3>Erreur</h3>';
		if ($return)
		{
			echo '
			<p>Les données que vous avez entrées sont erronées.</p>';
		}
		else
		{
			echo '
			<p>
				Une erreur interne s\'est produite, et l\'action a été arrêtée pour 
				éviter des erreurs. Veuillez contacter votre administrateur principal
				et lui comuniquer le message suivant&nbsp;:
			</p>
			<fieldset class="error">
				<legend>Erreur&nbsp;:</legend>
				SQL query failed. The response of <span class="phpfunc">MyDB::insert()</span> is: 
				<span class="phpbool">',($return) ? 'true' : 'false','</span> <br />
				The values of variables are: <br />
				<span class="phpvar">$pseudo</span>: &nbsp; <span class="phpval">',$pseudo,'</span>;<br />
				<span class="phpvar">$pass</span>: &nbsp; &nbsp; <span class="phpval">',$pass,'</span>;<br />
				<span class="phpvar">$passconf</span>: <span class="phpval">',$passconf,'</span>;<br />
				<span class="phpvar">$level</span>: &nbsp; &nbsp;<span class="phpval">',$level,'</span>.<br />
				<br />
				In function <span class="phpfunc">',__FUNCTION__,'()</span> in file "',__FILE__,'"
			</fieldset>';
		}
		echo '
		<p>
			',print_backbutton ('Retour', UrlTable::admin_admins (),
			                    'Retour à la page précédente'),'
		</p>';
	}
	
	return $return;
}

/** FIN DES FONCTIONS **/


// est-ce que l'admin est un "super-admin" ?
$master = User::has_rights (ADMIN_LEVEL_MASTER);


/* affichage de la page */
echo '
	<div id="presentation">
		<h2>Gestion des administrateurs</h2>
		<p>
		</p>
	</div>
	<div id="content">';
// =================================================================


if (isset ($_GET['action']))
{
	if ($_GET['action'] == 'edit')
	{
		edit_user ($_GET['pseudo']);
	}
	elseif ($_GET['action'] == 'save')
	{
		save_edit_user ($_POST['oldpseudo'], $_POST['pseudo'], 
		                $_POST['newpasswd'], $_POST['newpasswdconf'], 
		                (integer)($_POST['oldlevel']), (integer)($_POST['level']));
	}
	elseif ($_GET['action'] == 'rm')
	{
		if ($_POST['rmuser'] == 'yes')
			remove_user ($_GET['pseudo']);
		else
			remove_user_query ($_GET['pseudo']);
	}
	elseif ($_GET['action'] == 'show')
	{
		display_user ($_GET['pseudo']);
	}
	elseif ($_GET['action'] == 'add')
	{
		if (isset ($_POST['pseudo'], $_POST['passwd'], $_POST['passwdconf'], $_POST['level']))
			add_user ($_POST['pseudo'], $_POST['passwd'], $_POST['passwdconf'], (integer)$_POST['level']);
		else
			add_user_query ();
	}
	else
	{
		echo '
		<p>Action invalide.</p>';
	}
}
else
{
	display_table();
}



?>

</div>
