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
require_once ('lib/Html.php');
require_once ('lib/News.php');
require_once ('lib/PHPTemplate.php');



/*
 * \brief small wrapper for locked file_put_contents()
 * \param $file the file where write the feed
 * \param $data content of the feed file
 */
function feed_update ($file, $data)
{
	return file_put_contents ($file, $data, LOCK_EX);
}

function feed_update_news ()
{
	$vars = array (
		'language'      => 'fr',
		'title'         => 'News du SCEngine',
		'description'   => 'Site officiel du SCEngine',
		'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
		'site_url'      => BSE_BASE_URL,
		'alternate_url' => BSE_SITE_URL.UrlTable::news (),
		'date'          => time (),
		'id'            => BSE_BASE_URL,
		'items'         => array ()
	);
	
	$all_news = News::get (0, 10);
	foreach ($all_news as &$news) {
		$alternate_url = UrlTable::news ($news['id']);
		
		$vars['items'][] = array (
			'lang'          => 'fr',
			'title'         => $news['title'],
			/* FIXME: the content is XHTML but it doesn't work with &nbsp;s...
			 * the use HTML, even if it is not good as XHTML */
			'content'       => Html::escape ($news['content']),
			'date'          => $news['mdate'],
			'alternate_url' => $alternate_url,
			'id'            => $alternate_url,
			'author'        => $news['author']
		);
	}
	
	$atom_data = new PHPFileTemplate ('views/feeds/news.atom.phtml', $vars);
	$atom_data->self_url = BSE_BASE_URL.NEWS_ATOM_FEED_FILE;
	
	$rss_data = new PHPFileTemplate ('views/feeds/news.rss.phtml', $vars);
	$rss_data->self_url = BSE_BASE_URL.NEWS_RSS_FEED_FILE;
	
	return feed_update (NEWS_ATOM_FEED_FILE, (string) $atom_data) &&
	       feed_update (NEWS_RSS_FEED_FILE, (string) $rss_data);
}
