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

/* A class for accessing the database.
 * 
 * It tries to be high-level and convenient */

require_once ('lib/string.php');


/* internal count of instances */
$__MyDB_internal__n_instances = 0;
/* internal log of queries */
$__MyDB_internal__query_log = array ();


class MyDB extends mysqli
{
	protected $response = false;
	protected $table;
	
	public function __construct ($host, $username, $password, $db=null, $charset=null)
	{
		global $__MyDB_internal__n_instances;
		$__MyDB_internal__n_instances++;
		
		parent::__construct ($host, $username, $password, $db);
		if ($charset !== null) {
			$this->set_charset ($charset);
		}
	}
	
	public function error ()
	{
		return $this->error;
	}
	
	public function query ($query)
	{
		global $__MyDB_internal__query_log;
		$__MyDB_internal__query_log[] = $query;
		
		if (is_object ($this->response)) { /* is this REALLY needed!? */
			$this->response->free ();
		}
		$this->response = parent::query ($query) or die ($this->error ());
		return $this->response;
	}
	
	public function select_table ($table)
	{
		$this->table = $table;
	}
	
	public function escape ($string)
	{
		return $this->escape_string ($string);
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
	
	public function select ($what='*', $where='', $orderby='', $limits=0, $limite=0)
	{
		if (! $this->table) {
			return false;
		}
		
		if (is_array ($what)) {
			$what = implode_quoted ('`', ',', $what);
		}
		
		$where = $this->parse_where ($where);
		
		/* orderby can either be a raw SQL query chunk following "ORDER BY" or an
		 * array of row=>order to sort by */
		if (! empty ($orderby)) {
			if (is_array ($orderby)) {
				$sorts = array ();
				foreach ($orderby as $row => $dir) {
					$sorts[] = '`'.$row.'` '.((strtolower ($dir) == 'desc') ? 'DESC' : 'ASC');
				}
				$orderby = implode (',', $sorts);
			}
			$orderby = 'ORDER BY '.$orderby;
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
		if (! $this->table || ! is_array ($values) || empty ($values)) {
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
		if (! $this->table || ! is_array ($values) || empty ($values)) {
			return false;
		}
		
		return $this->query (sprintf ('UPDATE `%s` SET %s %s',
		                              $this->table,
		                              implode (',', $this->quote_column_value ($values)),
		                              $this->parse_where ($where)));
	}
	
	public function delete ($where='')
	{
		if (! $this->table) {
			return false;
		}
		
		$where = $this->parse_where ($where);
		
		return $this->query (sprintf ('DELETE FROM `%s` %s', $this->table, $where));
	}
	
	public function count ($where='')
	{
		$n = 0;
		
		if (! $this->table) {
			return 0;
		}
		
		if ($this->select ('COUNT(*) AS n', $where) !== null) {
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
			return $this->response->fetch_assoc ();
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
			while (($row = $this->response->fetch_assoc ()) !== null) {
				$rows[] = $row;
			}
			$this->response->free ();
			$this->response = false;
			return $rows;
		}
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
