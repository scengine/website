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

/* provides functions to get URL to pages */

require_once ('include/defines.php');
require_once ('lib/Route.php');
require_once ('lib/string.php');


abstract class UrlTable
{
	protected static function basic_php_html ($name)
	{
		$link = $name;
		
		if (! BSE_ENABLE_URL_REWRITING)
			$link .= '.php';
		else
			$link .= '.html';
		
		return BSE_BASE_PATH.$link;
	}
	
	protected static function controller ($controller, $action = 'index', array $args = array ())
	{
		return (string) new Route (array (
			'controller' => $controller,
			'action' => $action,
			'args' => $args
		));
	}
	
	public static function home ()
	{
		return self::controller ('index');
	}
	
	public static function medias ($id=false, $noreturn=false, $title=null)
	{
		$action = 'index';
		$args = array ();
		
		if ($id !== false) {
			$action = 'view';
			$args[] = $id;
			if ($title) {
				$args[] = normalize_string_for_url ($title);
			}
		}
		
		return self::controller ('medias', $action, $args);
	}
	public static function medias_tags (array $type=null, array $tags=null)
	{
		$args = array ();
		
		if ($type !== null) {
			$args[] = implode (',', $type);
			if ($tags !== null) {
				$args[] = implode (',', $tags);
			}
		}
		
		return self::controller ('medias', 'index', $args);
	}
	
	public static function downloads ()
	{
		return self::controller ('downloads');
	}
	
	public static function license ()
	{
		return self::controller ('license');
	}
	
	public static function about ()
	{
		return self::controller ('about');
	}
	
	public static function admin ($page=null)
	{
		$link = 'admin';
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link .= '.php';
			if ($page !== null)
				$link .= '?page='.$page;
		}
		else
		{
			if ($page !== null)
				$link .= '-'.$page;
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	protected static function generic_admin_action_id ($module, $action, $id)
	{
		$link = 'admin';
		$module = $module;
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link .= '.php?page='.$module;
			if ($action !== null)
			{
				$link .= '&amp;action='.$action;
				if ($id !== false)
					$link .= '&amp;id='.$id;
			}
		}
		else
		{
			$link .= '-'.$module;
			if ($action !== null)
			{
				$link .= '-'.$action;
				if ($id !== false)
					$link .= '-'.$id;
			}
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	public static function admin_news ($action=null, $id=false)
	{
		return self::generic_admin_action_id ('actualités', $action, $id);
	}
	public static function admin_medias ($action=null, $id=false)
	{
		return self::generic_admin_action_id ('medias', $action, $id);
	}
	public static function admin_admins ($action=null, $pseudo=null)
	{
		$link = 'admin';
		$module = 'administrateurs';
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link .= '.php?page='.$module;
			if ($action !== null)
			{
				$link .= '&amp;action='.$action;
				if ($pseudo !== null)
					$link .= '&amp;pseudo='.$pseudo;
			}
		}
		else
		{
			$link .= '-'.$module;
			if ($action !== null)
			{
				$link .= '-'.$action;
				if ($pseudo !== null)
					$link .= '-'.$pseudo;
			}
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	
	public static function news ($id=false, $title=null)
	{
		$action = 'index';
		$args = array ();
		
		if ($id !== false) {
			$action = 'view';
			$args[] = $id;
			if ($title) {
				$args[] = normalize_string_for_url ($title);
			}
		}
		
		return self::controller ('news', $action, $args);
	}
	public static function news_page ($page)
	{
		return self::controller ('news', 'index', array ($page));
	}
	
	public static function login ()
	{
		if (! BSE_ENABLE_URL_REWRITING)
			return BSE_BASE_PATH.'connexion.php';
		else
			return BSE_BASE_PATH.'login.html';
	}
	
	public static function logout ()
	{
		if (! BSE_ENABLE_URL_REWRITING)
			return BSE_BASE_PATH.'connexion.php?act=logout';
		else
			return BSE_BASE_PATH.'logout.html';
	}
	
	public static function feed ($feed)
	{
		if (! BSE_ENABLE_URL_REWRITING && ($feed == 'commits.atom' ||
		                                   $feed == 'commits.rss')) {
			return BSE_BASE_PATH.'commits-feed.php?format=' . filename_getext ($feed);
		}
		
		return BSE_BASE_PATH.'feeds/' . $feed;
	}
}
