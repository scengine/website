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

/* HTML helpers */

require_once ('lib/Route.php');

abstract class Html
{
	/* wraps htmlspecialchars() to use UTF-8 */
	public static function escape ($str)
	{
		return htmlspecialchars ($str, ENT_COMPAT, 'UTF-8');
	}
	
	/* prints a portable link button */
	public static function button_full ($label, $url, $title = null, $js = null, $accesskey = null)
	{
		/* the double click handler is to fix a Konqueror bug/problem:
		 * it submits on click unless JS returns false; but even if there's an onclick
		 * handler and no ondblclick one, it use return value of ondblclick on double
		 * click.
		 */
		return '
		<a href="'.self::escape ($url).'"
			'.(($title != null) ? 'title="'.self::escape ($title).'"' : '').'
			'.(($accesskey != null) ? 'accesskey="'.self::escape ($accesskey).'"' : '').'
			'.(($js != null) ? 'onclick="'.self::escape ($js).'; return false;" ondblclick="return false;"' : '').'
			><button type="button">'.$label.'</button></a>';
	}
	
	public static function button ($label, $url, $title = null)
	{
		return self::button_full ($label, $url, $title, 'window.location.replace (this.href); return false;');
	}
	
	public static function backbutton ($label, $url, $title=null, $back=1)
	{
		return self::button_full ($label, $url, $title, 'window.history.back ('.$back.'); return false;');
	}
	
	public static function button_js ($label, $js, $title=null, $accesskey=null)
	{
		return self::button_full ($label, '#', $title, $js, $accesskey);
	}
	
	public static function tag ($name, $data = null, $attrs = array ())
	{
		$tag = '<'.$name;
		
		foreach ($attrs as $attr => $value) {
			$tag .= ' '.$attr.'="'.self::escape ($value).'"';
		}
		if ($data !== null) {
			$tag .= '>'.$data.'</'.$name.'>';
		} else {
			$tag .= '/>';
		}
		
		return $tag;
	}
	
	public static function url ($url, $abs = false)
	{
		if (is_array ($url)) {
			$route = new Route ($url);
			$url = $route->to_url ($abs);
		}
		return $url;
	}
	
	public static function link ($title, $url = array ())
	{
		return self::tag ('a', self::escape ($title), array ('href' => self::url ($url)));
	}
}
