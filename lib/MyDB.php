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


/*
 * FIXME ou Pourquoi créer une classe abstraite de gestion des BDD est
 * complètement débile :
 * 
 * On pe peux pas imbriquer les requêtes. Et c'est finalement très
 * embêtant. Je croyais au début que ce n'était pas grave, sauf dans le
 * cas de connexion entre plusieurs bases pour des relation complexes.
 * Mais c'est comlètement crétin, car même deux requêtes peuvent
 * facilement s'imbriquer pour créer un bug.
 * Par exemple, prennont une fonction qui va afficher des news.
 * 
 * foreach (($news=MyDB::get_response ()) as $new) {
 *    # une fonction qui utilise la classe DB
 *    echo $news['texte'];
 * }
 * 
 * à première vue, quel est le problème ? et bien finalement pas grand
 * chose. Sauf si on a une classe qui mémorise le buffer de retour d'une
 * requête, comme ci-dessus. On se rendra vite compte que lors du
 * deuxième passage de la boucle, ce n'est plus la requête des news qui 
 * est retournée par MyDB::get_response() mais celle de la fonction
 * appellée entre-temps. Et donc, ça ne marche plus.
 * 
 * Je pense que celà est suffiusamment dérangeant pour prétendre qu'une
 * classe abstraite n'est vraiment pas adaptée à la gestion d'une base
 * de données.
 * 
 */


require_once ('lib/string.php');


/* internal count of instances */
$__MyDB_internal__n_instances = 0;
/* internal log of queries */
$__MyDB_internal__query_log = array ();

# Une classe non-abstraide de gestion de la DB

class MyDB
{
	protected $link, $response, $die;
	private $server, $username, $password, $db = null, $charset = null, $table;
	
	public function __construct ($server, $username, $password, $db=null, $charset=null)
	{
		global $__MyDB_internal__n_instances;
		$__MyDB_internal__n_instances++;
		
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		if ($this->connect () !== false) {
			if ($charset !== null)
				$this->set_charset ($charset);
			if ($db !== null)
				$this->select_db ($db);
		}
	}
	
	private function connect ()
	{
		$this->link = mysql_connect ($this->server, $this->username, $this->password);
		return $this->link !== false;
	}
	
	public function close ()
	{
		if ($this->link !== false) {
			mysql_close ($this->link);
			$this->link = false;
		}
	}
	
	public function __sleep ()
	{
		return array ('server', 'username', 'password', 'db', 'charset');
	}
	
	public function __wakeup ()
	{
		if ($this->connect () !== false) {
			if ($this->charset !== null)
				$this->set_charset ($this->charset);
		}
	}
	
	public function __destruct ()
	{
		/*
		 * see http://fr.php.net/manual/fr/function.mysql-close.php#72395 for why I
		 * don't close link.
		 * Moreover, I dunno why, perhaps because link is already implicitly closed,
		 * but sometimes I get an error about invalid connexion closed if I close it
		 * myself.
		 */
		//$this->close ();
	}
	
	public function error ()
	{
		return mysql_error ($this->link);
	}
	
	public function select_db ($db)
	{
		$this->db = $db;
		mysql_select_db ($this->db, $this->link);
	}
	
	public function query ($query)
	{
		global $__MyDB_internal__query_log;
		
		$__MyDB_internal__query_log[] = $query;
		$this->response = mysql_query ($query, $this->link) or die ($this->error ());
		return $this->response;
	}
	
	public function select_table ($table)
	{
		$this->table = $table;
	}
	
	public function escape ($string)
	{
		return mysql_real_escape_string ($string, $this->link);
	}
	
	/* converts an array of the form
	 *   array (column1 => value1,
	 *          column2 => value2)
	 * to the form:
	 *   array (`column1`='escaped value1',
	 *          `column2`='escaped value2')
	 * so it can be given to MySQL */
	private function quote_column_value (array $args)
	{
		$result = array ();
		
		foreach ($args as $column => $value) {
			$result[] = sprintf ('`%s`=\'%s\'', $column, $this->escape ($value));
		}
		
		return $result;
	}
	
	private function parse_where ($where)
	{
		if (empty ($where)) {
			return '';
		} else {
			if (is_array ($where)) {
				$where = implode (' AND ', $this->quote_column_value ($where));
			}
			
			return 'WHERE '.$where;
		}
	}
	
	public function select ($what='*', $where='', $orderby='', $order='DESC', $limits=0, $limite=0)
	{
		if (!$this->table)
			return false;
		
		if (is_array ($what)) {
			$what = implode_quoted ('`', ',', $what);
		}
		
		$where = $this->parse_where ($where);
		
		/* FIXME: this isn't really nice nor flexible */
		if ($orderby != '') {
			if ($order == 'DESC' || $order == 'ASC')
				$orderby = 'ORDER BY `'.$orderby.'` '.$order;
		}
		
		if ($limite != 0) {
			$limit = 'LIMIT '.intval ($limits).','.intval ($limite);
		} else {
			$limit = '';
		}
		
		$this->query (sprintf ('SELECT %s FROM `%s` %s %s %s',
		                       $what, $this->table, $where, $orderby, $limit));
		
		return $this->response ? true : false;
	}
	
	/**
	 * \brief Inserts a new row
	 * \param $values an array of column-value pairs, like
	 *                array('col1' => 'value1')
	 * \param $on_dup_key_update an array of column-value pairs or a custom values
	 *                           update snippet to apply if the row to insert
	 *                           already exists according to the database's key
	 *                           uniqueness rules (see MySQL docs about
	 *                           "ON DUPLICATE KEY UPDATE" for details)
	 * \returns true on success, false otherwise
	 * 
	 * Inserts a new row in the currently selected table of the current database.
	 * 
	 * If \param $on_dup_key_update is given and the row to insert conflicts with
	 * an existing one, it will be used for updating the existing row.  This
	 * parameter can either be a column-value paired array like
	 * \code array('column1' => 'value1') \endcode or a custom query string.
	 * 
	 * When using the array-based variant, you don't have to deal with any
	 * escaping of either the value or the column name;  but if you provide custom
	 * query code you need to take care to properly escape everything.  You should
	 * use MyDB::escape() to escape any value.
	 * 
	 * Example of inserting a new row:
	 * \code $db->insert(array('title' => 'Hello', 'content' => 'Some stuff')) \endcode
	 * 
	 * Example of inserting a new row or updating an existing one with custom code:
	 * \code $db->insert(array('ip' => $ip, 'count' => 1), '`count`=`count`+1') \endcode
	 */
	public function insert (array $values, $on_dup_key_update = '')
	{
		if (!$this->table || ! is_array ($values) || empty ($values)) {
			return false;
		}
		
		$columns = array_keys ($values);
		foreach ($values as &$value) {
			$value = $this->escape ($value);
		}
		
		/* support for ON DUPLICATE KEY UPDATE */
		if (empty ($on_dup_key_update)) {
			$on_dup_key_update = '';
		} else {
			if (is_array ($on_dup_key_update)) {
				$args = implode (',', $this->quote_column_value ($on_dup_key_update));
				$on_dup_key_update = implode (',', $args);
			}
			$on_dup_key_update = 'ON DUPLICATE KEY UPDATE '.$on_dup_key_update;
		}
		
		return $this->query (sprintf ('INSERT INTO `%s` (%s) VALUES (%s) %s',
		                              $this->table,
		                              implode_quoted ('`', ',', $columns),
		                              implode_quoted ('\'', ',', $values),
		                              $on_dup_key_update));
	}
	
	/**
	 * \brief Updates an existing row
	 * \param $values an array of column-value pairs, like
	 *                array('col1' => 'value1')
	 * \param $where an optional match for the rows to update, see
	 *               MyDB::parse_where()
	 * \returns true on success, false otherwise
	 * 
	 * Updates a existing row in the currently selected table of the current
	 * database.
	 * 
	 * Example:
	 * \code $db->update(array('title' => 'New Title'), array('id' => 42)); \endcode
	 */
	public function update (array $values, $where='')
	{
		if (! $this->table || ! is_array ($values) || empty ($values))
			return false;
		
		return $this->query (sprintf ('UPDATE `%s` SET %s %s',
		                              $this->table,
		                              implode (',', $this->quote_column_value ($values)),
		                              $this->parse_where ($where)));
	}
	
	public function delete ($where='')
	{
		if (!$this->table)
			return false;
		
		$where = $this->parse_where ($where);
		
		return $this->query (sprintf ('DELETE FROM `%s` %s', $this->table, $where));
	}
	
	public function count ($where='')
	{
		$n = 0;
		
		if (!$this->table)
			return 0;
		
		$where = $this->parse_where ($where);
		
		if ($this->select ('COUNT(*) AS n', $where) !== false) {
			$data = $this->fetch_response ();
			$n = $data['n'];
		}
		
		return $n;
	}
	
	public function increment ($column, $where='')
	{
		$where = $this->parse_where ($where);
		
		return $this->query (sprintf ('UPDATE `%s` SET `%s`=`%s`+1 %s',
		                              $this->table, $column, $column, $where));
	}
	
	public function random_row ($what='*', $where='')
	{
		if (is_array ($what)) {
			$what = implode_quoted ('`', ',', $what);
		}
		
		$where = $this->parse_where ($where);
		
		return $this->query (sprintf ('SELECT %s FROM `%s` %s ORDER BY rand() LIMIT 1',
		                              $what, $this->table, $where));
	}
	
	public function get_link ()
	{
		return $this->link;
	}
	
	public function get_response ()
	{
		return $this->response;
	}
	
	/**
	 * \brief Fetches the new row resulting from the last query
	 * \returns The next row, false if there is no more results
	 * 
	 * Fetches the next row resulting from the last query.  Repeated calls to
	 * this function will fetches a new row each time.
	 * 
	 * If you want to fetch all rows in the response at once, consider using
	 * fetch_all_responses ().
	 */
	public function fetch_response ()
	{
		if (is_bool ($this->response)) {
			return $this->response;
		} else {
			return mysql_fetch_assoc ($this->response);
		}
	}
	
	/**
	 * \brief Fetches all remaining rows resulting from the last query
	 * \returns an array of all the remaining rows
	 * 
	 * Like fetch_response(), but fetches all response in a single call,
	 * returning an array of them.
	 * 
	 * Note that this will return the *remaining* rows, so if you called
	 * fetch_response() it will return all the result but the ones to fetched
	 * with that function.  Similarly, calling this function twice will return
	 * an empty set on the second call.
	 */
	public function fetch_all_responses ()
	{
		if (is_bool ($this->response)) {
			return array ();
		} else {
			$rows = array ();
			while (($row = mysql_fetch_assoc ($this->response)) !== false) {
				$rows[] = $row;
			}
			return $rows;
		}
	}
	
	public function set_charset ($csname) {
		/* disable this since mysql_set_charset() is not available on TF servers */
		/*
		if (($rv = mysql_set_charset ($csname, $this->link)) !== false)
			$this->charset = $csname;
		
		return $rv;
		*/
		return true;
	}
	
	/* Returns number of queries. This count is kept between instances and this
	 * function can be called without a class instance. */
	public static function get_n_queries () {
		global $__MyDB_internal__query_log;
		return count ($__MyDB_internal__query_log);
	}
	
	public static function get_n_instances () {
		global $__MyDB_internal__n_instances;
		return $__MyDB_internal__n_instances;
	}
	
	public static function get_query_log () {
		global $__MyDB_internal__query_log;
		return $__MyDB_internal__query_log;
	}
}
