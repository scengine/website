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

require_once ('include/defines.php');

define (DIALOG_REDIRECT_TIME, 1);


class Dialog {
	protected $title = 'Title';
	protected $messages = array ();
	private $redirect_time = DIALOG_REDIRECT_TIME;
	private $redirect_url = null;
	private $redirect = true;
	
	public function __construct ($title='Title', $redirect=true, $url=null, $time=3) {
		$this->set_title ($title);
		$this->set_redirect_url ($url);
		$this->set_redirect_time ($time);
		$this->set_redirect ($redirect);
	}
	
	public function set_redirect_url ($url) {
		$this->redirect_url = $url;
	}
	public function set_redirect_time ($time) {
		$this->redirect_time = $time;
	}
	public function set_redirect ($redirect) {
		$this->redirect = ($redirect != false);
	}
	
	public function set_title ($title) {
		$this->title = $title;
	}
	public function set_message ($msg, $title=null) {
		$this->messages = array (array ($msg, $title));
	}
	public function add_message ($msg, $title=null) {
		$this->messages[] = array ($msg, $title);
	}
	
	public function flush () {
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
	<head>
		<title><?php echo $this->title,' - ',ENGINE ?></title>
		
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="icon" href="styles/default/icon.png" type="image/png" />
<!-- Feuille de style par défaut -->
		<link rel="stylesheet" media="screen" type="text/css" title="default" href="styles/default/messages.css" />
		<?php
		
		/* auto redirection */
		if ($this->redirect) {
			echo '<meta http-equiv="refresh" content="',$this->redirect_time,'; url=',$this->redirect_url,'" />';
		}
		
		echo '
	</head>
	<body>
		<div class="message">
			';
		
		/* print message(s) */
		foreach ($this->messages as $message) {
			if ($message[1] !== null)
				echo '<h2>',$message[1],'</h2>';
			echo '<p>',$message[0], '</p>';
		}
		
		/* print return link */
		if ($this->redirect) {
			echo '<p class="center small"><a href="',$this->redirect_url,'">Cliquez ici</a> si vous n\'êtes pas redirigé automatiquement</p>';
		}
		else {
			echo '<p class="center small"><a href="',$this->redirect_url,'">Retour</a></p>';
		}
		
		echo '
		</div>
	</body>
</html>
';
	}
}

/*
$d = &new Dialog ('Dialogue', false, 'page.html');
$d->add_message ('Ceci est un message', 'Titre');
$d->add_message ('Voici un deuxième message, mais il n\'a pas de titre.');
$d->flush ();
unset ($d);
//*/
?>
