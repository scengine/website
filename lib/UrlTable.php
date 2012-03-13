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
	
	public static function get_script ($uri)
	{
		$name = basename ($uri);
		/* strip anchors */
		if (($pos = strpos ($name, '#')) !== false) {
			$name = substr ($name, 0, $pos);
		}
		
		if (! BSE_ENABLE_URL_REWRITING) {
			/* strip GET args */
			if (($pos = strpos ($name, '?')) !== false) {
				$name = substr ($name, 0, $pos);
			}
			return $name;
		} else {
			$map = array (
				'index'       => 'index.php',
				'news'        => 'news.php',
				'media'       => 'medias.php',
				'downloads'   => 'downloads.php',
				'tuto'        => 'tuto.php',
				'license'     => 'license.php',
				'about'       => 'about.php',
				'admin'       => 'admin.php',
				'login'       => 'connexion.php',
				'logout'      => 'connexion.php'
			);
			
			foreach ($map as $match => $page) {
				if (str_has_prefix ($name, $match)) {
					return $page;
				}
			}
			
			return $name; // and just hope
		}
	}
	
	public static function home ()
	{
		return self::basic_php_html ('index');
	}
	
	public static function medias ($id=false, $noreturn=false, $title=null)
	{
		$link;
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link = 'medias.php';
			$sep = '?';
			if ($id !== false)
			{
				$link .= $sep.'watch='.$id;
				$sep = '&amp;';
			}
			if ($noreturn)
				$link .= $sep.'noreturn=1';
		}
		else
		{
			$link = 'media';
			if ($id !== false)
				$link .= '-'.$id;
			else
				$link .= 's';
			if ($noreturn)
				$link .= '-1';
			if ($title)
				$link .= '-'.normalize_string_for_url ($title);
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	public static function medias_tags (array $type=null, array $tags=null)
	{
		$link;
		
		if ($type !== null)
			$type = implode (',', $type);
		if ($tags !== null)
			$tags = implode (',', $tags);
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link = 'medias.php';
			if ($type !== null)
			{
				$link .= '?type='.$type;
				if ($tags !== null)
					$link .= '&showtag='.$tags;
			}
		}
		else
		{
			$link = 'medias';
			if ($type !== null)
			{
				$link .= '-'.$type;
				if ($tags !== null)
					$link .= '-'.$tags;
			}
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	
	public static function downloads ()
	{
		return self::basic_php_html ('downloads');
	}
	
	public static function tuto ()
	{
		return self::basic_php_html ('tuto');
	}
	
	public static function license ()
	{
		return self::basic_php_html ('license');
	}
	
	public static function about ()
	{
		return self::basic_php_html ('about');
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
		$link;
		
		if (! BSE_ENABLE_URL_REWRITING)
		{
			$link = 'news.php';
			if ($id !== false)
				$link .= '?shownews='.$id;
		}
		else
		{
			if ($id !== false)
			{
				/* link of form news-NEWSID[-name-of-the-news].html */
				$link = 'news-'.$id;
				
				if ($title)
					$link .= '-'.normalize_string_for_url ($title);
			}
			else
				$link = 'news';
			$link .= '.html';
		}
		
		return BSE_BASE_PATH.$link;
	}
	public static function news_page ($page)
	{
		$link;
		
		if (! BSE_ENABLE_URL_REWRITING)
			$link = 'news.php?page='.$page;
		else
			$link = 'news-page'.$page.'.html';
		
		return BSE_BASE_PATH.$link;
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
