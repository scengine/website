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
require_once ('lib/News.php');
require_once ('lib/MyDB.php');
require_once ('lib/Template.php');


/** Feed templates for devel news **/
/* Atom */
define ('ATOM_FEED_NEWS_TPL',
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<generator>BSE</generator>
	<icon>{icon}</icon>
	<title>{title}</title>
	<link rel="self" href="{self_url}" />
	<link rel="alternate" href="{alternate_url}" />
	<updated>{date}</updated>
	<id>{id}</id>
	
{items}
</feed>
');
define ('ATOM_FEED_NEWS_ITEM_TPL',
'	<entry>
		<title xml:lang="{lang}">{title}</title>
		<content type="html" xml:lang="{lang}">
			{content}
		</content>
		<updated>{date}</updated>
		<link rel="alternate" href="{alternate_url}" />
		<id>{id}</id>
		<author>
			<name>{author}</name>
		</author>
	</entry>
');
/* RSS */
define ('RSS_FEED_NEWS_TPL',
'<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<generator>BSE</generator>
		<title>{title}</title>
		<description>{description}</description>
		<atom:link rel="self" type="application/rss+xml" href="{self_url}" />
		<link>{site_url}</link>
		<language>{language}</language>
		<pubDate>{date}</pubDate>
		<lastBuildDate>{date}</lastBuildDate>
		<image>
			<title>{title}</title>
			<url>{icon}</url>
			<link>{site_url}</link>
		</image>
		
{items}
	</channel>
</rss>
');
define ('RSS_FEED_NEWS_ITEM_TPL',
'		<item>
			<title>{title}</title>
			<link>{alternate_url}</link>
			<description>
				{content}
			</description>
			<pubDate>{date}</pubDate>
			<guid isPermaLink="false">{id}</guid>
		</item>
');

/** Feed templates for devel news **/
/* Atom */
define ('ATOM_FEED_DEVEL_TPL',
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<generator>BSE</generator>
	<icon>{icon}</icon>
	<title>{title}</title>
	<link rel="self" href="{self_url}" />
	<link rel="alternate" href="{alternate_url}" />
	<updated>{date}</updated>
	<id>{id}</id>
	<author>
		<name>{author}</name>
	</author>
	
{items}
</feed>
');
define ('ATOM_FEED_DEVEL_ITEM_TPL',
'	<entry>
		<title xml:lang="{lang}">{title}</title>
		<content type="html" xml:lang="{lang}">
			{content}
		</content>
		<updated>{date}</updated>
		<link rel="alternate" href="{alternate_url}" />
		<id>{id}</id>
	</entry>
');
/* RSS */
define ('RSS_FEED_DEVEL_TPL',
'<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<generator>BSE</generator>
		<title>{title}</title>
		<description>{description}</description>
		<atom:link rel="self" type="application/rss+xml" href="{self_url}" />
		<link>{site_url}</link>
		<language>{language}</language>
		<pubDate>{date}</pubDate>
		<lastBuildDate>{date}</lastBuildDate>
		<image>
			<title>{title}</title>
			<url>{icon}</url>
			<link>{site_url}</link>
		</image>
		
{items}
	</channel>
</rss>
');
define ('RSS_FEED_DEVEL_ITEM_TPL',
'		<item>
			<title>{title}</title>
			<link>{alternate_url}</link>
			<description>
				{content}
			</description>
			<pubDate>{date}</pubDate>
			<guid isPermaLink="false">{id}</guid>
		</item>
');



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
	$atom_items = array ();
	$rss_items = array ();
	
	/*
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	$db->select ('*', '', '`id`', 'DESC', 0, 10);
	while (($news = $db->fetch_response ()) !== false)
	*/
	$all_news = News::get (0, 10);
	foreach ($all_news as &$news) {
		$alternate_url = BSE_BASE_URL.UrlTable::news ($news['id']);
		$id = /*sha1 (*/$alternate_url/*)*/;
		
		$atom_items[] = new StringTemplate (
			ATOM_FEED_NEWS_ITEM_TPL,
			array (
				'lang'          => 'fr',
				'title'         => $news['title'],
				/* FIXME: the content is XHTML but it doesn't work with &nbsp;s...
				 * the use HTML, even if it is not good as XHTML */
				'content'       => htmlspecialchars ($news['content'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('c', $news['mdate']),
				'alternate_url' => $alternate_url,
				'id'            => $id,
				'author'        => $news['author']
			)
		);
		$rss_items[] = new StringTemplate (
			RSS_FEED_NEWS_ITEM_TPL,
			array (
				'title'         => $news['title'],
				'content'       => htmlspecialchars ($news['content'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('r', $news['mdate']),
				'alternate_url' => $alternate_url,
				'id'            => $id,
				'author'        => $news['author']
			)
		);
	}
	unset ($db);
	
	$atom_data = new StringTemplate (
		ATOM_FEED_NEWS_TPL,
		array (
			'title'         => 'News du SCEngine',
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'self_url'      => BSE_BASE_URL.NEWS_ATOM_FEED_FILE,
			'alternate_url' => BSE_BASE_URL.'index.php',
			'date'          => date ('c'),
			'id'            => BSE_BASE_URL,
			'items'         => &$atom_items
		)
	);
	$rss_data = new StringTemplate (
		RSS_FEED_NEWS_TPL,
		array (
			'title'         => 'News du SCEngine',
			'description'   => 'Site officiel du SCEngine',
			'self_url'      => BSE_BASE_URL.NEWS_RSS_FEED_FILE,
			'site_url'      => BSE_BASE_URL,
			'language'      => 'fr',
			'date'          => date ('r'),
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'items'         => &$rss_items
		)
	);
	
	return feed_update (NEWS_ATOM_FEED_FILE, (string) $atom_data) &&
	       feed_update (NEWS_RSS_FEED_FILE, (string) $rss_data);
}

function feed_update_devel ()
{
	$atom_items = array ();
	$rss_items = array ();
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (DEVEL_TABLE);
	$db->select ('*', '', '`id`', 'DESC', 0, 16);
	while (($news = $db->fetch_response ()) !== false) {
		$atom_items[] = new StringTemplate (
			ATOM_FEED_DEVEL_ITEM_TPL,
			array (
				'lang'          => 'fr',
				'title'         => date ('d/m/Y à H\hi', $news['date']),
				/* FIXME: the content is XHTML but it doesn't work with &nbsp;s...
				 * the use HTML, even if it is not good as XHTML */
				'content'       => htmlspecialchars ($news['content'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('c', $news['date']),
				'alternate_url' => BSE_BASE_URL.'index.php#m'.$news['id'],
				'id'            => BSE_BASE_URL.'index.php#m'.$news['id']
			)
		);
		$rss_items[] = new StringTemplate (
			RSS_FEED_DEVEL_ITEM_TPL,
			array (
				'title'         => date ('d/m/Y à H\hi', $news['date']),
				'content'       => htmlspecialchars ($news['content'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('r', $news['date']),
				'alternate_url' => BSE_BASE_URL.'index.php#m'.$news['id'],
				'id'            => BSE_BASE_URL.'index.php#m'.$news['id']
			)
		);
	}
	unset ($db);
	
	$atom_data = new StringTemplate (
		ATOM_FEED_DEVEL_TPL,
		array (
			'title'         => 'News du développement du SCEngine',
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'self_url'      => BSE_BASE_URL.DEVEL_ATOM_FEED_FILE,
			'alternate_url' => BSE_BASE_URL.'index.php',
			'date'          => date ('c'),
			'id'            => BSE_BASE_URL,
			'items'         => &$atom_items,
			'author'        => 'Yno'
		)
	);
	$rss_data = new StringTemplate (
		RSS_FEED_DEVEL_TPL,
		array (
			'title'         => 'News du développement du SCEngine',
			'description'   => 'Site officiel du SCEngine',
			'self_url'      => BSE_BASE_URL.DEVEL_RSS_FEED_FILE,
			'site_url'      => BSE_BASE_URL,
			'language'      => 'fr',
			'date'          => date ('r'),
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'items'         => &$rss_items
		)
	);
	
	return feed_update (DEVEL_ATOM_FEED_FILE, (string) $atom_data) &&
	       feed_update (DEVEL_RSS_FEED_FILE, (string) $rss_data);
}
