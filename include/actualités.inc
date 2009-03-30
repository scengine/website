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

require_once ('lib/MyDB.php');
require_once ('lib/BCode.php');
require_once ('lib/User.php');
require_once ('include/defines.php');
require_once ('lib/feeds.php');
require_once ('lib/misc.php');
require_once ('lib/News.php');


/* this function display a form containg or not values. */
function display_form ($title='', $source='', $action='new', $id='') {
	News::print_form ($title, $source, $action, $id, 'admin.php?page='.strtolower (PAGE), 
	                  get_button ('Annuler', '?page='.strtolower (PAGE)));
}

function display_form_new () {
	display_form ();
}

function display_form_edit ($title='', $source='', $id='') {
	display_form ($title, $source, 'edit', $id);
}

function print_all_messages ()
{
	$news = News::get ();
	$page = strtolower (PAGE);
	
	echo '
	<table>
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
	foreach ($news as $new) {
		$title = htmlspecialchars ($new['title']);
		$author = htmlspecialchars ($new['author']);
		
		echo '
		<tr>
			<td>
				<a href="?page=',$page,'&amp;action=edit&amp;id=',$new['id'],'"
					title="Modifier «&nbsp;',$title,'&nbsp;»">
					',$title,'
				</a>
			</td>
			<td>
				<a href="?page=administrateurs&amp;pseudo=',$author,'"
				  title="Voir la fiche de ',$auteur,'">
					',$author,'
				</a>
			</td>
			<td>', date ('d/m/y (H:i)', $new['date']), '</td>
			<td>
				<a href="?page=',$page,'&amp;action=rm&amp;id=',$new['id'],'"
				   onclick="return news_delete (\'',$new['id'],'\')"
				   title="Supprimer «&nbsp;',$title,'&nbsp;»">
					<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
				</a>
			</td>
		</tr>';
	}
	
	echo '
		</tbody>
	</table>';
}



/* affichage de la page */
echo '
	<div id="presentation">
	<h2>Gestion des News</h2>
		<p>
			 Création, edition &amp; suppression des news.
		</p>
	</div>
	
	<div id="content">';
// =================================================================

if (User::has_rights (ADMIN_LEVEL_NEWS)) {
	if ($_GET['action'] == 'edit') {
		if ($_GET['id']) {
			$new = News::get_by_id ($_GET['id']);
			
			if ($new !== false)
			{
				$title = htmlspecialchars ($new['title']);
				
				echo '<h2>Édition de «&nbsp;',$title,'&nbsp;»</h2>';
				display_form_edit ($new['title'], $new['source'], $new['id']);
			}
			else {
				echo '
				<p>Erreur&bsnp;: message inéxistant.</p>
				<p>
					',print_button ('Retour', '?page='.strtolower (PAGE)),'
				</p>';
			}
		}
		else {
			echo '
			<p>Erreur&nbsp;: pas d\'ID</p>
			<p>
				',print_button ('Retour', '?page='.strtolower (PAGE)),'
			</p>';
		}
	}
	else if ($_GET['action'] == 'rm')
	{
		if ($_GET['id'])
		{
			$new = News::get_by_id ($_GET['id']);
			
			if ($new !== false)
			{
				$title = htmlspecialchars ($new['title']);
				echo '
				<p>Voulez-vous vraiment supprimer «&nbsp;', $title, '&nbsp;»&nbsp;?</p>
				<form method="post"
				      action="post.php?sec=news&amp;act=rm&amp;id=',$_GET['id'],'&amp;redirect=',urlencode ('admin.php?page='.strtolower (PAGE)),'">
					<p>
						<input type="hidden" name="rm" value="rm" />
						<input type="submit" value="Supprimer" />
						',print_button ('Annuler', '?page='.strtolower (PAGE)),'
					</p>
				</form>';
			}
			else
			{
				echo '
				<p>Erreur : ID inéxistante.</p>
				<p>
					',print_button ('Retour', '?page='.strtolower (PAGE)),'
				</p>';
			}
		}
		else
		{
			echo '
			<p>Erreur : pas d\'ID</p>
			<p>
				',print_button ('Retour', '?page='.strtolower (PAGE)),'
			</p>';
		}
	}
	else if ($_GET['action'] == 'new')
	{
		display_form_new ();
	}
	else if ($_GET['action'] == 'update_feeds')
	{
		if (feed_update_news () && feed_update_devel ())
		{
			echo '
			<p>Flux mis à jour avec succès</p>
			<p>
				',print_button ('Retour', '?page='.strtolower (PAGE)),'
			</p>';
		}
		else
		{
			echo '
			<p>Erreur lors de la mise à jour des flux</p>
			<p>
				',print_button ('Retour', '?page='.strtolower (PAGE)),'
			</p>';
		}
	}
	else
	{
		echo '
		<p>
			',print_button ('Nouvelle nouvelle', '?page='.strtolower (PAGE).'&action=new',
			                'Créer une nouvelle actualité'),'
			
			',print_button ('Forcer la mise à jour des flux', '?page='.strtolower (PAGE).'&action=update_feeds',
			                'Forcer la mise à jour des fulx Atom et RSS pour les actualités et les actualités du développement'),'
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
