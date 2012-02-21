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

/*
 * This file is used to interact with the database
 * Actions are writing, updating or deleting data from the "news"-like tables, 
 * like news & engine's actuality
 */


require_once ('include/defines.php');
require_once ('lib/User.php');
require_once ('lib/MyDB.php');
require_once ('lib/BCode.php');
require_once ('lib/string.php');
require_once ('lib/TypedDialog.php');
require_once ('lib/feeds.php');
require_once ('lib/UrlTable.php');



// adresse de redirection par défaut, utilisée si aucune autre n'est trouvée
$refresh = UrlTable::home ();

if (isset ($_GET['redirect'])) {
	// if a redirect was sepcified, use it
	$refresh = urldecode ($_GET['redirect']);
}
else {
	// else, try to get previous URL
	// on vérifie que le client a donné une adresse de page précédante
	if ($_SERVER['HTTP_REFERER']) {
		// on vérifie que la page pérécdante correspond à une page du site
		if (str_has_prefix ($_SERVER['HTTP_REFERER'], 'http://'.$_SERVER['SERVER_NAME'])) {
			$refresh = $_SERVER['HTTP_REFERER'];
			
			if (isset ($_GET['id']) && ctype_digit ($_GET['id']))
			{
				if (PostDevel::SECTION == $_GET['sec'])
				{
					$refresh .= '#m'.$_GET['id'];
				}
				else
				{
					/* don't redirect to a removed page */
					if ($_GET['act'] == 'rm')
						$refresh = UrlTable::news ();
					else
						$refresh = UrlTable::news ($_GET['id']);
				}
			}
		}
	}
}

/* dialog creation */
$dialog = new TypedDialog (DIALOG_TYPE_INFO, $refresh);


/* abstract class to update devel news */
abstract class PostDevel {
	/*
	 * Table shape:
	 *  - id       INT(11) PRIMARY AUTO_INCREMENT
	 *  - date     BIGINT(20)
	 *  - content  TEXT
	 */
	const SECTION = 'devel';
	private static $table = DEVEL_TABLE;
	
	protected static function update_feed () {
		feed_update_devel ();
	}
	
	protected static function parse ($str) {
		$str = htmlspecialchars ($str, ENT_COMPAT, 'UTF-8');
		$str = nl2br ($str);
		$str = preg_replace ('# ([!?:;])#', '&nbsp;$1', $str);
		//$str = addslashes ($str); // secure SQL request
		
		return $str;
	}
	
	public static function save ($content) {
		global $dialog;
		
		$date = time ();
		$content = self::parse ($content);
		
		if (! empty ($content)) {
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			
			if ($db->insert (array ('date' => $date, 'content' => $content))) {
				$dialog->add_info_message ('Message posté avec succès.');
				
				self::update_feed ();
			}
			else {
				$dialog->add_error_message ('Une erreur est survenue lors de l\'insertion des '.
				                            'données dans la base de données. Veuillez contacter '.
				                            'votre administrateur pour qu\'il corrige l\'erreur.');
			}
			
			unset ($db);
		}
		else {
			$dialog->add_error_message ('Aucune information n\'a été trouvée pour poster le message&nbsp;!');
		}
	}
	
	public static function remove ($id) {
		global $dialog;
		
		if (! empty ($id)) {
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			
			if ($db->delete (array ('id' => $id))) {
				$dialog->add_info_message ('Message supprimé avec succès.');
				
				self::update_feed ();
			}
			else {
				$dialog->add_error_message ('Erreur lors de la suppression du message.');
			}
			
			unset ($db);
		}
		else{
			$dialog->add_error_message ('Pas d\'ID spécifiée.');
		}
	}
	
	public static function edit ($id, $content) {
		global $dialog;
		
		if (! empty ($id) && ! empty ($content)) {
			$content = self::parse ($content);
			
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			
			if ($db->update (array ('content' => $content), array ('id' => $id))) {
				$dialog->add_info_message ('Message édité avec succès.');
				
				self::update_feed ();
			} else {
				$dialog->add_error_message ('Erreur lors de l\'édition du message.');
			}
			
			unset ($db);
		}
		else {
			$dialog->add_error_message ('Données erronées.');
		}
	}
	
	public static function get_table() {
		return self::$table;
	}
}

/* abstract class to update news */
abstract class PostNews {
	/*
	 * Table shape:
	 *  - id       INT(11) PRIMARY KEY AUTO_INCREMENT
	 *  - date     BIGINT(20) NOT NULL
	 *  - mdate    BIGINT(20) NOT NULL
	 *  - title    VARCHAR(256) NOT NULL
	 *  - content  TEXT NOT NULL
	 *  - source   TEXT NOT NULL
	 *  - author   VARCHAR(256) NOT NULL
	 *  - mauthor  VARCHAR(256) NOT NULL
	 */
	const SECTION = 'news';
	private static $table = NEWS_TABLE;
	
	protected static function update_feed () {
		feed_update_news ();
	}
	
	public static function save ($title, $content) {
		global $dialog;
		
		//$content = parse ($content);
		$source = $content;
		$content = BCode::parse ($source);
		$author = User::get_name ();
		$date = time ();
		
		if (! empty ($title) && ! empty ($content) && ! empty ($source) && ! empty ($author)) {
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			if ($db->insert (array ('date' => $date, 'mdate' => $date,
				                      'title' => $title, 'content' => $content,
				                      'source' => $source, 'author' => $author,
				                      'mauthor' => $author))) {
				$dialog->add_info_message ('News postée avec succès.');
				
				self::update_feed ();
			}
			else {
				$dialog->add_error_message ('Une erreur est survenue lors de l\'insertion des '.
				                            'données dans la base de données. Veuillez contacter '.
				                            'votre administrateur pour qu\'il corrige l\'erreur.');
			}
			
			unset ($db);
		}
		else
			$dialog->add_error_message ('Aucune information n\'a été trouvée pour poster la news&nbsp;!');
	}
	
	public static function remove ($id) {
		global $dialog;
		
		if (!empty ($id)) {
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			if ($db->delete (array ('id' => $id))) {
				$dialog->add_info_message ('News supprimée avec succès.');
				
				self::update_feed ();
			}
			else {
				$dialog->add_error_message ('Erreur lors de la suppression de la news.');
			}
			
			unset ($db);
		}
		else {
			$dialog->add_error_message ('Pas d\'ID spécifiée.');
		}
	}
	
	public static function edit ($id, $title, $content) {
		global $dialog;
		
		$source = $content;
		$content = BCode::parse ($source);
		$title = $title;
		
		if (! empty ($id) && ! empty ($title) && ! empty ($content)) {
			$mdate = time ();
			$mauthor = User::get_name ();
			
			$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
			$db->select_table (self::$table);
			if ($db->update (array ('mdate' => $mdate, 'title' => $title,
			                        'content' => $content, 'source' => $source,
			                        'mauthor' => $mauthor),
			                 array ('id' => $id))) {
				$dialog->add_info_message  ('News éditée avec succès.');
				
				self::update_feed ();
			}
			else
				$dialog->add_error_message  ('Erreur lors de l\'édition de la news.');
			
			unset ($db);
		}
		else
			$dialog->add_error_message  ('Les données puxxent !!!');
	}
	
	public static function get_table() {
		return self::$table;
	}
}



/******************************************************************************/

if (User::get_logged ())
{
	if (PostDevel::SECTION == $_GET['sec'])
	{
		if (User::has_rights (ADMIN_LEVEL_NEWSDEVEL))
		{
			// new devel post
			if ($_GET['act'] == 'new')
				PostDevel::save ($_POST['content']);
			
			// edit an existing devel post
			else if ($_GET['act'] == 'edit')
			{
				if (!empty ($_GET['id']))
					PostDevel::edit ($_GET['id'], $_POST['content']);
				else
					$dialog->add_error_message ('Aucun ID spécifié');
			}
			
			// remove an existing devel post
			else if ($_GET['act'] == 'rm')
			{
				if (!empty ($_GET['id']))
					PostDevel::remove ($_GET['id']);
				else
					$dialog->add_error_message ('Aucun ID spécifié');
			}
			
			else // invalid action request
				$dialog->add_error_message ('Action invalide.');
		}
		else // user level for news
			$dialog->add_error_message ('Vous n\'avez pas le droit d\'effectuer cette action.');
	}
	else if (PostNews::SECTION == $_GET['sec'])
	{
		if (User::has_rights (ADMIN_LEVEL_NEWS))
		{
			// new news
			if ($_GET['act'] == 'new')
				PostNews::save ($_POST['title'], $_POST['content']);
			
			// edit an existing news
			else if ($_GET['act'] == 'edit')
			{
				if (!empty ($_GET['id']))
					PostNews::edit ($_GET['id'], $_POST['title'], $_POST['content']);
				else
					$dialog->add_error_message ('Aucun ID spécifié');
			}
			
			// remove an existing news
			else if ($_GET['act'] == 'rm')
			{
				if (!empty ($_GET['id']))
					PostNews::remove ($_GET['id']);
				else
					$dialog->add_error_message ('Aucun ID spécifié');
			}
			
			else // invalid action request
				$dialog->add_error_message ('Action invalide.');
		}
		else // user level for news
			$dialog->add_error_message ('Vous n\'avez pas le droit d\'effectuer cette action.');
	}
	else // invalid section post (news || devel)
		$dialog->add_error_message ('Aucune section spécifiée.');
}
else // not logged in
	$dialog->add_error_message ('Vous n\'avez pas le droit d\'effectuer cette action.');


/* display the dialog */
$dialog->flush ();
