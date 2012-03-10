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

/* Flash messages handling */


abstract class Flash
{
	private static $messages = array ();
	
	/**
	 * \brief Add a flash message
	 * \param $type A lowercase string representing the type of the message.
	 *              Valid values are "info", "warning" and "error".
	 * \param $message The user-readable message to display
	 * 
	 * Logs a flash message that will be displayed to the user.
	 */
	public static function add ($type, $message)
	{
		self::$messages[] = array ('type' => $type, 'message' => $message);
	}
	
	/**
	 * \brief Gets all logged flash messages
	 * \returns An array of logged messages
	 * 
	 * Gets the an array of all messages logged until now.  Each message in the
	 * array is an array with two elements: 'type', the type of the message; and
	 * 'message', the body of the message.
	 */
	public static function get_messages ()
	{
		return self::$messages;
	}
}
