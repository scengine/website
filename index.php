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

/*
 * index & news
 */

define (TITLE, 'Accueil');
define (NEWS_OFFSET, 8);

require_once ('include/defines.php');
require_once ('include/top.minc');
require_once ('include/MyDB.php');
require_once ('include/BCode.php');


$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';


function get_news ($start=0, $end=NEWS_OFFSET) {
	$rv = Array ();

	if ($end < 0)
		$end = $start + NEWS_OFFSET;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
	$db->select_table (NEWS_TABLE);
	$db->select ('*', '', 'id', 'DESC', $start, $end);

	for ($i = 0; False !== ($content = $db->fetch_response ()); $i++) {
		$rv[$i] = $content;
	}
	
	unset ($db);

	return $rv;
}

function get_n_news ()
{
	$n = 0;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
/*
	if ($db->query('SELECT COUNT(*) AS n FROM news') !== false)
	{
		$data = $db->fetch_response ();
		$n = $data['n'];
	}
*/
	$db->select_table (NEWS_TABLE);
	$n = $db->count ();
	
	unset ($db);

	return $n;
}

function escape_html_quotes (&$str)
{
	$str = str_replace ('"', '&quot;', $str);
	return $str;
} 

function print_news ($start=0) {
	$news = get_news ($start, NEWS_OFFSET);

	// pour permetre aux admins d'ajouter une news
	if (User::get_logged () &&
		User::get_level () <= NEWSLEVEL) {
		echo '<p>[<a href="admin.php?page=actualités&amp;action=new">Ajouter une news</a>]</p>';
	}

	foreach ($news as $new) {
		echo '<div class="new">';
		
		if (User::get_logged () &&
			User::get_level () <= NEWSLEVEL) {
			echo '<div class="admin">';
	//                  [<a href="admin/?page=actualités&amp;id=', $new['id'], '&amp;action=édit">Éditer</a>]
	//                  [<a href="admin/?page=actualités&amp;id=', $new['id'], '&amp;action=rm">Supprimer</a>]
			echo '[<a onclick="edit(\'n', $new['id'], '\', this)">Éditer</a>]
				  [<a href="post.php?sec=news&amp;id=', $new['id'], '&amp;act=rm"
					  onclick="return confirm(\'Voulez-vous vraiment supprimer cette news ?\')">Supprimer</a>]';
			echo '</div>';
			
			echo '<div class="formedit" id="fn', $new['id'], '" style="display:none;">
					<form method="post" action="post.php?sec=news&amp;id=', $new['id'], '&amp;act=edit">
						<div>
							<a href="javascript:entry_more(\'tn', $new['id'], '\')">[+]</a>
							<a href="javascript:entry_lesser(\'tn', $new['id'], '\')">[-]</a>
						</div>
						<p>
							<input type="hidden" name="date" value="', $new['date'], '" />
							<label>Titre&nbsp;:<br />
							<input type="text" name="title" value="', escape_html_quotes (stripslashes ($new['titre'])), '" />
							</label>
							<br />
							<br />
							<label>Contenu&nbsp;:<br />
							<textarea name="content" cols="24" rows="16" id="tn', $new['id'], '">',
								stripslashes (BCode::unparse ($new['source'])),
							'</textarea>
							</label>
							<br />
							<input type="submit" value="Envoyer" />
							<input type="reset" value="Réinitialiser" />
						</p>
					</form>
				  </div>';
		}
		
		echo '<div id="mn', $new['id'], '">';
		echo '<h3 id="n', $new['id'], '"><a href="#n', $new['id'], '">', escape_html_quotes (stripslashes ($new['titre'])), '</a></h3>';
		echo '<div class="author"><p>Par <span class="b">',
			stripslashes ($new['auteur']),
			'</span> le ',
			date ('d/m/Y à H:i',
			$new['date']),
			'</p></div>';
			//<p>';
		echo stripslashes ($new['contenu']);
		echo //'</p>
			 '</div>
			</div>';
	}
}


function print_page_browser ($current)
{
	$bstr = '';
	$n_news = get_n_news ();
	$n_pages = ceil ($n_news / NEWS_OFFSET);
	$has_prev = ($current > 0) ? true : false;
	$has_next = ($n_news > $current + NEWS_OFFSET) ? true : false;

	if ($has_prev)
		echo '<a href="?s=',
			($current >= NEWS_OFFSET) ? $current - NEWS_OFFSET : 0,
			'">&lt;</a> ';


	for ($i=0; $i<$n_pages; $i++)
	{
		$cur_offset = $i * NEWS_OFFSET;
		
		if ($cur_offset == $current)
			echo $i+1, ' ';
		else
			echo '<a href="?s=', $cur_offset, '">', $i+1, '</a> ';
	}


	if ($has_next)
		echo ' <a href="?s=', $current + NEWS_OFFSET, '">&gt;</a>';
}


/***********/


$start_news = (isset ($_GET['s'])) ? $_GET['s'] : 0;
settype ($start_news, 'integer');
if ($start_news < 0) $start_news = 0;

?>

	<div id="presentation">
		<h2>Bienvenue sur le site officiel du SCEngine</h2>
		<p>
			Le <acronym title="Simple C Engine">SCEngine</acronym> est un
			<a href="http://fr.wikipedia.org/wiki/Moteur_3D">moteur 3D</a> programmé,
			comme son nom l'indique, en langage C. Il est libre, open-source, et distribué sous
			<a href="licence.php">licence GNU GPL</a>. Il utilise exclusivement
			l'<acronym title="Application Programming Interface">API</acronym> OpenGL pour le rendu.
		</p>
	</div>

	<div id="content"><!--
		<h2>Dernières news</h2>-->

		<?php
		
		print_news ($start_news);
		
		/* buttons for other pages */
		echo '<div class="newslinks">';
		print_page_browser ($start_news);
		echo '</div>';
		
		?>

	</div>

<?php

require_once ('include/bottom.minc');

?>
