<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2008 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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
 * foreach (($news=DB::get_response ()) as $new) {
 *    # une fonction qui utilise la classe DB
 *    echo $news['texte'];
 * }
 * 
 * à première vue, quel est le problème ? et bien finalement pas grand
 * chose. Sauf si on a une classe qui mémorise le buffer de retour d'une
 * requête, comme ci-dessus. On se rendra vite compte que lors du
 * deuxième passage de la boucle, ce n'est plus la requête des news qui 
 * est retournée par DB::get_response() mais celle de la fonction
 * appellée entre-temps. Et donc, ça ne marche plus.
 * 
 * Je pense que celà est suffiusamment dérangeant pour prétendre qu'une
 * classe abstraite n'est vraiment pas adaptée à la gestion d'une base
 * de données.
 * 
 */



# Une classe non-abstraide de gestion de la DB

class DB
{
	protected $link, $response, $die;
	private $server, $username, $password, $db = null, $charset = null, $table;
	
	public function __construct ($server, $username, $password, $db=null, $charset=null)
	{
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->connect ();
		if ($charset !== null)
			$this->set_charset ($charset);
		if ($db !== null)
			$this->select_db ($db);
	}
	
	private function connect ()
	{
		return $this->link = mysql_connect ($this->server, $this->username, $this->password);
	}
	
	private function close ()
	{
		mysql_close ($this->link);
	}
	
	public function __sleep ()
	{
		return array ('server', 'username', 'password', 'db', 'charset');
	}
	
	public function __wakeup ()
	{
		$this->connect ();
		if ($this->charset !== null)
			$this->set_charset ($this->charset);
	}
	
	public function __destruct ()
	{
		$this->close ();
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
		{
			$limit = 'LIMIT '.$limits.','.$limite;
		}
		
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
		if (($rv = mysql_set_charset ($csname, $this->link)) !== false)
			$this->charset = $csname;
		
		return $rv;
	}
}
