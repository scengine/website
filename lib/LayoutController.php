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

require_once ('lib/Controller.php');
require_once ('lib/Metadata.php');


class LayoutController extends Controller
{
	public $layout = 'views/layout.phtml';
	
	private function get_title ()
	{
		$className = preg_replace ('/Controller$/', '', get_class ($this));
		return preg_replace (array ('/([a-z])([A-Z0-9])/', '/([0-9])([a-zA-Z])/'), '\1 \2', $className);
	}
	
	protected function get_layout_vars ($route, $action_data)
	{
		$tpl_file = 'views/'.$route->controller.'/'.$route->action.'.phtml';
		
		return array (
			'controller'  => $route->controller,
			'template'    => new PHPFileTemplate ($tpl_file, $action_data),
			'site_title'  => Metadata::get_instance()->get_name (),
			'page_title'  => $this->get_title ()
		);
	}
	
	public function render ($route, $action_data)
	{
		$vars = $this->get_layout_vars ($route, $action_data);
		$layout = new PHPFileTemplate ($this->layout, $vars);
		$layout->render ();
	}
}
