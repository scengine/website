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
 */


/* Base class for PHP templates, implementing the common part */
abstract class PHPTemplate
{
	protected $vars = array ();
	
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
	
	public function __toString ()
	{
		ob_start ();
		$this->render ();
		$data = ob_get_contents ();
		ob_end_clean ();
		
		return $data;
	}
	
	public abstract function render ();
}

/* File-based template.
 * 
 * This is a simple wrapper around PHP inclusion to make it easy to use it as
 * a template engine.  It allows to provide a set of variables available in the
 * template as well as isolating the parsing context from the calling code.
 * 
 * Note that anything that isn't protected by PHP because of it's scope will
 * still be available, like superglobals and defines. */
class PHPFileTemplate extends PHPTemplate
{
	protected $tpl = '';
	
	public function __construct ($filename, array $vars = array ())
	{
		$this->tpl = $filename;
		$this->vars = $vars;
	}
	
	public function render ()
	{
		extract ($this->vars);
		include ($this->tpl);
	}
}

/* String-based template.
 * 
 * This is normally not needed since it's probably simpler to directly build
 * the output in PHP rather than building a string with PHP snippets in it.
 * This class is here mostly for compatibility and as a proof of concept.
 * 
 * Though, a use case might be deferring computation of some values to the
 * moment there will actually be used, e.g. by passing an objects that
 * implements string representation as one of the variables.  In such cases,
 * the string representation would be only computed when actually rendering the
 * template rather than when building it. */
class PHPStringTemplate extends PHPTemplate
{
	protected $tpl = '';
	
	public function __construct ($data, array $vars = array ())
	{
		$this->tpl = $data;
		$this->vars = $vars;
	}
	
	public function render ()
	{
		extract ($this->vars);
		eval ('?>'.$this->tpl.'<?php');
	}
}
