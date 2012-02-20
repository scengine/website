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

require_once ('include/defines.php');
require_once ('lib/MyDB.php');


class CounterIP {
	/*
	 * Table shape:
	 *  - ip     VARCHAR(32) UNIQUE KEY
	 *  - count  INT(10) UNSIGNED DEFAULT=0
	 */
	
	protected $db;
	
	public function __construct ($count=false, $counter=COUNTER_TABLE) {
		$this->db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
		$this->db->select_table ($counter);
		
		if ($count) {
			$this->count ();
		}
	}
	
	public function __destruct () {
		unset ($this->db);
	}
	
	protected function increment_ip ($ip) {
		$this->db->insert (array ('ip' => $ip, 'count' => '1'), '`count`=`count`+1');
	}
	
	public function get_ip () {
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function get_n_for_ip ($ip) {
		if ($this->db->select (array ('count'), array ('ip' => $ip)) &&
		    ($response = $this->db->fetch_response ()) !== false) {
			return $response['count'];
		} else {
			return 0;
		}
	}
	
	public function get_n_ip () {
		return $this->db->count ();
	}
	
	public function get_n_total () {
		if ($this->db->select ('SUM(`count`) AS n') &&
		    ($response = $this->db->fetch_response ()) !== false) {
			return $response['n'];
		} else {
			return 0;
		}
	}
	
	public function count () {
		$this->increment_ip ($this->get_ip ());
	}
}
