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

require_once ('lib/string.php');
require_once ('lib/Controller.php');
require_once ('lib/Route.php');
require_once ('lib/PHPTemplate.php');


class Dispatcher
{
	public $route = null;
	public $controller = null;
	
	public function __construct ($query)
	{
		$this->route = new Route ($query);
		$this->controller = $this->instantiate_controller ();
		$this->check_controller_action ();
	}
	
	public function run ()
	{
		$vars = $this->call_controller_method ($this->route->action, $this->route->args);
		$tpl_file = 'views/'.$this->route->controller.'/'.$this->route->action.'.phtml';
		return new PHPFileTemplate ($tpl_file, $vars);
	}
	
	private function controller_class_name ($controller_name)
	{
		return camelize ($controller_name).'Controller';
	}
	
	private function get_controller_file ($controller_name)
	{
		return 'controllers/'.$this->controller_class_name ($controller_name).'.php';
	}
	
	private function route_404 ()
	{
		return new Route (array (
			'controller' => 'error_404',
			'action' => 'index',
			'args' => array ('query' => $this->route->to_url ())
		));
	}
	
	private function instantiate_controller ()
	{
		$file = $this->get_controller_file ($this->route->controller);
		if (! file_exists ($file)) {
			$this->route = $this->route_404 ();
			$file = $this->get_controller_file ($this->route->controller);
		}
		include_once ($file);
		$className = $this->controller_class_name ($this->route->controller);
		return new $className ();
	}
	
	private function check_controller_action ()
	{
		if (! method_exists ($this->controller, $this->route->action)) {
			$this->route = $this->route_404 ();
			$this->controller = $this->instantiate_controller ();
		}
	}
	
	private function call_controller_method ($method, $args = array ())
	{
		return call_user_func_array (array ($this->controller, $method), $args);
	}
}
