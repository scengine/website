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

/* Simple template engine
 * 
 * Actually it's only a search-and-replace style template engine, so there is
 * no conditions or template-define loops, etc.
 */


abstract class Template
{
	protected $tpl = '';
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
		$replaces = array ();
		$searches = array ();
		
		foreach ($this->vars as $search => &$replace) {
			$searches[] = '{'.$search.'}';
			if (is_array ($replace)) {
				$actual_replace = '';
				foreach ($replace as &$item) {
					$actual_replace .= (string) $item;
				}
				$replaces[] = &$actual_replace;
			} else {
				$replaces[] = (string) $replace;
			}
		}
		
		return str_replace ($searches, $replaces, $this->tpl);
	}
}

class FileTemplate extends Template
{
	public function __construct ($filename, array $vars = array ())
	{
		$this->tpl = file_get_contents ($filename);
		$this->vars = $vars;
	}
}

class StringTemplate extends Template
{
	public function __construct ($template, array $vars = array ())
	{
		$this->tpl = $template;
		$this->vars = $vars;
	}
}

/*

// simple example

$tpl = new StringTemplate ('Hello, {name}!');
$tpl->name = 'John';

echo $tpl;

// recursive example

$items = array ();
for ($i = 0; $i < 42; $i++) {
	$item = new StringTemplate ('<ul>{item}</ul>');
	$item->item = $i;
	$items[] = $item;
}
$tpl = new StringTemplate ('{items}');
$tpl->items = $items;

echo $tpl;

// creation-time variable definitions

$tpl = new StringTemplate (
	'{a} {b} {c}',
	array (
		'a' => 'first value',
		'b' => 'second value',
		'c' => new StringTemplate (
			'{x} {y}',
			array (
				'x' => 42,
				'y' => 84
			)
		)
	)
);

echo $tpl;

//*/

