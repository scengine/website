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


require_once ('lib/BCode.php');
require_once ('include/defines.php');
require_once ('lib/MyDB.php');
require_once ('lib/User.php');
require_once ('lib/misc.php');

/* required JavaScript */
$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';


abstract class News
{
	/* Prints a news form */
	public function print_form ($title='', $source='', $action='new', $id='',
                              $redirect=null, $extra_buttons='', $extra_div_attrs='')
	{
		$title = escape_html_quotes ($title);
		$source = htmlspecialchars ($source, ENT_COMPAT, 'UTF-8');
		
		if ($redirect)
		{
			$redirect = '&amp;redirect='.urlencode ($redirect);
		}
		
		echo '
		<div class="formedit" id="fn',$id,'" ',$extra_div_attrs,'>
			<form method="post" action="post.php?sec=news&amp;id=',$id,'&amp;act=',$action,$redirect,'">
				<div>
					<label>T<span class="u">i</span>tre&nbsp;:<br />
						<input type="text" name="title" accesskey="i" value="',$title,'" />
					</label>
					<br />
					<br />
					<div class="form_toolbar">',
						print_button_js ('+', "entry_more('tn$id')", 'Agrandir le formulaire'),
						print_button_js ('-', "entry_lesser('tn$id')", 'Rapetisser le formulaire'),
						print_button_js ('http://', "textarea_insert('tn$id', '[[', ']]')", 'Insérer un lien'),
						print_button_js ('img', "textarea_insert('tn$id', '{{', '|Texte aleternatif}}')", 'Insérer une image'),
						print_button_js ('<span class="b">G</span>', "textarea_insert_around('tn$id', '**')", 'Mettre en gras'),
						print_button_js ('<span class="i">I</span>', "textarea_insert_around('tn$id', '//')", 'Mettre en italique'),
						print_button_js ('<span class="u">S</span>', "textarea_insert_around('tn$id', '__')", 'Souligner'),
						print_button_js ('<span class="s">B</span>', "textarea_insert_around('tn$id', '--')", 'Barrer'),
					'</div>
					<label><span class="u">C</span>ontenu&nbsp;:<br />
						<textarea name="content" cols="24" rows="16" accesskey="c" id="tn',$id,'">',
							$source,
						'</textarea>
					</label>
					<br />
					<input type="submit" value="Poster" accesskey="p" title="Poster (Alt + P)" />
					<!--input type="reset" value="Réinitialiser" accesskey="x" title="Vider le forumlaire (Alt + X)" /-->
					',$extra_buttons,'
				</div>
			</form>
		</div>';
	}
	
	
	/*** Retriving of data from the DB ***/
	
	/* Converts DB response to news's array */
	protected function convert_db_response (array &$resp)
	{
		return array (
			'id'      => $resp['id'],
			'date'    => $resp['date'],
			'mdate'   => $resp['mdate'],
			'title'   => stripslashes ($resp['titre']),
			'content' => stripslashes ($resp['contenu']),
			'source'  => stripslashes ($resp['source']),
			'author'  => stripslashes ($resp['auteur']),
			'mauthor' => stripslashes ($resp['mauthor']),
		);
	}
	
	/* Gets a list of news
	 * \param $start_offset number of news (from the last one) to skip
	 * \param $n            number of news to get from $start_offset
	 * \returns an array of news or false on failure.
	 */
	public function get ($start_offset=0, $n=0)
	{
		$news = false;
		
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		if ($db->select ('*', '', '`id`', 'DESC', intval ($start_offset), intval ($n)))
		{
			$news = array ();
			while (false !== ($resp = $db->fetch_response ()))
			{
				$news[] = self::convert_db_response ($resp);
			}
		}
		
		unset ($db);
		return $news;
	}
	
	/* Gets a news by its ID
	 * \param $id the ID of the news to get
	 * \returns a news or false on failure.
	 */
	public function get_by_id ($id)
	{
		$id = intval ($id);
		
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		if ($db->select ('*', "`id`=$id") && ($resp = $db->fetch_response ()) !== false)
			return self::convert_db_response ($resp);
		else
			return false;
	}
	
	/* Gets the total number of news */
	public function get_n ()
	{
		$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		return $db->count ();
	}
}
