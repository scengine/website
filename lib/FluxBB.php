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

/* Interacts with FluxBB 1.4 */

require_once ('include/defines.php');
require_once ('lib/FileCache.php');


abstract class FluxBB
{
	protected static function _url ($query)
	{
		$args = '';
		foreach ($query as $k => $v) {
			if ($args) {
				$args .= '&';
			}
			$args .= urlencode ($k).'='.urlencode ($v);
		}
		
		return BSE_BASE_FLUXBB_URL.'extern.php?'.$args;
	}
	
	/*
	 * \param $query The query from which fetch the data
	 * \returns The result of the query or FALSE on failure.
	 */
	protected static function _query (array $query, $cache_time = 300 /* 5min cache */)
	{
		$query_url = self::_url ($query);
		$cache = new FileCache ($query_url, BSE_CACHE_DIR.urlencode ($query_url), $cache_time);
		
		return $cache->load ();
	}
	
	public static function get_recent_list (array $options = array ())
	{
		return self::_query (
			array_merge (
				array (
					'type' => 'html',
					'action' => 'feed',
					'order' => 'last_post'
				),
				$options
			)
		);
	}
	
	public static function get_recent_feed (array $options = array ())
	{
		return self::_url (
			array_merge (
				array (
					'type' => 'atom',
					'action' => 'feed',
					'order' => 'last_post'
				),
				$options
			)
		);
	}
	
	public static function get_newest_list (array $options = array ())
	{
		return self::_query (
			array_merge (
				array (
					'type' => 'html',
					'action' => 'feed',
					'order' => 'posted'
				),
				$options
			)
		);
	}
	
	public static function get_newest_feed (array $options = array ())
	{
		return self::_url (
			array_merge (
				array (
					'type' => 'atom',
					'action' => 'feed',
					'order' => 'posted'
				),
				$options
			)
		);
	}
	
	public static function get_online_users_infos ()
	{
		return self::_query (array ('action' => 'online'), 180 /* 3min cache */);
	}
	
	public static function get_online_users_full_infos ()
	{
		return self::_query (array ('action' => 'online_full'), 180 /* 3min cache */);
	}
}

