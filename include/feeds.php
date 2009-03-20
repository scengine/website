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

require_once ('include/defines.php');
require_once ('include/MyDB.php');


/** Feed templates for devel news **/
/* Atom */
define (ATOM_FEED_NEWS_TPL,
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
define (ATOM_FEED_NEWS_ITEM_TPL,
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
define (RSS_FEED_NEWS_TPL,
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
define (RSS_FEED_NEWS_ITEM_TPL,
'		<item>
			<title>{title}</title>
			<link>{alternate_url}</link>
			<description>
				{content}
			</description>
			<pubDate>{date}</pubDate>
			<guid isPermaLink="false">{id}</guid>
			<!--author>{author}</author-->
		</item>
');

/** Feed templates for devel news **/
/* Atom */
define (ATOM_FEED_DEVEL_TPL,
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
define (ATOM_FEED_DEVEL_ITEM_TPL,
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
define (RSS_FEED_DEVEL_TPL,
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
define (RSS_FEED_DEVEL_ITEM_TPL,
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


function tpl_process (array &$arr)
{
	$out = '';
	
	foreach ($arr as &$subarr)
	{
		$tpl = &$subarr['tpl'];
		$search_replace = &$subarr['replace'];
		
		foreach ($search_replace as &$replace)
		{
			if (is_array ($replace))
				$replace = tpl_process ($replace);
		}
		
		$searches = array_keys ($search_replace);
		foreach ($searches as &$search)
		{
			$search = '{'.$search.'}';
		}
		$out .= str_replace ($searches, $search_replace, $tpl);
	}
	
	return $out;
}


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
	$atom_data = array ();
	$atom_items = array ();
	$rss_data = array ();
	$rss_items = array ();
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (NEWS_TABLE);
	$db->select ('*', '', '`id`', 'DESC', 0, 10);
	while (($news = $db->fetch_response ()) !== false)
	{
		$atom_items[] = array (
			'tpl'     => ATOM_FEED_NEWS_ITEM_TPL,
			'replace' => array (
				'lang'          => 'fr',
				'title'         => stripslashes ($news['titre']),
				/* FIXME: the content is XHTML but it doesn't work with &nbsp;s...
				 * the use HTML, even if it is not good as XHTML */
				'content'       => htmlspecialchars ($news['contenu'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('c', $news['mdate']),
				'alternate_url' => BSE_BASE_URL.'index.php#n'.$news['id'],
				'id'            => BSE_BASE_URL.'index.php#n'.$news['id'],
				'author'        => $news['auteur']
			)
		);
		$rss_items[] = array (
			'tpl'     => RSS_FEED_NEWS_ITEM_TPL,
			'replace' => array (
				'title'         => stripslashes ($news['titre']),
				'content'       => htmlspecialchars ($news['contenu'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('r', $news['mdate']),
				'alternate_url' => BSE_BASE_URL.'index.php#n'.$news['id'],
				'id'            => BSE_BASE_URL.'index.php#n'.$news['id'],
				'author'        => $news['auteur']
			)
		);
	}
	unset ($db);
	
	$atom_data[] = array (
		'tpl'     => ATOM_FEED_NEWS_TPL,
		'replace' => array (
			'title'         => 'News du SCEngine',
			'icon'          => BSE_BASE_URL.'styles/default/icon.png',
			'self_url'      => BSE_BASE_URL.NEWS_ATOM_FEED_FILE,
			'alternate_url' => BSE_BASE_URL.'index.php',
			'date'          => date ('c'),
			'id'            => BSE_BASE_URL,
			'items'         => &$atom_items
		)
	);
	$rss_data[] = array (
		'tpl'     => RSS_FEED_NEWS_TPL,
		'replace' => array (
			'title'         => 'News du SCEngine',
			'description'   => 'Site officiel du SCEngine',
			'self_url'      => BSE_BASE_URL.NEWS_RSS_FEED_FILE,
			'site_url'      => BSE_BASE_URL,
			'language'      => 'fr',
			'date'          => date ('r'),
			'icon'          => BSE_BASE_URL.'styles/default/icon.png',
			'items'         => &$rss_items
		)
	);
	
	return feed_update (NEWS_ATOM_FEED_FILE, tpl_process ($atom_data)) &&
	       feed_update (NEWS_RSS_FEED_FILE, tpl_process ($rss_data));
}

function feed_update_devel ()
{
	$atom_data = array ();
	$atom_items = array ();
	$rss_data = array ();
	$rss_items = array ();
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (DEVEL_TABLE);
	$db->select ('*', '', '`id`', 'DESC', 0, 16);
	while (($news = $db->fetch_response ()) !== false)
	{
		$atom_items[] = array (
			'tpl'     => ATOM_FEED_DEVEL_ITEM_TPL,
			'replace' => array (
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
		$rss_items[] = array (
			'tpl'     => RSS_FEED_DEVEL_ITEM_TPL,
			'replace' => array (
				'title'         => date ('d/m/Y à H\hi', $news['date']),
				'content'       => htmlspecialchars ($news['content'], ENT_COMPAT, 'UTF-8'),
				'date'          => date ('r', $news['date']),
				'alternate_url' => BSE_BASE_URL.'index.php#m'.$news['id'],
				'id'            => BSE_BASE_URL.'index.php#m'.$news['id']
			)
		);
	}
	unset ($db);
	
	$atom_data[] = array (
		'tpl'     => ATOM_FEED_DEVEL_TPL,
		'replace' => array (
			'title'         => 'News du développement du SCEngine',
			'icon'          => BSE_BASE_URL.'styles/default/icon.png',
			'self_url'      => BSE_BASE_URL.DEVEL_ATOM_FEED_FILE,
			'alternate_url' => BSE_BASE_URL.'index.php',
			'date'          => date ('c'),
			'id'            => BSE_BASE_URL,
			'items'         => &$atom_items,
			'author'        => 'Yno'
		)
	);
	$rss_data[] = array (
		'tpl'     => RSS_FEED_DEVEL_TPL,
		'replace' => array (
			'title'         => 'News du développement du SCEngine',
			'description'   => 'Site officiel du SCEngine',
			'self_url'      => BSE_BASE_URL.DEVEL_RSS_FEED_FILE,
			'site_url'      => BSE_BASE_URL,
			'language'      => 'fr',
			'date'          => date ('r'),
			'icon'          => BSE_BASE_URL.'styles/default/icon.png',
			'items'         => &$rss_items
		)
	);
	
	return feed_update (DEVEL_ATOM_FEED_FILE, tpl_process ($atom_data)) &&
	       feed_update (DEVEL_RSS_FEED_FILE, tpl_process ($rss_data));
}


/*
$err = feed_update_news ();
if (! $err)
	echo "$err\n";
else
	echo "Réussi\n";
*/
/*
$replace_ = array (
	'{content}' => 'relol',
	'{title}' => 'lol',
	'{date}' => '12463542112',
	'{link}' => 'http://patate.com',
	'{id}' => 'rda:uuid:1001:a144:45ab:b11f',
	'{author}' => 'moi'
);

$all = array (
	array (
		'tpl' => ATOM_FEED_TPL,
		'replace' => array (
			'{title}' => 'News du SCEngine',
			'{icon}' => BSE_BASE_URL.'styles/default/icon.png',
			'{self_url}' => BSE_BASE_URL.NEWS_FEED_FILE,
			'{alternate_url}' => BSE_BASE_URL,
			'{date}' => date ('c'),
			'{id}' => 'rda:uuid:1001:a144:45cc:b11f',
			'{items}' => array (
				array (
					'tpl' => ATOM_FEED_ITEM_TPL,
					'replace' => array (
						'{content}' => 'relol',
						'{title}' => 'lol',
						'{date}' => '12463542112',
						'{link}' => 'http://patate.com',
						'{id}' => 'rda:uuid:1001:a144:45ab:b11f',
						'{author}' => 'moi'
					)
				)
			)
		)
	)
);


echo tpl_process ($all);
*/
