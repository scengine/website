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
 * Compteur::
 */

require_once ('include/defines.php');
require_once ('lib/MyDB.php');

/*
class CounterIP_ {
	protected $file = COUNTER_FILE;
	protected $ips = array();
	
	public function __construct ($count=false, $counter=COUNTER_FILE) {
		$this->file = $counter;
		
		$this->data_load ();
		// echo 'I know ',$this->get_n_ip (), ' IPs.',"\n";
		// echo 'Total count is ',$this->get_n_total (), '.',"\n";
		// 
		// $ip = $this->get_ip ();
		// echo 'I have seen this IP (', $ip, ') ',$this->get_n_for_ip ($ip),' times.',"\n";
		if ($count)
			$this->count ();
	}
	
	public function __destruct () {
		$this->data_write ();
	}
	
	private function data_load () {
		$fp = @fopen ($this->file, 'r');
		
		if ($fp) {
			while ($line = fgets ($fp)) {
				$line = split (' ', $line);
				settype ($line[1], int);
				$this->ips[$line[0]] = $line[1];
			}
			
			fclose ($fp);
		}
	}
	
	// FIXME: use a lockfile or file_put_contents() with LOCK_EX flag
	private function data_write () {
		$fp = @fopen ($this->file, 'w');
		
		if ($fp) {
			foreach ($this->ips as $ip => $count) {
				fputs ($fp, $ip.' '.$count."\n");
			}
			
			fclose ($fp);
			return true;
		}
		return false;
	}
	
	protected function ip_is_known ($ip) {
		return (array_key_exists ($ip, $this->ips));
	}
	
	protected function increment_ip ($ip) {
		if (!$this->ip_is_known ($ip))
			$this->ips[$ip] = 0;
		
		return $this->ips[$ip] += 1;
	}
	
	public function get_ip () {
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function get_n_for_ip ($ip) {
		if ($this->ip_is_known ($ip))
			return $this->ips[$ip];
		else
			return 0;
	}
	
	public function get_n_ip () {
		return count ($this->ips);
	}
	
	public function get_n_total () {
		return array_sum ($this->ips);
	}
	
	public function count () {
		$this->increment_ip ($this->get_ip ());
	}
}
*/



class CounterIP {
	protected $db;
	
	public function __construct ($count=false, $counter=COUNTER_TABLE) {
		$this->db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
		$this->db->select_table ($counter);
		
		if ($count)
			$this->count ();
	}
	
	public function __destruct () {
		unset ($this->db);
	}
	
	protected function ip_is_known ($ip) {
		if ($this->db->select ('`ip`', '`ip`=\''.$ip.'\'')) {
			return $this->db->fetch_response () ? true : false;
		}
		return false;
	}
	
	protected function increment_ip ($ip) {
		if ($this->ip_is_known ($ip)) {
			$n = $this->get_n_for_ip ($ip) + 1;
			return $this->db->update ('`count`=\''.$n.'\'', '`ip`=\''.$ip.'\'');
		}
		else
			return $this->db->insert ('\''.$ip.'\', \'1\'');
	}
	
	public function get_ip () {
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function get_n_for_ip ($ip) {
		$this->db->select ('*', '`ip`=\''.$ip.'\'');
		$response = $this->db->fetch_response ();
		if ($response !== false)
			return $response['count'];
		else
			return 0;
	}
	
	public function get_n_ip () {
		return $this->db->count ();
	}
	
	public function get_n_total () {
		$n = 0;
		$this->db->select ('*');
		while (($response = $this->db->fetch_response ()) !== false) {
			$n += $response['count'];
		}
		return $n;
	}
	
	public function count () {
		$this->increment_ip ($this->get_ip ());
	}
}

/*
$c = new CounterIP_ (false, '/tmp/counter');
$_SERVER['REMOTE_ADDR'] = $c->get_n_ip ();
$c->count ();
*/
