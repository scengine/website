<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2012 Colomban Wendling <ban@herbesfolles.org>
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


class Controller
{
	public $layout = 'views/layout.phtml';
	
	public function get_title ()
	{
		$className = preg_replace ('/Controller$/', '', get_class ($this));
		return preg_replace (array ('/([a-z])([A-Z0-9])/', '/([0-9])([a-zA-Z])/'), '\1 \2', $className);
	}
	
	public function render ($route, $action_data)
	{
		$tpl_file = 'views/'.$route->controller.'/'.$route->action.'.phtml';
		
		define ('TITLE', $this->get_title ());
		$layout = new PHPFileTemplate (
			$this->layout,
			array (
				'controller' => $route->controller,
				'template' => new PHPFileTemplate ($tpl_file, $action_data)
			)
		);
		$layout->render ();
	}
	
	public function index ()
	{
		return array ();
	}
}
