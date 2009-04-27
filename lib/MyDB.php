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


/* internal count of queries */
$__MyDB_internal__n_queries = 0;
/* internal count of instances */
$__MyDB_internal__n_instances = 0;

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
		global $__MyDB_internal__n_queries;
		$__MyDB_internal__n_queries++;
		
		//echo '<pre>MyDB: q is: ',$query,'</pre>';
		$this->response = mysql_query ($query, $this->link) or die ($this->error ());
		return $this->response;
	}
	
	public function select_table ($table)
	{
		$this->table = $table;
	}
	
	public function select ($what='*', $where='', $orderby='', $order='DESC', $limits=0, $limite=0)
	{
		if (!$this->table)
			return false;
		
		if ($where != '')
			$where = 'WHERE '.$where;
		
		if ($orderby != '')
		{
			if ($order == 'DESC' || $order == 'ASC')
				$orderby = 'ORDER BY '.$orderby.' '.$order;
		}
		
		if ($limite != 0)
			$limit = 'LIMIT '.$limits.','.$limite;
		else
			$limit = '';
		
		$this->query ('SELECT '.$what.' FROM '.$this->table.' '.$where.' '.$orderby.' '.$limit);
		
		if (!$this->response)
			return false;
		else
			return true;
	}
	
	public function insert ($values)
	{
		if (!$this->table || !$values)
			return false;
		
		return $this->query ('INSERT INTO '.$this->table.' VALUES('.$values.')');
	}
	
	public function update ($query, $where='')
	{
		if (!$this->table || !$query)
			return false;
		
		if ($where != '')
			$where = 'WHERE '.$where;
		
		return $this->query ('UPDATE '.$this->table.' SET '.$query.' '.$where);
	}
	
	public function delete ($where='')
	{
		if (!$this->table)
			return false;
		
		if ($where != '')
			$where = 'WHERE '.$where;
		
		return $this->query ('DELETE FROM '.$this->table.' '.$where);
	}
	
	public function count ($where='')
	{
		$n = 0;
		
		if (!$this->table)
			return 0;
		
		if ($where != '')
			$where = 'WHERE '.$where;
		
		if ($this->query ('SELECT COUNT(*) AS n FROM '.$this->table.' '.$where) !== false)
		{
			$data = $this->fetch_response ();
			$n = $data['n'];
		}
		
		return $n;
	}
	
	public function random_row ($column, $where='')
	{
		/* implementation found in the internet, supposed to be fatser than the
		 * naive one on large tables.
		 * But it has a bad alea if values in \p $column are not equally separated,
		 * e.g. if values are [1,2,5,7,42,43], 42 has the better chance to be
		 * selected (with (42-7) / 43 ~= 81.4% of chances), then 5 (~6.98%),
		 * then 7 (~4.66%) and finally 1, 2 and 43 (~2.33%).
		 */
		/*
		$this->select ('max('.$column.') AS max_id', $where);
		$max_row = $this->fetch_response ();
		$random = mt_rand (0, $max_row['max_id']);
		
		if ($where != '')
			$where = ' AND '.$where;
		
		if ($this->select ('*', $column.' >= '.$random.$where, $column, 'ASC', 1) == false)
			return $this->select ('*', $column.' < '.$random.$where, $column, 'DESC', 1);
		else
			return true;
		*/
		/* naive method */
		if ($where != '')
			$where = 'WHERE '.$where;
		
		return $this->query ('SELECT * FROM '.$this->table.' '.$where.' ORDER BY rand() LIMIT 1');
	}
	
	public function get_link ()
	{
		return $this->link;
	}
	
	public function get_response ()
	{
		return $this->response;
	}
	
	public function fetch_response ()
	{
		$response = $this->get_response ();
		
		if ($response)
			return mysql_fetch_array ($response);
		else
			return false;
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
	public function get_n_queries () {
		global $__MyDB_internal__n_queries;
		return $__MyDB_internal__n_queries;
	}
	
	public function get_n_instances () {
		global $__MyDB_internal__n_instances;
		return $__MyDB_internal__n_instances;
	}
}
