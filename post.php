<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2008 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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
require_once ('include/User.php');
require_once ('include/MyDB.php');
require_once ('include/BCode.php');
require_once ('include/TypedDialog.class.php');



// adresse de redirection par défaut, utilisée si aucune autre n'est trouvée
$refresh = 'index.php';

// on vérifie que le client a donné une adresse de page précédante
if ($_SERVER['HTTP_REFERER']) {
	// on vérifie que la page pérécdante correspond à une page du site
	if (preg_match ('#^http://'.$_SERVER['SERVER_NAME'].'#U', $_SERVER['HTTP_REFERER'])) {
		$refresh = $_SERVER['HTTP_REFERER'];
		
		if ($_GET['id'] && ctype_digit ($_GET['id']))
		  $refresh .= '#n'.$_GET['id'];
	}
}

/* dialog creation */
$dialog = &new TypedDialog (DIALOG_TYPE_INFO, $refresh);


/* abstract class to update devel news */
abstract class Devel {
	const SECTION = 'devel';
	private static $table = DEVEL_TABLE;
	
	protected static function parse ($str) {
		$str = &htmlspecialchars (&$str, ENT_COMPAT, 'UTF-8');
		$str = &nl2br (&$str);
		$str = &preg_replace ('# ([!?:;])#', '&nbsp;$1', &$str);
		//$str = &addslashes (&$str); // secure SQL request
		
		return $str;
	}
	
	public static function save ($date, $content) {
		global $dialog;
		
		// la date ne devrait-elle pas être cérée ici ?
		$content = addslashes (self::parse ($content));
		
		//MyDB::set_die (true);
		
		if (!empty ($date) && !empty ($content) ) {
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->insert ("'', '$date', '$content'")) {
				//Msg::info ('Message posté avec succès.');
				$dialog->add_info_message ('Message posté avec succès.');
			}
			else {
				//Msg::error ('Une erreur est survenue lors de l\'insertion des données dans la base de données. Veuillez contacter votre administrateur pour qu\'il corrige l\'erreur.');
				$dialog->add_error_message ('Une erreur est survenue lors de l\'insertion des données dans la base de données. Veuillez contacter votre administrateur pour qu\'il corrige l\'erreur.');
			}
			
			unset ($db);
		}
		else {
			//Msg::error ('Aucune information n\'a été trouvée pour poster le message&nbsp;!');
			$dialog->add_error_message ('Aucune information n\'a été trouvée pour poster le message&nbsp;!');
		}
	}

	public static function remove ($id) {
		global $dialog;
		
		if (!empty ($id)) {
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->delete ("`id`='$id'")) {
				//Msg::info ('Message supprimé avec succès.');
				$dialog->add_info_message ('Message supprimé avec succès.');
			}
			else {
				//Msg::error ('Erreur lors de la suppression du message.');
				$dialog->add_error_message ('Erreur lors de la suppression du message.');
			}
			
			unset ($db);
		}
		else{
			//Msg::error ('Pas d\'ID spécifiée.');
			$dialog->add_error_message ('Pas d\'ID spécifiée.');
		}
	}

	public static function edit ($id, $date, $content) {
		global $dialog;
		
		if (!empty ($id) && !empty ($date) && !empty ($content)) {
			$content = addslashes (self::parse ($content));
			
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->update ("`date`='$date', `content`='$content'", "`id`=$id")) {
				//Msg::info ('Message édité avec succès.');
				$dialog->add_info_message ('Message édité avec succès.');
			}
			else {
				//Msg::error ('Erreur lors de l\'édition du message.');
				$dialog->add_error_message ('Erreur lors de l\'édition du message.');
			}
			
			unset ($db);
		}
		else {
			//Msg::error ('Données erronées.');
			$dialog->add_error_message ('Données erronées.');
		}
	}

	public static function get_table() {
		return self::$table;
	}
}

/* abstract class to update news */
abstract class News {
	const SECTION = 'news';
	private static $table = NEWS_TABLE;

	public static function save ($date, $title, $content, $author) {
		global $dialog;
		
		//$content = parse ($content);
		$source = addslashes ($content);
		$content = addslashes (BCode::parse (($content)));
		$title = addslashes ($title);
		$author = addslashes ($author);
		
		//MyDB::set_die (true);
		
		if (!empty ($date) && !empty ($title) && !empty ($content) && !empty ($source) && !empty ($author)) {
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->insert ("'', '$date', '$title', '$content', '$source', '$author'")) {
				$dialog->add_info_message ('News postée avec succès.');
			}
			else {
				$dialog->add_error_message ('Une erreur est survenue lors de l\'insertion des données dans la table MySQL. Veuillez contacter votre administrateur pour qu\'il corrige l\'erreur.');
			}
			
			unset ($db);
		}
		else
			$dialog->add_error_message ('Aucune information n\'a été trouvée pour poster la news&nbsp;!');
		}

	public static function remove ($id) {
		global $dialog;
		
		if (!empty ($id)) {
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->delete ("`id`='$id'")) {
				$dialog->add_info_message ('News supprimée avec succès.');
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

	public static function edit ($id, $date, $title, $content) {
		global $dialog;
		
		$source = addslashes ($content);
		$content = addslashes (BCode::parse (stripslashes ($content)));
		$title = addslashes ($title);
		
		if (!empty ($id) && !empty ($date) && !empty ($title) && !empty ($content)) {
//			$content = parse ($content);
			
			$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
			$db->select_table (self::$table);
			
			if ($db->update ("`date`='$date', `titre`='$title', `contenu`='$content', `source`='$source'", "`id`=$id"))
				$dialog->add_info_message  ('News éditée avec succès.');
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
	if (Devel::SECTION == $_GET['sec'] &&
			//User::get_name () == 'Yno')
			User::get_level () == 0)
	{
		// new devel post
		if ($_GET['act'] == 'new')
			Devel::save ($_POST['date'], $_POST['content']);
		
		// edit an existing devel post
		else if ($_GET['act'] == 'edit')
		{
			if (!empty ($_GET['id']))
				Devel::edit ($_GET['id'], $_POST['date'], $_POST['content']);
			else
				$dialog->add_error_message ('Aucun ID spécifié');
		}
		
		// remove an existing devel post
		else if ($_GET['act'] == 'rm')
		{
			if (!empty ($_GET['id']))
				Devel::remove ($_GET['id']);
			else
				$dialog->add_error_message ('Aucun ID spécifié');
		}
		
		else // invalid action request
			$dialog->add_error_message ('Action invalide.');
	}
	else if (News::SECTION == $_GET['sec'])
	{
		if (User::get_level () <= 1)
		{
			// new news
			if ($_GET['act'] == 'new')
				News::save ($_POST['date'], $_POST['title'], $_POST['content'], $_POST['author']);
			
			// edit an existing news
			else if ($_GET['act'] == 'edit')
			{
				if (!empty ($_GET['id']))
					News::edit ($_GET['id'], $_POST['date'], $_POST['title'], $_POST['content']);
				else
					$dialog->add_error_message ('Aucun ID spécifié');
			}
			
			// remove an existing news
			else if ($_GET['act'] == 'rm')
			{
				if (!empty ($_GET['id']))
					News::remove ($_GET['id']);
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
