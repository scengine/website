<?php
/* 
 * 
 * Copyright (C) 2008 Colomban "Ban" Wendling <ban@herbesfolles.org>
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


/*============= Virtual dir functions =================*/

require_once ('include/string.php');

/*
 * A vdir file is formed as the following:
 *   path:size:mdate:type
 * 
 * @v path is a quoted url (with urlencode() or so) that urldecode() must can
 *    decode.
 * @v size is the size of the item (file or so) in octets
 * @v mdate is an UNIX timestamp of the modification date of the item
 * @v type is a string representing the type of the item. This must be one of
 *    'fifo', 'character', 'directory', 'block', 'file', 'symlink', 'socket' or
 *    'unknown'.
 * 
 * A vdir should have an item for each directory if any of its content is listed
 * in the vdir.
 */

define (VDIR_TYPE_NONE,   0);
define (VDIR_TYPE_FIFO,   1<<0);
define (VDIR_TYPE_CHAR,   1<<1);
define (VDIR_TYPE_DIR,    1<<2);
define (VDIR_TYPE_BLOCK,  1<<3);
define (VDIR_TYPE_FILE,   1<<4);
define (VDIR_TYPE_LINK,   1<<5);
define (VDIR_TYPE_SOCKET, 1<<6);
define (VDIR_TYPE_ALL,    (1<<7)-1);


class VDir {
	protected $list = array ();
	protected $modified = false;
	protected $file = null;
	
	public function __construct ($vdir_file) {
		$this->file = $vdir_file;
		$fp = fopen ($this->file, 'r');
		if ($fp === false)
			throw new Exception ('Failed to open VDir description file', 1);
		
		while (! feof ($fp))
		{
			$line = trim (fgets ($fp));
			if (empty ($line[0]) || $line[0] == '#')
				continue;
			
			$item = $this->_get_item ($line);
			$this->list[$item['path']] = $item;
			
			if ($types & $item['type'])
			{
				if ($cb (&$item, &$data) == false)
					break;
			}
		}
		fclose ($fp);
		
		return true;
	}
	
	public function __destruct () {
		unset ($this->list);
	}
	
	protected function _get_item ($line) {
		$item = array (
			'path' => '',
			'size' => 0,
			'mdate'=> 0,
			'type' => VDIR_TYPE_NONE
		);
		
		$fi = explode (':', $line);
		
		$item['path'] = rawurldecode ($fi[0]);
		
		$item['size'] = $fi[1];
		settype ($item['size'], integer);
		
		$item['mdate'] = $fi[2];
		settype ($item['mdate'], integer);
		
		switch ($fi[3])
		{
			case 'fifo':      $item['type'] = VDIR_TYPE_FIFO;   break;
			case 'character': $item['type'] = VDIR_TYPE_CHAR;   break;
			case 'directory': $item['type'] = VDIR_TYPE_DIR;    break;
			case 'block':     $item['type'] = VDIR_TYPE_BLOCK;  break;
			case 'file':      $item['type'] = VDIR_TYPE_FILE;   break;
			case 'symlink':   $item['type'] = VDIR_TYPE_LINK;   break;
			case 'socket':    $item['type'] = VDIR_TYPE_SOCKET; break;
		}
		
		unset ($fi);
		
		return $item;
	}
	
	protected function _unget_item (&$item) {
		$line = '';
		
		/* FIXME: urlencode() uses + for spaces, but python use %20... */
		$line .= rawurlencode ($item['path']).':';
		$line .= $item['size'].':';
		$line .= $item['mdate'].':';
		switch ($item['type']) {
			case VDIR_TYPE_FIFO:   $line .= 'fifo';      break;
			case VDIR_TYPE_CHAR:   $line .= 'character'; break;
			case VDIR_TYPE_DIR:    $line .= 'directory'; break;
			case VDIR_TYPE_BLOCK:  $line .= 'block';     break;
			case VDIR_TYPE_FILE:   $line .= 'file';      break;
			case VDIR_TYPE_LINK:   $line .= 'symlink';   break;
			case VDIR_TYPE_SOCKET: $line .= 'socket';    break;
			default:               $line .= 'none';      break;
		}
		$line .= "\n";
		
		return $line;
	}
	
	/**
	 * \brief walk the virtual directory (recursivly)
	 * \param $types OR of VDIR_TYPEs you want
	 * \param $cb    callback to call on each item matching \p $types
	 * \param $data  optional data to pass to the callback \p $cb
	 * 
	 */
	public function item_foreach ($types, $cb, $data=null) {
		foreach ($this->list as $item)
		{
			if ($types & $item['type'])
			{
				if ($cb (&$this, &$item, &$data) == false)
					break;
			}
		}
	}
	
	public function file_foreach ($cb, $data=null) {
		return $this->item_foreach (VDIR_TYPE_FILE, &$cb, &$data);
	}
	
	public function dir_foreach ($cb, $data=null) {
		return $this->item_foreach (VDIR_TYPE_DIR, &$cb, &$data);
	}
	
	/**
	 * \brief walk the virtual directory (recursivly)
	 * \param $subdir arbitrary prefix that item should match
	 * \param $types  OR of VDIR_TYPEs you want
	 * \param $cb     callback to call on each matching item
	 * \param $data   optional data to pass to the callback \p $cb
	 * 
	 */
	public function subdir_item_foreach ($subdir, $types, $cb, $data=null) {
		foreach ($this->list as $key => $item)
		{
			if (! str_has_prefix ($key, $subdir))
				continue;
			
			if ($types & $item['type'])
			{
				if ($cb (&$this, &$item, &$data) == false)
					break;
			}
		}
		
		return true;
	}
	
	public function subdir_file_foreach ($subdir, $cb, $data=null) {
		return $this->subdir_item_foreach (&$subdir, VDIR_TYPE_FILE, &$cb, &$data);
	}
	
	public function subdir_dir_foreach ($subdir, $cb, $data=null) {
		return $this->subdir_item_foreach (&$subdir, VDIR_TYPE_DIR, &$cb, &$data);
	}
	
	public function exists ($path, $type) {
		if (array_key_exists (&$path, $this->list)) {
			if ($this->list[$path]['type'] & $type)
				return true;
		}
		return false;
	}
	
	public function file_exists ($path) {
		return $this->exists (&$path, VDIR_TYPE_FILE);
	}
	
	public function add ($path, $size, $mdate, $type) {
		if (! $this->exists ($path, VDIR_TYPE_ALL)) {
			$item = array (
				'path'  => $path,
				'size'  => $size,
				'mdate' => $mdate,
				'type'  => $type
			);
			$this->list[$path] = $item;
			$this->modified = true;
			
			$fp = fopen ($this->file, 'a');
			
			if ($fp !== false) {
				fputs ($fp, $this->_unget_item ($item));
				echo 'writing...';
				fclose ($fp);
			}
		}
	}
}

/*
function cb (&$vdir, &$item, &$data) {
	echo $vdir->file_exists ($item['path']) ? 'true' : 'false', "\n";
	echo $item['path'], ' (', round ($item['size'] / 1000, 2), " ko) \n";
	return true;
}

try {
	$vdir = &new VDir ('screens.list');
} catch (Exception $e) {
	echo $e;
	exit (1);
}

$vdir->subdir_file_foreach ('./gallery/', cb, null);
unset ($vdir);
*/
