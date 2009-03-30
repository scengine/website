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

/*
 * index & news
 */

define (TITLE, 'Accueil');
define (NEWS_OFFSET, 8);

require_once ('include/defines.php');
require_once ('lib/News.php');
require_once ('lib/MyDB.php');
require_once ('lib/BCode.php');

$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';

function print_news ($start=0) {
	$news = News::get ($start, NEWS_OFFSET);
	
	// pour permetre aux admins d'ajouter une news
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		echo '
		<div class="fleft">
			[<a href="admin.php?page=actualités&amp;action=new"
			    onclick="return toggle_display (\'fld_nnew\', \'block\');">Ajouter une news</a>]
		</div>';
	}
	
	echo '
	<div class="links right">
		Flux
			<a href="',NEWS_ATOM_FEED_FILE,'" title="S\'abonner au flux Atom">',
				'Atom&nbsp;<img src="styles/',STYLE,'/feed-atom.png" alt="Flux Atom" />',
			'</a>
		/
		<a href="',NEWS_RSS_FEED_FILE,'" title="S\'abonner au flux RSS">',
			'RSS&nbsp;<img src="styles/',STYLE,'/feed-rss.png" alt="Flux RSS" />',
		'</a>
	</div>';
	
	if (User::has_rights (ADMIN_LEVEL_NEWS))
	{
		// new news form is displayed only by JS, then without JS it doesn't bother
		echo '<div class="new" id="fld_nnew" style="display: none">';
			News::print_form ('', '', 'new', 'new');
		echo '</div>';
	}
	
	foreach ($news as $new) {
		echo '
		<div class="new">';
		
		if (User::has_rights (ADMIN_LEVEL_NEWS)) {
			echo '
			<div class="admin">
				[<a href="admin.php?page=actualités&amp;id=',$new['id'],'&amp;action=edit"
				    onclick="return news_edit (\'n',$new['id'],'\', this);">Éditer</a>]
				[<a href="admin.php?page=actualités&amp;id=',$new['id'],'&amp;action=rm"
				    onclick="return news_delete (\'',$new['id'],'\');">Supprimer</a>]
			</div>';
			News::print_form ($new['title'], $new['source'], 'edit', $new['id'],
			                 null, '', 'style="display:none;"');
		}
		
		echo '
			<div id="mn',$new['id'],'">
				<h3 id="n',$new['id'],'">
					<a href="#n',$new['id'],'">',
						escape_html_quotes ($new['title']),
					'</a>
				</h3>
				<div class="author">
					<p>
						Par <span class="b">',$new['author'],'</span> le ',
						date ('d/m/Y à H:i', $new['date']);
		if ($new['date'] < $new['mdate'])
		{
			echo ' &ndash; édité par <span class="b">',$new['mauthor'],
				'</span> le ',date ('d/m/Y à H:i', $new['mdate']);
		}
		echo '
					</p>
				</div>
				',$new['content'],'
			</div>
		</div>';
	}
}


function print_page_browser ($current)
{
	$bstr = '';
	$n_news = News::get_n ();
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
settype ($start_news, integer);
if ($start_news < 0) $start_news = 0;

require_once ('include/top.minc');

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
