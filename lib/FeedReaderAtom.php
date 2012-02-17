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

require_once ('lib/FeedReader.php');

class FeedReaderAtom extends FeedReader {
	private $item = null;
	private $bind = null; /* the name of the property to set on $item */
	private $in_entry = false;
	private $in_author = false;
	private $escape_content = false;
	
	private function item_escape_prop ($prop) {
		$this->item[$prop] = htmlspecialchars ($this->item[$prop]);
	}
	
	private function item_strtotime ($prop, $fallback_prop) {
		$val = 0;
		
		if (! empty ($this->item[$prop])) {
			$val = strtotime ($this->item[$prop]);
		} else if (! empty ($this->item[$fallback_prop])) {
			$val = strtotime ($this->item[$fallback_prop]);
		}
		
		$this->item[$prop] = $val;
	}
	
	private function element_start ($parser, $name, $attrs) {
		if ($this->in_entry) {
			if ($this->in_author) {
				switch ($name) {
					case 'name': $this->bind = FEED_KEY_AUTHOR; break;
				}
			} else {
				switch ($name) {
					case 'author': $this->in_author = true;
					
					case 'id':        $this->bind = FEED_KEY_ID; break;
					case 'published': $this->bind = FEED_KEY_PUBLISHED; break;
					case 'updated':   $this->bind = FEED_KEY_UPDATED; break;
					case 'title':     $this->bind = FEED_KEY_TITLE; break;
					case 'content':
						$this->bind = FEED_KEY_CONTENT;
						$this->escape_content = ($attrs['type'] != 'html');
						break;
					
					case 'link':
						$this->item[FEED_KEY_LINK] = $attrs['href']; break;
				}
			}
		} else {
			if ($name == 'entry') {
				$this->item = self::new_feed_item ();
				$this->in_entry = true;
			}
		}
	}
	
	private function element_end ($parser, $name) {
		$this->bind = null;
		if ($this->in_entry) {
			if ($this->in_author) {
				if ($name == 'author') {
					$this->in_author = false;
				}
			} else {
				if ($name == 'entry') {
					$this->item_escape_prop (FEED_KEY_TITLE);
					$this->item_escape_prop (FEED_KEY_AUTHOR);
					if ($this->escape_content) {
						$this->item_escape_prop (FEED_KEY_CONTENT);
					}
					$this->item_strtotime (FEED_KEY_PUBLISHED, FEED_KEY_UPDATED);
					$this->item_strtotime (FEED_KEY_UPDATED, FEED_KEY_PUBLISHED);
					
					$this->items[] = $this->item;
					$this->item = null;
					$this->in_entry = false;
				}
			}
		}
	}
	
	private function element_content ($parser, $data) {
		if ($this->bind) {
			$this->item[$this->bind] .= $data;
		}
	}
	
	protected function read_feed ($data) {
		$success = true;
		
		$parser = xml_parser_create ();
		xml_set_object ($parser, $this);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler ($parser, 'element_start', 'element_end');
		xml_set_character_data_handler ($parser, 'element_content');
		if (! xml_parse ($parser, $data, true)) {
			$success = false;
		}
		xml_parser_free ($parser);
		
		return $success;
	}
}


/*
$feed = 'http://gitorious.org/scengine/core/commits/master/feed.atom';

$r = new FeedReaderAtom ($feed);
foreach ($r->get_items () as $item) {
	echo 'Title: ', $item[FEED_KEY_TITLE], "\n";
	echo 'Content: ', $item[FEED_KEY_CONTENT], "\n";
}
//*/


