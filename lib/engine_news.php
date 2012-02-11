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


require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/MyDB.php');
require_once ('lib/User.php');
require_once ('lib/string.php');

define ('ENGINE_NEWS_BY_PAGE', 16);
define ('ENGINE_NEWS_GET_PREFIX', 'devel_page');

/* required JavaScript */
//$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';

function get_engine_news ($start=0, $end=ENGINE_NEWS_BY_PAGE) {
	$rv = Array ();

	if ($end < 0)
		$end = $start + ENGINE_NEWS_BY_PAGE;

	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (DEVEL_TABLE);
	$db->select ('*', '', 'id', 'DESC', $start, $end);

	for ($i = 0; False !== ($content = $db->fetch_response ()); $i++) {
		$rv[$i] = $content;
	}

	unset ($db);

	return $rv;
}

function get_engine_n_news ($reload=false)
{
	static $init = false;
	static $n = 0;
	
	if (!$init || $reload)
	{
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (DEVEL_TABLE);
		
		$n = $db->count ();
		
		unset ($db);
		
		$init = true;
	}
	
	return $n;
}

function print_engine_page_browser ($current)
{
	$n_news = get_engine_n_news ();
	$n_pages = ceil ($n_news / ENGINE_NEWS_BY_PAGE);
	$has_prev = ($current > 0) ? true : false;
	$has_next = ($n_pages > $current + 1) ? true : false;
	
	if ($has_prev)
		echo '<a href="',UrlTable::devel_news_page ($current),'">&lt;</a> ';
	
	for ($i=0; $i < $n_pages; $i++)
	{
		if ($i == $current)
			echo $i + 1, ' ';
		else
			echo '<a href="',UrlTable::devel_news_page ($i + 1),'">',$i + 1,'</a> ';
	}
	
	if ($has_next)
		echo ' <a href="',UrlTable::devel_news_page ($current + 1 + 1),'">&gt;</a>';
}


/* gets a colour from the age of a news */
function get_color_for_oldness ($age)
{
	$old = 86400; // 24 hours
	$x = min ((time () - $age) / $old, 1.0) / 1.6;
	/* RGB color */
	$color = array (0.9 - $x, $x, 0.0);
	
	$convtable = array (
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'
	);
	
	$out = '';
	foreach ($color as $c)
	{
		$id = $c * (count ($convtable) -1);
		$out .= $convtable[$id];
	}
	
	return $out;
}

function print_one_engine_news (array &$new)
{
	$user_can_post = User::has_rights (ADMIN_LEVEL_NEWSDEVEL);
	
	/* color box */
	echo '
	<div class="datecolor"
	     style="background-color: #',get_color_for_oldness ($new['date']),';">
	</div>';
	
	/* title */
	echo '
	<h4 id="m',$new['id'],'">',date ('d/m/Y à H\hi', $new['date']),'</h4>';
	
	if ($user_can_post)
	{
		echo '
		<div class="admin">
			[<a onclick="news_edit(\'m',$new['id'],'\', this)" title="éditer">éditer</a>]
			[<a href="post.php?sec=devel&amp;act=rm&amp;id=',$new['id'],'"
			    onclick="return confirm(\'Voulez-vous vraiment supprimer ce post ?\')"
			    title="Supprimer">X</a>]
		</div>';
	}
	
	echo '
	<p id="mm',$new['id'],'">',
		stripslashes ($new['content']),
	'</p>';
	
	if ($user_can_post)
	{
		echo '
		<div class="formedit" id="fm',$new['id'],'" style="display:none;">
			<a href="javascript:entry_more(\'tm',$new['id'],'\')">[+]</a>
			<a href="javascript:entry_lesser(\'tm',$new['id'],'\')">[-]</a>
			<form method="post" action="post.php?sec=devel&amp;act=edit&amp;id=',$new['id'],'">
				<p>
					<textarea name="content" cols="24" rows="8" id="tm',$new['id'],'">',
						br2nl (stripslashes ($new['content'])),
					'</textarea>
					<input type="submit" value="Poster" />
					<!--input type="reset" value="Reset" /-->
				</p>
			</form>
		</div>';
	}
}

function print_engine_news ()
{
	$user_can_post = User::has_rights (ADMIN_LEVEL_NEWSDEVEL);
	$start_news = 0;
	
	$start_news = (isset ($_GET[ENGINE_NEWS_GET_PREFIX])) ? (int)($_GET[ENGINE_NEWS_GET_PREFIX] - 1) : 0;
	if ($start_news < 0) $start_news = 0;
	
	$news = get_engine_news ($start_news * ENGINE_NEWS_BY_PAGE, ENGINE_NEWS_BY_PAGE);
	
	echo '
	<div class="links right">
		Flux
			<a href="',DEVEL_ATOM_FEED_FILE,'" title="S\'abonner au flux Atom">',
				'Atom&nbsp;<img src="styles/',STYLE,'/feed-atom.png" alt="Flux Atom" />',
			'</a>
		/
		<a href="',DEVEL_RSS_FEED_FILE,'" title="S\'abonner au flux RSS">',
			'RSS&nbsp;<img src="styles/',STYLE,'/feed-rss.png" alt="Flux RSS" />',
		'</a>
	</div>';
	
	if ($user_can_post)
	{
		
		# si le JS n'est pas activé, l'erreur s'affichera
		echo '
		<noscript>
			<div style="color: red; font-weight: bold; text-decoration: blink; margin-top: 10px;">
				<strong>
					ATTENTION&nbsp;:<br />
					le JavaScript n\'est pas activé, la suppression des news se fera sans
					confirmation.
				</strong>
			</div>
		</noscript>';
		
		echo '
		<h4>',date ("d/m/Y à H\hi"),'</h4>
		<form method="post" action="post.php?sec=devel&amp;act=new">
			<div>
				<a href="javascript:entry_more(\'tnewdevel\')">[+]</a>
				<a href="javascript:entry_lesser(\'tnewdevel\')">[-]</a>
			</div>
			<p>
				<textarea name="content" cols="24" rows="8" id="tnewdevel"></textarea>
				<input type="submit" value="Poster" />
			</p>
		</form>';
	}
	
	echo '
	<div class="devel_news_box">';
	
	foreach ($news as &$new)
	{
		print_one_engine_news ($new);
	}
	
	echo '
	</div>';
	
	echo '
	<br />
	<div class="newslinks">';
	print_engine_page_browser ($start_news);
	echo '
	</div>';
	
	unset ($user_can_post, $news, $new);
}
