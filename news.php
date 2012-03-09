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

/*
 * index & news
 */

define ('TITLE', 'News');
define ('NEWS_PREVIEW_SIZE', 250);
define ('NEWS_BY_PAGE', 3);

require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/News.php');
require_once ('lib/Html.php');
require_once ('lib/MyDB.php');
require_once ('lib/BCode.php');

//$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';

/** prints a news
 * \param $new the array of the news
 * \param $more whether to trunctate content and show 'more...' if news content
 *        is longer than the configured size
 */
function print_new (array &$new, $more=false)
{
	$permalink = UrlTable::news ($new['id'], $new['title']);
	
	echo '
	<div class="news">';
	
	$content = $new['content'];
	if ($more) {
		$content = xmlstr_shortcut ($content, NEWS_PREVIEW_SIZE,
		                            '… <a href="'.$permalink.'" class="more">read more</a>');
	}
	
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		News::print_form ($new['title'], $new['source'], 'edit', $new['id'],
		                  null, '', 'style="display:none;"');
	}
	echo '
		<div>
			<div class="data" id="mn',$new['id'],'">
				<h3 id="n',$new['id'],'">
					<a href="',$permalink,'">',
						Html::escape ($new['title']),
					'</a>
				</h3>
				<div class="content">
					',$content,'
				</div>
				<div class="date">
					Posted by <span class="b">',$new['author'],'</span> on ',
					date ('Y-m-d \a\t H:i', $new['date']);
	if ($new['date'] < $new['mdate']) {
		echo ' &mdash; last updated by <span class="b">',$new['mauthor'],'</span> on ',
			date ('Y-m-d \a\t H:i', $new['mdate']);
	}
	echo '
				</div>
			</div>
			<div class="links">
				<ul>
					<li><a href="',$permalink,'">Permalink</a></li>';
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		echo '<li><a href="',UrlTable::admin_news ('edit', $new['id']),'" ',
			'onclick="return news_edit (\'n',$new['id'],'\', this);">Edit</a></li>';
		echo '<li><a href="',UrlTable::admin_news ('rm', $new['id']),'" ',
			'onclick="return news_delete (\'',$new['id'],'\');">Delete</a></li>';
	}
	echo '
				</ul>
			</div>
		</div>
	</div>';
}

function print_news ($start=0) {
	$news = News::get ($start, NEWS_BY_PAGE);
	
	// pour permetre aux admins d'ajouter une news
	if (User::has_rights (ADMIN_LEVEL_NEWS)) {
		echo
			'<div>',
			Html::button_full ('Add a news', UrlTable::admin_news ('new'), null,
		                     'return toggle_display (\'fld_nnew\', \'block\');'),
			'</div>';
	}
	
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


function print_page_browser ($current, $limit=2)
{
	$sep = ' | ';
	$n_news = News::get_n ();
	$n_pages = ceil ($n_news / NEWS_BY_PAGE);
	$has_prev = ($current > 0) ? true : false;
	$has_next = ($n_pages > $current + 1) ? true : false;
	
	if ($has_prev) {
		echo '<a href="',UrlTable::news_page ($current),'">newer</a>';
	} else {
		echo '<span class="disabled">newer</span>';
	}
	
	$was_ok = true;
	for ($i=0; $i < $n_pages; $i++)
	{
		if ($i < $limit ||
		    abs ($i - $current) <= $limit ||
		    $i + $limit >= $n_pages ||
		    /* only hide page if it would save > 1 link */
		    ($i < $current && $limit >= $current - 1 - $limit) ||
		    ($i > $current && $n_pages - 1 - $limit <= $current + 1 + $limit)) {
			if ($i == $current)
				echo $sep,'<span class="current">', $i + 1, '</span>';
			else
				echo $sep,'<a href="',UrlTable::news_page ($i + 1),'">',$i + 1,'</a>';
			$was_ok = true;
		} else if ($was_ok) {
			echo $sep,'...';
			$was_ok = false;
		}
	}
	
	if ($has_next) {
		echo $sep,'<a href="',UrlTable::news_page ($current + 1 + 1),'">older</a>';
	} else {
		echo $sep,'<span class="disabled">older</span>';
	}
}

function print_one_news ($id)
{
	$news = News::get_by_id ($id);
?>
	<div id="content" class="nopresentation">
		<?php
		
		if ($news)
			print_new ($news);
		else
		{
			echo '
			<h2>Erreur&nbsp;!</h2>
			<p>
                        The news you are looking for does not exist or has been removed.
			</p>
			<p><a href="',UrlTable::news (),'">Go back to the news index.</a></p>';
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
		<h2>News
			<span class="fright">
				<a href="<?php echo NEWS_ATOM_FEED_FILE; ?>" title="Subscribe to the Atom Feed">
					<img src="styles/<?php echo STYLE; ?>/feed.png" alt="Atom Feed" />
				</a>
			</span>
		</h2>
		<p>
             News index. Browse and read old stuff; you may rapidly find french posts.
		</p>
	</div>
	
	<div id="content"><!--
		<h2>Last news</h2>-->
		
		<?php
		
		print_news ($start_news * NEWS_BY_PAGE);
		
		/* buttons for other pages */
		echo '<div class="paging">';
		print_page_browser ($start_news, 2);
		echo '</div>';
		
		?>
	
	</div>
<?php
}

/***********/


require_once ('include/top.minc');

if (isset ($_GET['shownews']) && settype ($_GET['shownews'], 'int'))
{
	print_one_news ($_GET['shownews']);
}
else
{
	print_home ();
}

require_once ('include/bottom.minc');
