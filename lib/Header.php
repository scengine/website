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


abstract class Header {
	private static function sign () {
		//echo '<address>', $_SERVER['SERVER_SOFTWARE'], ' Server at ', $_SERVER['SERVER_NAME'], ' Port ', $_SERVER['SERVER_PORT'], '</address>';
	}

	public static function h404 ($file='') {
		if (!$file)
			$file = $_SERVER['PHP_SELF'];
		
		
		header ('HTTP/1.0 404 Not Found');
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
				<html>
					<head>
						<title>404 Not Found</title>
					</head>
					<body>
						<h1>Not Found</h1>
						<p>The requested URL ', $file, ' was not found on this server.</p>
						<hr>';
		self::sign ();
		echo '</body>
				</html>';
		exit (1);
	}

	public static function h403 ($file='') {
		if (!$file)
			$file = $_SERVER['PHP_SELF'];
		
		header ('HTTP/1.0 403 Forbidden');
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
				<html>
					<head>
						<title>403 Forbidden</title>
					</head>
					<body>
						<h1>Forbidden</h1>
						<p>You don\'t have permission to access ', $file, ' on this server.</p>
						<hr>';
		self::sign ();
		echo '</body>
				</html>';
		exit (1);
	}
}

