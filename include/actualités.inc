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

require_once ('MyDB.php');
require_once ('BCode.php');
require_once ('User.php');
require_once ('defines.php');
require_once ('feeds.php');


/* DEBUG : */
//MyDB::set_die (True);

/* this function adds a new news in the DB */
function add_message ($title, $source, $author) {
	/* if a var is empty, returns */
	if (empty ($title) || empty ($source) || empty ($author))
		return False;
	
	$content = BCode::parse ($source);
	
	$title = addslashes ($title);
	$source = addslashes ($source);
	$content = addslashes ($content);
	$author = addslashes ($author);
	
	$rv = True;
	$date = time ();

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	$rv = $db->insert ("'', '$date', '$date', '$title', '$content', '$source', '$author', '$author'");

	unset ($db);
	
	feed_update_news ();
	
	return $rv;
}
/* this function update an existing message */
function update_message ($id, $title, $source, $author) {
	if (empty ($id) || empty ($title) || empty ($source) || empty ($author))
		return False;

	$content = BCode::parse ($source);
	
	$title = addslashes ($title);
	$source = addslashes ($source);
	$content = addslashes ($content);
	$author = addslashes ($author);
	$mdate = time ();
	$mauthor = addslashes (User::get_name ());
	
	$rv = True;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	$rv = $db->update ("`mdate`='$mdate', `titre`='$title', `contenu`='$content', `source`='$source', `auteur`='$author', `mauthor`='$mauthor'", "`id`=$id");

	unset ($db);
	
	feed_update_news ();

	return $rv;
}
/* this function remove an existing message */
function remove_message ($id) {
	if (empty ($id))
		return False;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	$rv = $db->delete ("`id`='$id'");

	unset ($db);
	
	feed_update_news ();

	return $rv;
}

/* this function display a table containg or not values. */
function display_table ($title='', $source='', $author='', $action='new', $id='') {
	if (empty ($author))
		$author = User::get_name ();

/*
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (USERS_TABLE);
	$db->select ('`username`');
*/

	$title = htmlspecialchars (stripslashes ($title));
//    $content = htmlentities (stripslashes ($content));
	$source = stripslashes ($source);
	$author = htmlspecialchars (stripslashes ($author));


	echo '
<form method="post" action="?page=', strtolower (PAGE), '&action=', $action, '&id=', $id, '">
		<p id="form_entrees">
			<label>T<span class="u">i</span>tre : <br />
				<input type="text" name="titre" tabindex="20" accesskey="I" value="', htmlspecialchars ($title), '"/>
			</label>
			<br />
			<label><span class="u">C</span>ontenu : <br />
				<textarea name="contenu" rows="15" tabindex="30" accesskey="c">',
					htmlspecialchars ($source),
				'</textarea>
			</label>';
			echo '<input type="hidden" name="auteur" value="', $author, '" />';
			/* // gestion de changement de l'auteur
			<br />
			<label>A<span class="u">u</span>teur : <br />
			<select tabindex="40" accesskey="u" name="auteur">';

	while ($user = $db->fetch_response ()) {
		echo '<option value="', $user[0], '"';
		if ($user[0] == $author)
			echo ' selected="selected"';
		echo '>', $user[0], '</option>';
	}

	echo '</select>
			</label>
			*/
	echo '
		</p>
		<p class="form_buttons">
			<input type="submit" value="Poster" accesskey="P" title="Poster la nouvelle (Alt + P)"/>
			<input type="reset" accesskey="x" title="Vider le formulaire (Alt + X)"/>
			<a href="?page=', strtolower (PAGE), '"
			   onclick="window.location.replace(this.href)">
				<input type="button" value="Annuler" />
			</a>
		</p>
	</form>';

	/*
	unset ($db);
	*/
}
function display_table_new () {
   display_table ();
}
function display_table_edit ($title='', $source='', $author='', $id='') {
   display_table ($title, $source, $author, 'edit', $id);
}

/* this function return an array of getted values on success or False on failure */
function get_message ($id) {
	if (empty ($id))
		return False;

	$rv = False;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	if ($db->select ('*', "`id`=$id")) {
		$rv = $db->fetch_response ();
	}

	unset ($db);

	return $rv;
}

function get_all_messages () {
	$rv = false;
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	if ($db->select ('*', '', 'id')) {
		$rv = array ();
		while ($subarr = $db->fetch_response ()) {
			$rv[] = $subarr;
		}
	}
	
	unset ($db);
	
	return $rv;
}


function print_all_messages () {
	$messages = get_all_messages ();
	$page = strtolower (PAGE);

	echo '<table>
			<caption>Liste des actualités</caption>
			
			<thead>
				<tr>
					<th>Titre</th>
					<th>Auteur</th>
					<th>Date</th>
					<th></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Auteur</th>
					<th>Date</th>
					<th></th>
				</tr>
			</tfoot>
			<tbody>';
	foreach ($messages as $message) {
/*
		$titre = htmlentities (stripslashes ($message['titre']));
		$auteur = htmlentities (stripslashes ($message['auteur']));
*/
		$titre = htmlspecialchars (stripslashes ($message['titre']));
		$auteur = htmlspecialchars (stripslashes ($message['auteur']));
		
		echo '<tr>
					<td>
						<a href="?page=', $page, '&amp;action=edit&amp;id=', $message['id'], '"
							title="Modifier «&nbsp;', $titre, '&nbsp;»">
							', $titre, '
						</a>
					</td>
					<td>
						<a href="?page=administrateurs&amp;pseudo=', $auteur, '"
							title="Voir la fiche de ', $auteur, '">
							', $auteur, '
						</a>
					</td>
					<td>', date ('d/m/y (H:i)', $message['date']), '</td>
					<td>
						<a href="?page=', $page, '&amp;action=rm&amp;id=', $message['id'], '"
							title="Supprimer «&nbsp;', $titre, '&nbsp;»">
							<img src="styles/', STYLE, '/delete.png" alt="Supprimer" />
						</a>
					</td>
				</tr>';
	}

	echo '</tbody>
			</table>';
}



/* affichage de la page */
echo '<div id="presentation">
			<h2>Gestion des News</h2>
				<p>
					 Création, edition &amp; suppression des news.
				</p>
		</div>
				
		<div id="content">';
// =================================================================

if (User::get_level () <= NEWSLEVEL) {
	if (urldecode ($_GET['action']) == 'edit') {
		if ($_GET['id']) {
			$message = get_message ($_GET['id']);
			
			$titre = htmlspecialchars (stripslashes ($message['titre']));
			
			echo '<h2>Édition de « ', $titre, ' »</h2>';
			
			if ($_POST['titre'] && $_POST['contenu'] && $_POST['auteur'])
			{
				$titre = htmlspecialchars (stripslashes ($_POST['titre']));
				
				if (update_message ($_GET['id'],
				                    $_POST['titre'],
				                    $_POST['contenu'],
				                    $_POST['auteur']))
				{
					echo '<p>« ', $titre, ' » mis à jour avec succès.</p>';
				}
				else
				{
					echo '<p>Erreur lors de la mise à jour de « ', $titre, ' ».</p>';
				}
				echo '
				<p>
					<a href="?page=', strtolower (PAGE), '"
					   onclick="window.location.replace(this.href)">
						<input type="button" value="Retour" />
					</a>
				</p>';
			}
			else 
			{
				if (!empty ($message))
				{
					display_table_edit ($message['titre'],
					                    $message['source'],
					                    $message['auteur'],
					                    $message['id']);
				}
				else
					echo '
					<p>Erreur&bsnp;: message inéxistant.</p>
					<p>
						<a href="?page=', strtolower (PAGE), '"
						   onclick="window.location.replace(this.href)">
							<input type="button" value="Retour" />
						</a>
					</p>';
			}
		}
		else {
			echo '
			<p>Erreur&nbsp;: pas d\'ID</p>
			<p>
				<a href="?page=', strtolower (PAGE), '"
				   onclick="window.location.replace(this.href)">
					<input type="button" value="Retour" />
				</a>
			</p>';
		}
	}
	else if (urldecode ($_GET['action']) == 'rm')
	{
		if ($_GET['id'])
		{
			$message = get_message ($_GET['id']);
			
			if (!empty ($message))
			{
				$titre = htmlspecialchars (stripslashes ($message['titre']));
				
				if ($_POST['rm'])
				{
					if (remove_message ($_GET['id']))
					{
						echo '
						<p>« ', $titre, ' » supprimé avec succès.</p>
						<p>
							<a href="?page=', strtolower (PAGE), '"
							   onclick="window.location.replace(this.href)">
								<input type="button" value="Retour" />
							</a>
						</p>';
					}
					else
					{
						echo '
						<p>Erreur lors de la suppression de « ', $titre, ' »</p>
						<p>
							<a href="?page=',strtolower (PAGE),'"
							   onclick="window.location.replace(this.href)">
								<input type="button" value="Retour" />
							</a>
						</p>';
					}
				}
				else
				{
					echo '
					<p>voulez-vous vraiment buter tout « ', $titre, ' » ?</p>
					<form method="post" action="?page=', strtolower (PAGE), '&amp;action=rm&amp;id=', $_GET['id'], '">
						<p>
							<input type="hidden" name="rm" value="rm" />
							<input type="submit" value="Supprimer" />
							<a href="?page=', strtolower (PAGE), '"
								 onclick="window.location.replace(this.href)">
								<input type="button" value="Annuler" />
							</a>
						</p>
					</form>';
				}
			}
			else
			{
				echo '
				<p>Erreur : ID inéxistante.</p>
				<p>
					<a href="?page=', strtolower (PAGE), '"
					   onclick="window.location.replace(this.href)">
						<input type="button" value="Retour" />
					</a>
				</p>';
			}
		}
		else
		{
			echo '
			<p>Erreur : pas d\'ID</p>
			<p>
				<a href="?page=', strtolower (PAGE), '"
				   onclick="window.location.replace(this.href)">
					<input type="button" value="Retour" />
				</a>
			</p>';
		}
	}
	else if (urldecode ($_GET['action']) == 'new')
	{
		if ($_POST['titre'] && $_POST['contenu'] && $_POST['auteur'])
		{
			$titre = htmlspecialchars (stripslashes ($_POST['titre']));
			
			if (add_message ($_POST['titre'],
			                 $_POST['contenu'],
			                 $_POST['auteur']))
			{
				echo '
				<p>« ', $titre, ' » créé avec succès.</p>
				<p>
					<a href="?page=', strtolower (PAGE), '"
					   onclick="window.location.replace(this.href)">
						<input type="button" value="Retour" />
					</a>
				</p>';
			}
			else
			{
				echo '
				<p>Erreur lors de la création de « ', $titre, ' »</p>
				<p>
					<a href="?page=', strtolower (PAGE), '"
					   onclick="window.location.replace(this.href)">
						<input type="button" value="Retour" />
					</a>
				</p>';
			}
		}
		else
		{
			display_table_new ();
		}
	}
	else if (urldecode ($_GET['action']) == 'update_feeds')
	{
		if (feed_update_news () && feed_update_devel ())
		{
			echo '
			<p>Flus mis à jour avec succès</p>
			<p>
				<a href="?page=', strtolower (PAGE), '"
					 onclick="window.location.replace(this.href)">
					<input type="button" value="Retour" />
				</a>
			</p>';
		}
		else
		{
			echo '
			<p>Erreur lors de la mise à jour des flux</p>
			<p>
				<a href="?page=', strtolower (PAGE), '"
					 onclick="window.location.replace(this.href)">
					<input type="button" value="Retour" />
				</a>
			</p>';
		}
	}
	else
	{
		echo '
		<p>
			<a href="?page=', strtolower (PAGE), '&amp;action=new"
			   onclick="window.location.replace(this.href)"
			   title="Créer une nouvelle actualité">',
				'<input type="button" value="Nouvelle nouvelle" />',
			'</a>
			
			<a href="?page=', strtolower (PAGE), '&amp;action=update_feeds"
			   onclick="window.location.replace(this.href)"
			   title="Forcer la mise à jour des fulx Atom et RSS pour les actualités et les actualités du développement">',
				'<input type="button" value="Forcer la mise à jour des flux" />',
			'</a>
		</p>';
		print_all_messages ();
	}
}
else
{
	echo '<p>Petit curieux, tu n\'as pas le droit de voir ça&nbsp;!</p>';
}


?>

</div>
