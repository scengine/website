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


class Singleton
{
	private static $instance;
	
	protected function __construct ()
	{
		if (self::$instance) {
			trigger_error (sprintf ('Cannot instantiate %s twice', __CLASS__), E_USER_ERROR);
		}
	}
	
	public static function get_instance ()
	{
		if (! isset (self::$instance)) {
			$class = get_called_class ();
			self::$instance = new $class ();
		}
		return self::$instance;
	}
	
	public function __clone ()
	{
		trigger_error (sprintf ('Cannot clone %s', __CLASS__), E_USER_ERROR);
	}
	
	public function __wakeup ()
	{
		trigger_error (sprintf ('Cannot unserialize %s', __CLASS__), E_USER_ERROR);
	}
}
