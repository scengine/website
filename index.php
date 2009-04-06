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
define (NEWS_PREVIEW_SIZE, 250);
define (NEWS_BY_PAGE, 8);

require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/News.php');
require_once ('lib/MyDB.php');
require_once ('lib/BCode.php');

$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';

/** prints a news
 * \param $new the array of the news
 * \param $more whether to trunctate content and show 'more...' if news content
 *        is longer than the configured size
 */
function print_new (array &$new, $more=false)
{
	$permalink = UrlTable::news ($new['id'], $new['title']);
	
	echo '
	<div class="new">';
	
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		echo '
		<div class="admin">
			[<a href="',UrlTable::admin_news ('edit', $new['id']),'"
			    onclick="return news_edit (\'n',$new['id'],'\', this);">Éditer</a>]
			[<a href="',UrlTable::admin_news ('rm', $new['id']),'"
			    onclick="return news_delete (\'',$new['id'],'\');">Supprimer</a>]
		</div>';
		News::print_form ($new['title'], $new['source'], 'edit', $new['id'],
		                  null, '', 'style="display:none;"');
	}
	
	echo '
		<div id="mn',$new['id'],'">
			<h3 id="n',$new['id'],'">
				<a href="',$permalink,'">',
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
			</div>';
	if ($more)
	{
		echo xmlstr_shortcut ($new['content'], NEWS_PREVIEW_SIZE,
		                      '… <a href="'.$permalink.'" class="more">lire la suite</a>');
	}
	else
		echo $new['content'];
	echo '
			<div class="clearer"></div>
		</div>
	</div>';
}

function print_news ($start=0) {
	$news = News::get ($start, NEWS_BY_PAGE);
	
	// pour permetre aux admins d'ajouter une news
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		echo '
		<div class="fleft">
			[<a href="',UrlTable::admin_news ('new'),'"
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
	
	foreach ($news as &$new) {
		print_new ($new);
	}
}


function print_page_browser ($current)
{
	$bstr = '';
	$n_news = News::get_n ();
	$n_pages = ceil ($n_news / NEWS_BY_PAGE);
	$has_prev = ($current > 0) ? true : false;
	$has_next = ($n_pages > $current + 1) ? true : false;
	
	if ($has_prev)
		echo '<a href="',UrlTable::news_page ($current),'">&lt;</a> ';
	
	for ($i=0; $i < $n_pages; $i++)
	{
		if ($i == $current)
			echo $i + 1, ' ';
		else
			echo '<a href="',UrlTable::news_page ($i + 1),'">',$i + 1,'</a> ';
	}
	
	if ($has_next)
		echo ' <a href="',UrlTable::news_page ($current + 1 + 1),'">&gt;</a>';
}

function print_one_news ($id)
{
	$news = News::get_by_id ($id);
?>
	<div id="content" class="nopresentation">
		<?php
		
		if ($news !== false)
			print_new ($news);
		else
		{
			echo '
			<h2>Erreur&nbsp;!</h2>
			<p>
				La news que vous cherchez n\'existe pas, a été supprimée ou déplacée.
			</p>
			<p><a href="',UrlTable::news (),'">Retour à la liste des news</a></p>';
		}
		
		?>
	</div>
<?php
}

function print_home ()
{
	$start_news = (isset ($_GET['page'])) ? (int)($_GET['page'] - 1) : 0;
	if ($start_news < 0)
		$start_news = 0;
	
?>
	<div id="presentation">
		<h2>Bienvenue sur le site officiel du SCEngine</h2>
		<p>
			Le <acronym title="Simple C Engine">SCEngine</acronym> est un
			<a href="http://fr.wikipedia.org/wiki/Moteur_3D">moteur 3D</a> programmé,
			comme son nom l'indique, en langage C. Il est libre, open-source, et distribué sous
			<a href="<?php echo UrlTable::license (); ?>">licence GNU GPL</a>. Il utilise exclusivement
			l'<acronym title="Application Programming Interface">API</acronym> OpenGL pour le rendu.
		</p>
	</div>
	
	<div id="content"><!--
		<h2>Dernières news</h2>-->
		
		<?php
		
		print_news ($start_news * NEWS_BY_PAGE);
		
		/* buttons for other pages */
		echo '<div class="newslinks">';
		print_page_browser ($start_news);
		echo '</div>';
		
		?>
	
	</div>
<?php
}

/***********/


require_once ('include/top.minc');

if (isset ($_GET['shownews']) && settype ($_GET['shownews'], integer))
{
	print_one_news ($_GET['shownews']);
}
else
{
	print_home ();
}

require_once ('include/bottom.minc');
