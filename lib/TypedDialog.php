<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2009 Colomban "Ban" Wendling <ban@herbesfolles.org>
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
	
	/* this two overrides are to remove title set possibility */
	public function set_message ($msg, $dontuse=null) {
		parent::set_message ($msg);
	}
	public function add_message ($msg, $dontuse=null) {
		parent::add_message ($msg);
	}
	
	public function set_typed_message ($type, $msg) {
		$this->set_type ($type);
		$this->set_message ($msg);
	}
	public function add_typed_message ($type, $msg) {
		$this->set_type ($type);
		$this->add_message ($msg);
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
		switch ($this->type) {
			case DIALOG_TYPE_NONE:
				throw new Exception ('Dialog type cannot be DIALOG_TYPE_NONE for printing');
				return;
			case DIALOG_TYPE_ERROR:
				$this->set_title ('Erreur');
				break;
			case DIALOG_TYPE_INFO:
				$this->set_title ('Information');
				break;
			case DIALOG_TYPE_WARNING:
				$this->set_title ('Attention');
				break;
		}
		
		/* hack to set the right title for the message */
		$this->messages[0][1] = $this->title;
		
		parent::flush ();
	}
}

/*
$d = new TypedDialog (DIALOG_TYPE_WARNING, 'page.html');
$d->set_message ('coucou les gens !');
$d->flush ();
unset ($d);
//*/
