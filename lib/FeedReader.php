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

require_once ('lib/FileCache.php');

/* feed cache settings */
define ('FEED_READER_CACHE_DIR', BSE_CACHE_DIR.'feeds/');
define ('FEED_READER_CACHE_TIME', 3600); /* cache time, in seconds */

define ('FEED_KEY_ID',        'id');
define ('FEED_KEY_PUBLISHED', 'published');
define ('FEED_KEY_UPDATED',   'updated');
define ('FEED_KEY_LINK',      'link');
define ('FEED_KEY_TITLE',     'title');
define ('FEED_KEY_CONTENT',   'content');
define ('FEED_KEY_AUTHOR',    'author');


abstract class FeedReader extends FileCache {
	public    $feed_url; /* string */
	protected $items = array (); /* array */
	
	public static function new_feed_item ()
	{
		return array (
			FEED_KEY_ID         => '',
			FEED_KEY_PUBLISHED  => '',
			FEED_KEY_UPDATED    => '',
			FEED_KEY_LINK       => '',
			FEED_KEY_TITLE      => '',
			FEED_KEY_CONTENT    => '',
			FEED_KEY_AUTHOR     => '',
		);
	}
	
	/* (string) -> bool */
	protected abstract function read_feed ($data);
	
	protected function fill ()
	{
		if (($data = parent::fill ()) !== false) {
			if ($this->read_feed ($data)) {
				return $this->items;
			}
		}
		return false;
	}
	
	protected function serialize (&$data)
	{
		return serialize ($data);
	}
	
	protected function unserialize (&$data)
	{
		$this->items = unserialize ($data);
		return $this->items;
	}
	
	public function __construct ($feed_url) {
		$this->feed_url = $feed_url;
		parent::__construct ($this->feed_url,
		                     FEED_READER_CACHE_DIR . urlencode ($feed_url),
		                     FEED_READER_CACHE_TIME);
		$this->load ();
	}
	
	public function get_items ($n = -1) {
		if ($n < 0) {
			return $this->items;
		} else {
			return array_slice ($this->items, 0, $n);
		}
	}
	
	public function get_item (string $id) {
		foreach ($this->items as &$item) {
			if ($item[FEED_KEY_ID] == $id) {
				return $item;
			}
		}
		return null;
	}
}
