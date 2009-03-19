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


require_once ('defines.php');
require_once ('include/MyDB.php');
require_once ('include/User.php');
require_once ('include/string.php');

define (ENGINE_NEWS_OFFSET, 16);
define (ENGINE_NEWS_GET_PREFIX, 's_e');

/* required JavaScript */
$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';

function get_engine_news ($start=0, $end=ENGINE_NEWS_OFFSET) {
	$rv = Array ();

	if ($end < 0)
		$end = $start + ENGINE_NEWS_OFFSET;

	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
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
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
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
	$n_pages = ceil ($n_news / ENGINE_NEWS_OFFSET);
	$has_prev = ($current > 0) ? true : false;
	$has_next = ($n_news > $current + ENGINE_NEWS_OFFSET) ? true : false;

	if ($has_prev)
		echo '<a href="?', ENGINE_NEWS_GET_PREFIX, '=',
			($current >= ENGINE_NEWS_OFFSET) ? $current - ENGINE_NEWS_OFFSET : 0,
			'">&lt;</a> ';


	for ($i=0; $i<$n_pages; $i++)
	{
		$cur_offset = $i * ENGINE_NEWS_OFFSET;
		
		if ($cur_offset == $current)
			echo $i+1, ' ';
		else
			echo '<a href="?', ENGINE_NEWS_GET_PREFIX, '=', $cur_offset, '">', $i+1, '</a> ';
	}


	if ($has_next)
		echo ' <a href="?', ENGINE_NEWS_GET_PREFIX, '=', $current + ENGINE_NEWS_OFFSET, '">&gt;</a>';
}


/* gets a colour from the age of a news */
function get_color_for_oldness ($age)
{
	$old = (86400 * 1);
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

function print_engine_news ()
{
	$user_logged = User::get_logged ();
	$user_level  = User::get_level ();
	$start_news = 0;
	
	$start_news = (isset ($_GET[ENGINE_NEWS_GET_PREFIX])) ? $_GET[ENGINE_NEWS_GET_PREFIX] : 0;
	settype ($start_news, 'integer');
	if ($start_news < 0) $start_news = 0;
	
	$news = get_engine_news ($start_news, ENGINE_NEWS_OFFSET);
	
	echo '
	<div class="links right">
		Flux
			<a href="',DEVEL_ATOM_FEED_FILE,'" title="S\'abonner au flux Atom">',
				'Atom&nbsp;<img src="styles/default/feed-atom.png" alt="Flux Atom" />',
			'</a>
		/
		<a href="',DEVEL_RSS_FEED_FILE,'" title="S\'abonner au flux RSS">',
			'RSS&nbsp;<img src="styles/default/feed-rss.png" alt="Flux RSS" />',
		'</a>
	</div>';
	
	if ($user_logged &&
	    //User::get_name ()   == 'Yno')
	    $user_level == 0)
	{
		
		# si le JS n'est pas activé, l'erreur s'affichera
		echo '<div id="checkjs" style="color: red; font-weight: bold; text-decoration: blink;">
			ATTENTION&nbsp;:<br />
			le JavaScript n\'est pas activé.</div>';
		# ci-dessous, le code pour cacher le message (en JS, donc que si le JS marche)
		echo '<script type="text/javascript">
			if (document.all) // IE
				document.all["checkjs"].style.display = \'none\';
			else // autres
				document.getElementById ("checkjs").style.display = \'none\';
			</script>';
		
		echo '<h4>', date ("d/m/Y à H\hi"), '</h4>
				<form method="post" action="post.php?sec=devel&amp;act=new">
					<div>
						<a href="javascript:entry_more(\'tnewdevel\')">[+]</a>
						<a href="javascript:entry_lesser(\'tnewdevel\')">[-]</a>
					</div>
					<p>
						<input type="hidden" name="date" value="', mktime (), '"/>
						<textarea name="content" cols="24" rows="8" id="tnewdevel"></textarea>
						<input type="submit" value="Poster" />
					</p>
				</form>';
	}
	
	echo '
	<div class="news_engine_box">';
	
	foreach ($news as $new)
	{
		/* color box */
		echo '
		<div class="datecolor"
		     style="background-color: #',get_color_for_oldness ($new['date']),';">
		</div>';
		
		/* title */
		echo '<h4 id="m',$new['id'],'">',date ('d/m/Y à H\hi', $new['date']),'</h4>';
		
		if ($user_logged &&
		    //User::get_name ()   == 'Yno')
		    $user_level == 0)
		{
			echo '<div class="admin">
					[<a onclick="edit(', $new['id'], ', this)"
						title="éditer">éditer</a>]
					[<a href="post.php?sec=devel&amp;act=rm&amp;id=', $new['id'], '"
						onclick="return confirm(\'Voulez-vous vraiment supprimer ce post ?\')"
						title="Supprimer">X</a>]
				</div>';
		}
		
		echo '<p>', stripslashes ($new['content']), '</p>';
		
		if ($user_logged &&
			 //User::get_name ()   == 'Yno')
			 $user_level == 0)
		{
			echo '<div class="formedit" id="f', $new['id'], '" style="display:none;">
						<a href="javascript:entry_more(\'t', $new['id'], '\')">[+]</a>
						<a href="javascript:entry_lesser(\'t', $new['id'], '\')">[-]</a>
						<form method="post" action="post.php?sec=devel&amp;act=edit&amp;id=', $new['id'], '">
							<p>
								<input type="hidden" name="date" value="', $new['date'], '"/>
								<textarea name="content" cols="24" rows="8" id="t', $new['id'], '">', br2nl (stripslashes ($new['content'])), '</textarea>
								<input type="submit" value="Poster" />
								<input type="reset" value="Reset" />
							</p>
						</form>
					</div>';
		}
	} //endwhile
	
	echo '</div>';
	
	echo '<br /><div class="newslinks">';
	print_engine_page_browser ($start_news);
	echo '</div>';
	
	unset ($user_logged, $user_level, $news, $new);
}
