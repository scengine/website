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

/* Interface for PHP template rendering
 * 
 * Such templates are simply PHP scripts with variables defined.
 * 
 * Unlike Template, there is no String version.
 */


class PHPTemplate
{
	protected $tpl = '';
	protected $vars = array ();
	
	public function __construct ($filename, array $vars = array ())
	{
		$this->tpl = $filename;
		$this->vars = $vars;
	}
	
	public function __set ($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	public function __get ($name)
	{
		if (array_key_exists ($name, $this->vars)) {
			return $this->vars[$name];
		}
		
		$trace = debug_backtrace ();
		trigger_error ('Undefined property "'.$name.'" '.
		               '(from file "'.$trace[0]['file'].'" '.
		               'at line '.$trace[0]['line'].')',
		               E_USER_NOTICE);
		return null;
	}
	
	public function render ()
	{
		extract ($this->vars);
		include ($this->tpl);
	}
	
	public function __toString ()
	{
		ob_start ();
		$this->render ();
		$data = ob_get_contents ();
		ob_end_clean ();
		
		return $data;
	}
}
