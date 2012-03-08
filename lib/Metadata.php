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

/**
 * \brief A class to manage metadata of a project
 */
class Metadata
{
	protected $fields = array (
		'name'         => 'Project name', //!< project name
		'version'      => '0.1', //!< project version
		'authors'      => array (), //!< array of project's authors
		'contributors' => array (),
		'translators'  => array (),
		'documenters'  => array (),
		'graphists'    => array (),
		'license'      => 'GPLv3', //!< project license
		'mdate'        => 0, //!< project modification date
		/* informations about metadata */
		'meta_mdate'   => 0 //!< metadata modification date
	);
	private $datafile = 'metadatas';
	
	public function __construct ($datafile='metadatas') {
		if (is_string ($datafile))
			$this->datafile = $datafile;
		
		$this->data_load ();
	}
	
	public function __destruct () {
		//$this->data_write ();
	}
	
	public function save () {
		$this->data_write ();
	}
	
	private function data_load () {
		$szdata = @file_get_contents ($this->datafile);
		if ($szdata !== false) {
			$data = unserialize ($szdata);
			if (is_array ($data)) {
				/* don't directly set $this->fields=$data to permit adding fields
				 * and keeping odl datafile */
				foreach ($data as $k => $v) {
					$this->fields[$k] = $v;
				}
			}
			else
				return false;
			
			unset ($data);
		}
		else
			return false;
		
		unset ($szdata);
		
		return true;
	}
	
	private function data_write () {
		$szdata = serialize ($this->fields);
		if ($szdata) {
			return file_put_contents ($this->datafile, $szdata, LOCK_EX) !== false;
		}
		
		return false;
	}
	
	private function get_array ($array) {
		return $this->fields[$array];
	}
	/** Adds an item to an array field
	 * \param array the name of the array field
	 * \param value an item as a string
	 * \returns true on success, false otrherwise
	 * 
	 * This function adds an array item only if the given parameter is a string.
	 */
	private function add_array_item ($array, $value) {
		if (!is_string ($value))
			return false;
		
		if (!in_array ($value, $this->fields[$array]))
			$this->fields[$array][] = $value;
		
		return true;
	}
	/** Removes value from the given filed
	 * \param array the name of the array field
	 * \param value teh value to remove from the given array field
	 * 
	 * \note This function remove all occurences of \p $value from the given
	 *       field.
	 * \note This function do a type-sensitive comparaison with the value.
	 * \note This function doesn't modify any array keys.
	 */
	private function remove_array_item ($array, $value) {
		$a = &$this->fields[$array];
		
		foreach ($a as $key => $val) {
			if ($val === $value)
				unset ($a[$key]);
		}
		
		/*$arr = array ();
		
		foreach ($this->fields[$array] as $val) {
			if ($val !== $value)
				$arr[] = $val;
		}
		
		$this->fields[$array] = $arr;
		*/
		
		/*
		function my_filter ($v) {
			$value;
			echo "=== $value ===\n";
			return ($v !== $value);
		}
		
		$this->fields[$array] = array_filter ($this->fields[$array], 'my_filter');
		*/
	}
	/** Sets an array field
	 * \param array the name of the array field
	 * \param values an array of the values or a string of one value.
	 * \returns true on success, false otrherwise
	 */
	private function set_array ($array, $values) {
		$this->fields[$array] = array ();
		
		if (is_null ($values)) {
			/* don't do anything, the array is already reseted */
		}
		else if (is_array ($values)) {
			foreach ($values as $value) {
				$this->add_array_item ($array, $value);
			}
		}
		else
			return $this->add_array_item ($array, $values);
		
		return true;
	}
	/** Adds item(s) to an array field
	 * \param array the name of the array field
	 * \param values values an array of the values or a string of one value.
	 * \returns true on success, false otrherwise
	 */
	private function add_array_items ($array, $values) {
		if (is_array ($values)) {
			foreach ($values as $value) {
				$this->add_array_item ($array, $value);
			}
		}
		else
			return $this->add_array_item ($array, $values);
		
		return true;
	}
	private function remove_array_items ($array, $values) {
		if (is_array ($values)) {
			foreach ($values as $value) {
				$this->remove_array_item ($array, $value);
			}
		}
		else
			return $this->remove_array_item ($array, $values);
		
		return true;
	}
	
	/* Name filed */
	public function get_name () {
		return $this->fields['name'];
	}
	public function set_name ($name) {
		if (is_string ($name))
			$this->fields['name'] = $name;
		else
			return false;
		
		return true;
	}
	
	/* Version field */
	public function get_version () {
		return $this->fields['version'];
	}
	public function set_version ($version) {
		if (!settype ($version, 'string'))
			return false;
		
		$this->fields['version'] = $version;
		return true;
	}
	
	/* Authors field */
	public function get_authors () {
		return $this->get_array ('authors');
	}
	/** Sets the author(s) list
	 * \param authors   an array containing authors or a string containing the
	 *                  single author or null to clear the author list.
	 * \returns true on success, false on failure
	 */
	public function set_authors ($authors) {
		return $this->set_array ('authors', $authors);
	}
	/** Adds author(s)
	 * \param authors   an array containing authors or a string containing the
	 *                  single author
	 * \returns true on success, false on failure
	 */
	public function add_authors ($authors) {
		return $this->add_array_items ('authors', $authors);
	}
	public function remove_authors ($authors) {
		return $this->remove_array_items ('authors', $authors);
	}
	
	/* Contributors field */
	public function get_contributors () {
		return $this->get_array ('contributors');
	}
	public function set_contributors ($contributors) {
		return $this->set_array ('contributors', $contributors);
	}
	public function add_contributors ($contributors) {
		return $this->add_array_items ('contributors', $contributors);
	}
	public function remove_contributors ($contributors) {
		return $this->remove_array_items ('contributors', $contributors);
	}
	
	/* Translators field */
	public function get_translators () {
		return $this->get_array ('translators');
	}
	public function set_translators ($translators) {
		return $this->set_array ('translators', $translators);
	}
	public function add_translators ($translators) {
		return $this->add_array_items ('translators', $translators);
	}
	public function remove_translators ($translators) {
		return $this->remove_array_items ('translators', $translators);
	}
	
	/* Documenters field */
	public function get_documenters () {
		return $this->get_array ('documenters');
	}
	public function set_documenters ($documenters) {
		return $this->set_array ('documenters', $documenters);
	}
	public function add_documenters ($documenters) {
		return $this->add_array_items ('documenters', $documenters);
	}
	public function remove_documenters ($documenters) {
		return $this->remove_array_items ('documenters', $documenters);
	}
	
	/* Graphists field */
	public function get_graphists () {
		return $this->get_array ('graphists');
	}
	public function set_graphists ($graphists) {
		return $this->set_array ('graphists', $graphists);
	}
	public function add_graphists ($graphists) {
		return $this->add_array_items ('graphists', $graphists);
	}
	public function remove_graphists ($graphists) {
		return $this->remove_array_items ('graphists', $graphists);
	}
	
	/* License field */
	public function get_license () {
		return $this->fields['license'];
	}
	public function set_license ($license) {
		if (is_string ($license))
			$this->fields['license'] = $license;
		else
			return false;
		
		return true;
	}
	
	/* Modification date field */
	public function get_mdate () {
		return $this->fields['mdate'];
	}
	public function set_mdate ($date) {
		if (is_int ($date) && $date >= 0)
			$this->fields['mdate'] = $date;
		else
			return false;
		
		return true;
	}
	public function update_mdate () {
		return $this->fields['mdate'] = time ();
	}
	
	public function update_metadata_mdate () {
		return $this->fields['meta_mdate'] = time ();
	}
	
	/* other stuff */
	public function field_foreach ($function) {
		foreach ($this->fields as $f => $v) {
			if (!$function ($f, $v))
				break;
		}
	}
}


/* insrance par défaut.
 * Cette classe pouraît être un calsse abstraite je pense, il faudrait voir */
$MDI = new Metadata (METADATA_FILE);

/*
$md = new Metadata ('/tmp/metadata');
printf ("%s v%s\n", $md->get_name (), $md->get_version ());
printf ("Last update: %s\n", date ("d/m/Y H:i:s", $md->get_mdate ()));
echo "Authors:\n";
foreach ($md->get_authors () as $v)
	echo "\t", $v, "\n";


#$md->set_version ('0.0.7');
#$md->update_mdate ();
#$md->add_authors ('Colomban "Ban" Wendling');
#$md->add_documenters ('Tuxer');
#$md->add_documenters ('Kreeg');
#$md->remove_documenters ('Kreeg');

$md->save ();

function field_foreach_func ($f, $v) {
	if (is_array ($v)) {
		echo "$f are:\n";
		foreach ($v as $vv) {
			echo "\t$vv\n";
		}
	}
	else
		echo "$f is: \t$v\n";
	
	return true;
}
$md->field_foreach (field_foreach_func);

//*/
