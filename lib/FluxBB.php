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

/* Interacts with FluxBB */

require_once ('include/defines.php');

abstract class FluxBB {
	/*
	 * \param $query The query from which fetch the data
	 * \returns The result of the query or FALSE on failure.
	 */
	protected static function _fluxbb_extern_query ($query) {
		$data = @file_get_contents (BSE_BASE_FLUXBB_URL.'extern.php?'.$query);
		/* Hum, what is the encoding FluxBB returns for external requests? */
		return $data !== false ? utf8_encode ($data)/*$data*/ : false;
	}
	
	/*
	 * \returns Some HTML list items of the results or FALSE on failure. As the
	 *          result is a block of list items, you should surround it by a list
	 *          tag (ul, ol, etc.).
	 */
	protected static function _get_fluxbb_list ($action, $n=15, $fid='') {
		settype ($n, 'int') or die ('Invalid type for argument 2 of '.__FUNCTION__);
		$query = 'action='.$action.'&show='.$n;
		if ($fid) $query .= '&fid='.$fid;
		$data = self::_fluxbb_extern_query ($query);
		if ($data) {
			if (! $data)
				$data = '<li>Aucun post</li>';
		}
		return $data;
	}
	
	protected static function _get_fluxbb_feed ($action, $fid='') {
		$feed_url = BSE_BASE_FLUXBB_URL.'extern.php?action='.$action;
		if ($fid) $feed_url .= '&fid='.$fid;
		return $feed_url;
	}
	
	public static function get_recent_list ($n, $fid='') {
		return self::_get_fluxbb_list ('active', $n, $fid);
	}
	
	public static function get_recent_feed ($fid='') {
		return self::_get_fluxbb_feed ('active', $fid);
	}
	
	public static function get_newest_list ($n, $fid='') {
		return self::_get_fluxbb_list ('new', $n, $fid);
	}
	
	public static function get_newest_feed ($fid='') {
		return self::_get_fluxbb_feed ('new', $fid);
	}
	
	public static function get_online_users_infos () {
		return self::_fluxbb_extern_query ('action=online');
	}
	
	public static function get_online_users_full_infos () {
		return self::_fluxbb_extern_query ('action=online_full');
	}
}

