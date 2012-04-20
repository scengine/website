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
require_once ('lib/string.php');

class Route
{
	public $controller = 'index';
	public $action = 'index';
	public $args = array ();
	
	private static function cut ($str)
	{
		return explode ('/', trim (str_replace ('//', '/', $str), '/'));
	}
	
	public function __construct ($query_or_string)
	{
		if (is_array ($query_or_string)) {
			if (array_key_exists ('controller', $query_or_string)) {
				$this->controller = $query_or_string['controller'];
			}
			if (array_key_exists ('action', $query_or_string)) {
				$this->action = $query_or_string['action'];
			}
			if (array_key_exists ('args', $query_or_string)) {
				if (is_array ($query_or_string['args'])) {
					$this->args = $query_or_string['args'];
				} else {
					$this->args = $this->cut ($query_or_string['args']);
				}
			}
		} else {
			$chunks = $this->cut ($query_or_string);
			if (isset ($chunks[0])) {
				$this->controller = str_replace (array (' ', '-'), '_', strtolower ($chunks[0]));
			}
			if (isset ($chunks[1])) {
				$this->action = strtolower ($chunks[1]);
			}
			if (isset ($chunks[2])) {
				$this->args = array_slice ($chunks, 2);
			}
		}
	}
	
	public function to_query ()
	{
		return array (
			'controller' => $this->controller,
			'action' => $this->action,
			'args' => $this->args
		);
	}
	
	public function to_url ($abs = false)
	{
		if ($this->action == 'index' && empty ($this->args)) {
			$query = array ('controller' => $this->controller);
		} else {
			$query = array (
				'controller' => $this->controller,
				'action' => $this->action,
				'args' => $this->args
			);
		}
		$url = BSE_ENABLE_URL_REWRITING ? '' : 'index.php?url=';
		$url .= implode_r ('/', $query);
		if ($abs) {
			return BSE_BASE_URL.$url;
		} else {
			return BSE_BASE_PATH.$url;
		}
	}
	
	public function __toString ()
	{
		return $this->to_url ();
	}
}
