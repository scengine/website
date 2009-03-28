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


/*
session_start ();
*/

require_once ('include/defines.php');
require_once ('lib/MyDB.php');

define (COOKIE_EXPIRE, 0);

abstract class User
{
	private static $time;
	private static $logged = 0;
	private static $level = null;

	public static function login($time=COOKIE_EXPIRE) {
		self::$time = $time;
		self::$logged = false;
		
		if (isset($_POST['username'], $_POST['password']))
		{
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			
			$username = mysql_real_escape_string ($_POST['username'], $db->get_link ());
			
			$db->select_table (USERS_TABLE);
			$db->select ('*', '`username`=\''.$username.'\'');
			$response = $db->fetch_response();
			if ($response['password'] == md5($_POST['password']))
			{
				setcookie ('username', $response['username'], self::$time);
				setcookie ('password', $response['password'], self::$time);
				setcookie ('level', $response['level'], self::$time);
				setcookie ('logged', 1, self::$time);
				
				
				$db->update('`logged`=1', '`username`=\''.$username.'\'');
				
				self::$logged = true;
			}
			
			unset ($db);
		}
		
		return self::get_logged ();
	}

	public static function logout() {
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table(USERS_TABLE);
		$db->update('`logged`=0', '`username`=\''.$_SESSION['username'].'\'');
		unset ($db);
		
		setcookie ('username', false, time () - 3600);
		setcookie ('password', false, time () - 3600);
		setcookie ('level',    false, time () - 3600);
		setcookie ('logged',   false, time () - 3600);
		
		self::$logged = false;
		
		return true;
	}

	public static function get_name() {
		return $_COOKIE['username'];
	}

	public static function get_level() {
		/* search value only if not in cache */
		if (self::$level === null)
		{
			if (isset ($_COOKIE['username'], $_COOKIE['password']))
			{
				$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
				$db->select_table (USERS_TABLE);
				
				$username = mysql_real_escape_string (self::get_name (), $db->get_link ());
				$db->select ('`level`', '`username`=\''.$username.'\'');
				$response = $db->fetch_response ();
				self::$level = $response['level'];
			}
			else
			{
				/* return a big level for no rights */
				self::$level = 512;
			}
		}
		return self::$level;
	}

	public static function get_logged() {
		$return = false;
		
		/* if state is in cache */
		if (self::$logged === true ||
			 self::$logged === false)
			return self::$logged;
		
		if (isset ($_COOKIE['username'], $_COOKIE['password']))
		{
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			
			$db->select_table (USERS_TABLE);
			$db->select ('*', '`username`=\''.mysql_real_escape_string ($_COOKIE['username'], $db->get_link ()).'\'');
			$response = $db->fetch_response ();
			if ($response['password'] == $_COOKIE['password'])
				$return = true;
			
			unset ($db);
			
			self::$logged = $return;
			return $return;
		}
		
		return false;
	}

	public static function set_name($newname) {
		setcookie ('username', $newname, self::$time);
		
		return true;
	}

	public static function set_password($newpass) {
		setcookie ('password', $newpass, self::$time);
		
		return true;
	}

	public static function set_level($newlevel) {
		setcookie ('level', $newlevel, self::$time);
		
		return true;
	}
	
	public static function has_rights ($right_level)
	{
		return (self::get_logged () && self::get_level () <= $right_level) ? true : false;
	}
}
