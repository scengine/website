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

/* Display messages dialogs */

require_once ('lib/Dialog.php');


define ('DIALOG_TYPE_NONE', 0);
define ('DIALOG_TYPE_INFO', 1);
define ('DIALOG_TYPE_ERROR', 2);
define ('DIALOG_TYPE_WARNING', 3);

class TypedDialog extends Dialog {
	private $type;
	
	public function __construct ($type=DIALOG_TYPE_NONE, $url=null, $time=DIALOG_REDIRECT_TIME) {
		$this->set_type ($type);
		parent::__construct ('None', true, $url, $time);
	}
	
	public function set_type ($type) {
		if ($type >= 0 && $type <= 3)
			$this->type = $type;
		else
			throw new Exception ('Invalid type');
	}
	
	private function title_from_type ($type)
	{
		switch ($type) {
			case DIALOG_TYPE_ERROR:		return 'Error';
			case DIALOG_TYPE_INFO:		return 'Information';
			case DIALOG_TYPE_WARNING:	return 'Warning';
		}
		return null;
	}
	
	public function set_typed_message ($type, $msg) {
		$this->set_type ($type);
		$this->set_message ($msg, $this->title_from_type ($type));
	}
	public function add_typed_message ($type, $msg) {
		$this->set_type ($type);
		$this->add_message ($msg, $this->title_from_type ($type));
	}
	
	public function set_error_message ($msg) {
		$this->set_redirect (false);
		$this->set_typed_message (DIALOG_TYPE_ERROR, $msg);
	}
	public function add_error_message ($msg) {
		$this->set_redirect (false);
		$this->set_typed_message (DIALOG_TYPE_ERROR, $msg);
	}
	public function set_info_message ($msg) {
		$this->set_typed_message (DIALOG_TYPE_INFO, $msg);
	}
	public function add_info_message ($msg) {
		$this->set_typed_message (DIALOG_TYPE_INFO, $msg);
	}
	public function set_warning_message ($msg) {
		$this->set_typed_message (DIALOG_TYPE_WARNING, $msg);
	}
	public function add_warning_message ($msg) {
		$this->set_typed_message (DIALOG_TYPE_WARNING, $msg);
	}
	
	public function flush () {
		if (! $this->title) {
			$this->set_title ($this->title_from_type ($this->type));
		}
		
		parent::flush ();
	}
}

/*
$d = new TypedDialog (DIALOG_TYPE_WARNING, 'page.html');
$d->set_message ('coucou les gens !');
$d->flush ();
unset ($d);
//*/
