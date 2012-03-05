<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2012 Colomban Wendling <ban@herbesfolles.org>
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
require_once ('lib/Cache.php');
require_once ('lib/FeedReader.php');
require_once ('lib/FeedReaderAtom.php');
require_once ('lib/PHPTemplate.php');


define ('N_COMMITS', 20);
define ('CACHE_TIME', 3600);


abstract class CommitsCache extends Cache
{
	protected $view;
	
	protected abstract function get_item_tpl_vars (array &$item);
	protected abstract function get_feed_tpl_vars (array &$items);
	
	protected function fill ()
	{
		$feed_items = array ();
		$items = array ();
		$feeds = array (
			'http://git.tuxfamily.org/scengine/core.git?a=atom',
			'http://git.tuxfamily.org/scengine/utils.git?a=atom',
			'http://git.tuxfamily.org/scengine/interface.git?a=atom',
			'http://git.tuxfamily.org/scengine/renderergl.git?a=atom'
		);
		
		/* read upstream feeds */
		foreach ($feeds as $feed) {
			$reader = new FeedReaderAtom ($feed);
			$items = array_merge ($items, $reader->get_items ());
		}
		
		/* merge items... */
		usort ($items, function ($a, $b) { return $b[FEED_KEY_UPDATED] - $a[FEED_KEY_UPDATED]; });
		/* ...and keep only the N first elements */
		array_splice ($items, N_COMMITS);
		
		foreach ($items as &$item) {
			$feed_items[] = $this->get_item_tpl_vars ($item);
		}
		
		$tpl = new PHPFileTemplate ($this->view, $this->get_feed_tpl_vars ($feed_items));
		
		return (string) $tpl;
	}
}

/* Atom */
class AtomCommitsCache extends CommitsCache
{
	protected $view = 'views/feeds/commits.atom.phtml';
	
	protected function get_item_tpl_vars (array &$item)
	{
		return array (
			'lang'          => 'en',
			'title'         => $item[FEED_KEY_TITLE],
			'content'       => $item[FEED_KEY_CONTENT],
			'date'          => date ('c', $item[FEED_KEY_UPDATED]),
			'alternate_url' => $item[FEED_KEY_LINK],
			'id'            => $item[FEED_KEY_ID],
			'author'        => $item[FEED_KEY_AUTHOR]
		);
	}
	
	protected function get_feed_tpl_vars (array &$items)
	{
		return array (
			'title'         => 'SCEngine Commits',
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'self_url'      => BSE_BASE_URL.'commits-feed.php',
			'alternate_url' => BSE_BASE_URL.'index.php',
			'date'          => date ('c'),
			'id'            => BSE_BASE_URL.'commits',
			'entries'       => &$items
		);
	}
}

/* RSS */
class RSSCommitsCache extends CommitsCache
{
	protected $view = 'views/feeds/commits.rss.phtml';
	
	protected function get_item_tpl_vars (array &$item)
	{
		return array (
			'title'         => $item[FEED_KEY_TITLE],
			'content'       => $item[FEED_KEY_CONTENT],
			'date'          => date ('r', $item[FEED_KEY_UPDATED]),
			'alternate_url' => $item[FEED_KEY_LINK],
			'id'            => $item[FEED_KEY_ID],
			'author'        => $item[FEED_KEY_AUTHOR]
		);
	}
	
	protected function get_feed_tpl_vars (array &$items)
	{
		return array (
			'title'         => 'SCEngine Commits',
			'description'   => 'SCEngine Commits From All Repositories',
			'self_url'      => BSE_BASE_URL.'commits-feed.php?format=rss',
			'site_url'      => BSE_BASE_URL,
			'language'      => 'en',
			'date'          => date ('r'),
			'icon'          => BSE_BASE_URL.'styles/'.STYLE.'/icon.png',
			'entries'       => &$items
		);
	}
}


if (isset ($_GET['format']) && $_GET['format'] == 'rss') {
	$cache = new RSSCommitsCache ('feeds/commits.rss', CACHE_TIME);
} else {
	$cache = new AtomCommitsCache ('feeds/commits.atom', CACHE_TIME);
}

echo $cache->load ();
